#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SRC="${REPO_ROOT}/wordpress-plugin/icap-seo"
DIST_DIR="${REPO_ROOT}/dist"
ZIP_PATH="${DIST_DIR}/icap-seo.zip"

if [[ ! -d "${PLUGIN_SRC}" ]]; then
  echo "Plugin source not found: ${PLUGIN_SRC}"
  exit 1
fi

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_PATH}"

(
  cd "${REPO_ROOT}/wordpress-plugin"
  zip -rq "${ZIP_PATH}" "icap-seo"
)

echo "Built plugin zip:"
echo "  ${ZIP_PATH}"
