#!/bin/bash
# Script per eseguire i test del USE Pipeline
# Verifica la logica e la coerenza di tutte le condizioni

set -e

echo "🧪 NATAN_LOC Test Suite - USE Pipeline Logic Verification"
echo "=========================================================="
echo ""

# Attiva virtualenv se esiste
if [ -d "venv" ]; then
    source venv/bin/activate
    echo "✅ Virtualenv attivato"
fi

# Installa dipendenze test se necessario
echo "📦 Verificando dipendenze test..."
pip install -q pytest pytest-asyncio pytest-cov pytest-mock

echo ""
echo "🚀 Esecuzione test..."
echo ""

# Esegui tutti i test con output verboso
pytest tests/test_use_pipeline_logic.py -v -s --tb=short

echo ""
echo "=========================================================="
echo "✅ Test completati!"

# Opzionale: mostra coverage
if command -v pytest-cov &> /dev/null; then
    echo ""
    echo "📊 Generazione coverage report..."
    pytest tests/test_use_pipeline_logic.py --cov=app.services.use_pipeline --cov-report=term-missing --cov-report=html
    echo "📄 Coverage HTML generato in: htmlcov/index.html"
fi






