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

PLUGIN_VERSION="$(sed -nE "s/^define\\('ICAP_SEO_VERSION',[[:space:]]*'([^']+)'\\);/\\1/p" "${PLUGIN_MAIN}" | head -n1)"
if [[ -z "${PLUGIN_VERSION}" ]]; then
  PLUGIN_VERSION="$(sed -nE "s/^ \\* Version:[[:space:]]*([^[:space:]]+).*$/\\1/p" "${PLUGIN_MAIN}" | head -n1)"
fi
if [[ -z "${PLUGIN_VERSION}" ]]; then
  echo "Unable to detect plugin version from: ${PLUGIN_MAIN}"
  exit 1
fi

TAG_VERSION="${PLUGIN_VERSION}"
if [[ "${TAG_VERSION}" != v* ]]; then
  TAG_VERSION="v${TAG_VERSION}"
fi

ZIP_BASENAME="icap-seo-${TAG_VERSION}.zip"
ZIP_PATH="${DIST_DIR}/${ZIP_BASENAME}"
LEGACY_ZIP_PATH="${DIST_DIR}/icap-seo.zip"

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_PATH}"
rm -f "${LEGACY_ZIP_PATH}"

(
  cd "${REPO_ROOT}/wordpress-plugin"
  zip -rq "${ZIP_PATH}" "icap-seo"
)

echo "Built plugin zip:"
echo "  ${ZIP_PATH}"
