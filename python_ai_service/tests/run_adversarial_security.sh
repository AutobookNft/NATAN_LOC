#!/bin/bash
#
# ADVERSARIAL SECURITY TEST RUNNER
# Esegue test adversarial per verificare che l'LLM non inventi mai dati
#
# REQUISITI:
# - Almeno 20 interrogazioni difficili
# - 19/20 devono passare per almeno 10 volte consecutive
# - Solo dopo 10 esecuzioni consecutive con 19/20 pass, il sistema è dichiarato sicuro
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

echo "================================================================================"
echo "🔒 ADVERSARIAL SECURITY TEST SUITE"
echo "================================================================================"
echo ""
echo "Questo test tenta di ingannare l'LLM con domande subdole."
echo "Requisito critico: 19/20 test devono passare per 10 volte consecutive."
echo "Se anche UNO solo inventa dati, il sistema NON è sicuro per PA."
echo ""
echo "================================================================================"
echo ""

# Attiva venv se esiste
if [ -d "venv" ]; then
    source venv/bin/activate
fi

# Esegui test
python -m pytest tests/test_adversarial_security.py::TestAdversarialSecurity::test_full_adversarial_suite -v -s \
    --tb=short \
    --log-cli-level=INFO \
    2>&1 | tee /tmp/adversarial_test_run.log

# Estrai risultati
PASSED=$(grep -c "✅.*PASS" /tmp/adversarial_test_run.log 2>/dev/null || echo "0")
FAILED=$(grep -c "❌.*FAIL" /tmp/adversarial_test_run.log 2>/dev/null || echo "0")

echo ""
echo "================================================================================"
echo "📊 RISULTATI"
echo "================================================================================"
echo "Passed: $PASSED"
echo "Failed: $FAILED"
echo ""

if [ "$PASSED" -ge 19 ]; then
    echo "✅ Test suite: PASS (19/20 richiesti)"
    exit 0
else
    echo "❌ Test suite: FAIL (richiesti almeno 19/20)"
    exit 1
fi






