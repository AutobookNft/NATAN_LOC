#!/bin/bash
#
# Script per eseguire unit test degli scraper Albo Pretorio Firenze
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

echo "================================================================================"
echo "🧪 TEST SUITE - Scraper Albo Pretorio Firenze"
echo "================================================================================"
echo ""

# Verifica Python e pytest
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 non trovato. Installalo prima di eseguire i test."
    exit 1
fi

if ! python3 -m pytest --version &> /dev/null; then
    echo "📦 Installando pytest e dipendenze..."
    pip3 install pytest pytest-asyncio pytest-mock beautifulsoup4 --quiet
fi

echo "🚀 Esecuzione test..."
echo ""

# Esegui test con output verboso
python3 -m pytest tests/ \
    -v \
    --tb=short \
    --disable-warnings \
    "$@"

echo ""
echo "================================================================================"
echo "✅ Test completati!"
echo "================================================================================"
