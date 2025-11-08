#!/bin/bash

set -euo pipefail

ROOT_DIR="/home/fabio/NATAN_LOC"
SCRIPT_PATH="python_ai_service/app/scripts/diagnose_retrieval.py"
VENV_PYTHON="$ROOT_DIR/python_ai_service/venv/bin/python"

cd "$ROOT_DIR"

if [ -x "$VENV_PYTHON" ]; then
  PYTHON_EXEC="$VENV_PYTHON"
else
  PYTHON_EXEC="$(command -v python3 || command -v python)"
fi

if [ -z "${PYTHON_EXEC:-}" ]; then
  echo "Python non disponibile. Installa python3 o crea il venv."
  exit 1
fi

"$PYTHON_EXEC" "$SCRIPT_PATH" --tenant-id 2 --query "Analizza le principali aree di investimento del Comune negli ultimi 12 mesi"
