#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SRC="${REPO_ROOT}/wordpress-plugin/icap-seo"
PLUGIN_MAIN="${PLUGIN_SRC}/icap-seo.php"
DIST_DIR="${REPO_ROOT}/dist"

if [[ ! -d "${PLUGIN_SRC}" ]]; then
  echo "Plugin source not found: ${PLUGIN_SRC}"
  exit 1
fi
if [[ ! -f "${PLUGIN_MAIN}" ]]; then
  echo "Plugin main file not found: ${PLUGIN_MAIN}"
  exit 1
fi

PLUGIN_VERSION_CONSTANT="$(sed -nE "s/^define\\('ICAP_SEO_VERSION',[[:space:]]*'([^']+)'\\);/\\1/p" "${PLUGIN_MAIN}" | head -n1)"
PLUGIN_VERSION_HEADER="$(sed -nE "s/^ \* Version:[[:space:]]*([^[:space:]]+).*$/\\1/p" "${PLUGIN_MAIN}" | head -n1)"

if [[ -z "${PLUGIN_VERSION_CONSTANT}" || -z "${PLUGIN_VERSION_HEADER}" ]]; then
  echo "Unable to detect plugin version header/constant from: ${PLUGIN_MAIN}"
  exit 1
fi
if [[ "${PLUGIN_VERSION_CONSTANT}" != "${PLUGIN_VERSION_HEADER}" ]]; then
  echo "Plugin version mismatch in ${PLUGIN_MAIN}"
  echo "  Header:   ${PLUGIN_VERSION_HEADER}"
  echo "  Constant: ${PLUGIN_VERSION_CONSTANT}"
  exit 1
fi
PLUGIN_VERSION="${PLUGIN_VERSION_CONSTANT}"

TAG_VERSION="${PLUGIN_VERSION}"
if [[ "${TAG_VERSION}" != v* ]]; then
  TAG_VERSION="v${TAG_VERSION}"
fi

ZIP_BASENAME="icap-seo-${TAG_VERSION}.zip"
ZIP_PATH="${DIST_DIR}/${ZIP_BASENAME}"
LEGACY_ZIP_PATH="${DIST_DIR}/icap-seo.zip"
SHA_PATH="${DIST_DIR}/icap-seo-${TAG_VERSION}.sha256"

mkdir -p "${DIST_DIR}"
if [[ -f "${ZIP_PATH}" && "${FORCE_REBUILD:-0}" != "1" ]]; then
  echo "Release package already exists for ${TAG_VERSION}: ${ZIP_PATH}"
  echo "Bump ICAP_SEO_VERSION before packaging a new release, or rerun with FORCE_REBUILD=1."
  exit 1
fi

python3 - "${REPO_ROOT}" "${DIST_DIR}" "${PLUGIN_VERSION}" "${FORCE_REBUILD:-0}" <<'PY'
import glob
import os
import pathlib
import re
import subprocess
import sys

repo_root, dist_dir, plugin_version, force_rebuild = sys.argv[1:5]
force_rebuild_enabled = force_rebuild == "1"
semver_pattern = re.compile(r"^\d+\.\d+\.\d+$")
dist_file_pattern = re.compile(r"^icap-seo-v(\d+\.\d+\.\d+)\.zip$")

def parse_semver(value):
    return tuple(int(part) for part in value.split("."))

def ensure_semver(value, label):
    if not semver_pattern.match(value):
        print(f"{label} must be semantic version x.y.z; got: {value}")
        sys.exit(1)

def bump_patch(value):
    major, minor, patch = parse_semver(value)
    return f"{major}.{minor}.{patch + 1}"

ensure_semver(plugin_version, "Plugin version")

dist_versions = []
for path in glob.glob(os.path.join(dist_dir, "icap-seo-v*.zip")):
    name = pathlib.Path(path).name
    match = dist_file_pattern.match(name)
    if not match:
        continue
    dist_versions.append(match.group(1))

latest_dist = max(dist_versions, key=parse_semver) if dist_versions else ""

tag_output = subprocess.check_output(
    ["git", "-C", repo_root, "--no-pager", "tag", "--list", "v*"],
    text=True,
)
tag_versions = []
for raw_tag in tag_output.splitlines():
    tag = raw_tag.strip()
    if not tag:
        continue
    candidate = tag[1:] if tag.startswith("v") else tag
    if semver_pattern.match(candidate):
        tag_versions.append(candidate)
latest_tag = max(tag_versions, key=parse_semver) if tag_versions else ""

if latest_dist:
    if parse_semver(plugin_version) < parse_semver(latest_dist):
        print(f"Plugin version {plugin_version} is behind latest dist artifact version {latest_dist}.")
        print(f"Bump ICAP_SEO_VERSION to at least {bump_patch(latest_dist)} before packaging.")
        sys.exit(1)
    if parse_semver(plugin_version) == parse_semver(latest_dist) and not force_rebuild_enabled:
        print(f"Plugin version {plugin_version} matches latest dist artifact version {latest_dist}.")
        print(f"Bump ICAP_SEO_VERSION to {bump_patch(latest_dist)} (or set FORCE_REBUILD=1 intentionally).")
        sys.exit(1)

if latest_tag:
    if parse_semver(plugin_version) < parse_semver(latest_tag):
        print(f"Plugin version {plugin_version} is behind latest git tag version {latest_tag}.")
        print(f"Bump ICAP_SEO_VERSION to at least {bump_patch(latest_tag)} before packaging.")
        sys.exit(1)
    if parse_semver(plugin_version) == parse_semver(latest_tag) and not force_rebuild_enabled:
        print(f"Plugin version {plugin_version} matches latest git tag version {latest_tag}.")
        print(f"Bump ICAP_SEO_VERSION to {bump_patch(latest_tag)} before packaging a new release.")
        sys.exit(1)
PY
rm -f "${ZIP_PATH}"
rm -f "${LEGACY_ZIP_PATH}"
rm -f "${SHA_PATH}"

(
  cd "${REPO_ROOT}/wordpress-plugin"
  zip -rq "${ZIP_PATH}" "icap-seo"
)

(
  cd "${DIST_DIR}"
  shasum -a 256 "${ZIP_BASENAME}" > "${SHA_PATH##*/}"
)

echo "Built plugin zip:"
echo "  ${ZIP_PATH}"
echo "Checksum:"
echo "  ${SHA_PATH}"
