#!/bin/bash
#
# SCRIPT DI PROTEZIONE CODICE - NATAN_LOC
# Verifica e previene perdita accidentale di codice
#

RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "🔒 PROTEZIONE CODICE - NATAN_LOC"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Verifica hook installati
if [ ! -f ".git/hooks/pre-commit" ]; then
    echo -e "${RED}❌ Pre-commit hook NON installato!${NC}"
    echo "   Esegui: chmod +x .git/hooks/pre-commit"
    exit 1
fi

if [ ! -x ".git/hooks/pre-commit" ]; then
    echo -e "${YELLOW}⚠️  Pre-commit hook non eseguibile${NC}"
    echo "   Eseguo: chmod +x .git/hooks/pre-commit"
    chmod +x .git/hooks/pre-commit
fi

echo -e "${GREEN}✅ Pre-commit hook installato${NC}"

if [ ! -f ".git/hooks/pre-push" ]; then
    echo -e "${RED}❌ Pre-push hook NON installato!${NC}"
    echo "   Esegui: chmod +x .git/hooks/pre-push"
    exit 1
fi

if [ ! -x ".git/hooks/pre-push" ]; then
    echo -e "${YELLOW}⚠️  Pre-push hook non eseguibile${NC}"
    echo "   Eseguo: chmod +x .git/hooks/pre-push"
    chmod +x .git/hooks/pre-push
fi

echo -e "${GREEN}✅ Pre-push hook installato${NC}"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo -e "${BLUE}📋 REGOLE DI PROTEZIONE ATTIVE:${NC}"
echo ""
echo "1. ❌ Blocca commit che rimuovono > 100 righe per file"
echo "2. ⚠️  Warning per commit che rimuovono > 50 righe per file"
echo "3. ❌ Blocca commit che rimuovono > 50% del contenuto file"
echo "4. ❌ Blocca commit che rimuovono > 500 righe totali"
echo "5. ❌ Blocca push di commit pericolosi"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo ""
echo -e "${GREEN}✅ Sistema di protezione attivo${NC}"
echo ""
echo "Per bypassare (solo se necessario):"
echo "  ${YELLOW}git commit --no-verify${NC}  (bypass pre-commit)"
echo "  ${YELLOW}git push --no-verify${NC}    (bypass pre-push)"
echo ""

