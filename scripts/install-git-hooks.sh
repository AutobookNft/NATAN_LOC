#!/bin/bash
#
# SCRIPT INSTALLAZIONE GIT HOOKS - NATAN_LOC
# Installa pre-commit e pre-push hooks per protezione codice
#
# Uso: bash scripts/install-git-hooks.sh (dalla root del progetto)
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  🔒 GIT HOOKS INSTALLER - NATAN_LOC"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Verifica di essere nella root del progetto
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ ERRORE: Esegui questo script dalla root del progetto Git${NC}"
    echo ""
    echo "   Esempio:"
    echo "   cd /home/fabio/NATAN_LOC"
    echo "   bash scripts/install-git-hooks.sh"
    echo ""
    exit 1
fi

# Verifica che la directory sorgente esista
if [ ! -d "git-hooks" ]; then
    echo -e "${RED}❌ ERRORE: Directory git-hooks/ non trovata${NC}"
    echo ""
    echo "   Assicurati che git-hooks/ esista con i file:"
    echo "   - git-hooks/pre-commit"
    echo "   - git-hooks/pre-push"
    echo ""
    exit 1
fi

HOOKS_DIR=".git/hooks"

# Backup hook esistenti se presenti
BACKUP_CREATED=0

if [ -f "$HOOKS_DIR/pre-commit" ]; then
    BACKUP_FILE="$HOOKS_DIR/pre-commit.backup.$(date +%Y%m%d_%H%M%S)"
    echo -e "${YELLOW}⚠️  Hook pre-commit esistente trovato${NC}"
    cp "$HOOKS_DIR/pre-commit" "$BACKUP_FILE"
    echo "   📦 Backup creato: $(basename $BACKUP_FILE)"
    BACKUP_CREATED=1
fi

if [ -f "$HOOKS_DIR/pre-push" ]; then
    BACKUP_FILE="$HOOKS_DIR/pre-push.backup.$(date +%Y%m%d_%H%M%S)"
    echo -e "${YELLOW}⚠️  Hook pre-push esistente trovato${NC}"
    cp "$HOOKS_DIR/pre-push" "$BACKUP_FILE"
    echo "   📦 Backup creato: $(basename $BACKUP_FILE)"
    BACKUP_CREATED=1
fi

if [ "$BACKUP_CREATED" -eq 1 ]; then
    echo ""
fi

echo "📥 Installazione hooks..."
echo ""

# Contatore successi
SUCCESS_COUNT=0

# Installa pre-commit
if [ -f "git-hooks/pre-commit" ]; then
    cp "git-hooks/pre-commit" "$HOOKS_DIR/pre-commit"
    chmod +x "$HOOKS_DIR/pre-commit"
    echo -e "   ${GREEN}✅ pre-commit${NC} hook installato"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo -e "   ${RED}❌ pre-commit${NC} - File git-hooks/pre-commit non trovato"
fi

# Installa pre-push
if [ -f "git-hooks/pre-push" ]; then
    cp "git-hooks/pre-push" "$HOOKS_DIR/pre-push"
    chmod +x "$HOOKS_DIR/pre-push"
    echo -e "   ${GREEN}✅ pre-push${NC} hook installato"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo -e "   ${RED}❌ pre-push${NC} - File git-hooks/pre-push non trovato"
fi

echo ""

if [ "$SUCCESS_COUNT" -eq 2 ]; then
    echo "════════════════════════════════════════════════════════════════"
    echo -e "${GREEN}✅ INSTALLAZIONE COMPLETATA CON SUCCESSO${NC}"
    echo "════════════════════════════════════════════════════════════════"
    echo ""
    echo "📍 Hooks installati in: $HOOKS_DIR"
    echo ""
    echo "🧪 Test hook:"
    echo "   git commit --allow-empty -m '[TEST] Test pre-commit hook'"
    echo "   git push --dry-run"
    echo ""
    echo "🔧 Per disabilitare temporaneamente:"
    echo "   git commit --no-verify -m 'Messaggio'"
    echo "   git push --no-verify"
    echo ""
    echo "📚 Documentazione completa:"
    echo "   cat docs/git-hooks/README.md"
    echo ""
else
    echo "════════════════════════════════════════════════════════════════"
    echo -e "${YELLOW}⚠️  INSTALLAZIONE PARZIALE${NC}"
    echo "════════════════════════════════════════════════════════════════"
    echo ""
    echo "Hook installati: $SUCCESS_COUNT / 2"
    echo ""
    echo "Verifica che i file sorgente esistano in git-hooks/"
    echo ""
fi

# Verifica finale
echo "🔍 Verifica installazione:"
echo ""

if [ -x "$HOOKS_DIR/pre-commit" ]; then
    echo -e "   ${GREEN}✅ pre-commit${NC} - eseguibile"
else
    echo -e "   ${RED}❌ pre-commit${NC} - non eseguibile o mancante"
fi

if [ -x "$HOOKS_DIR/pre-push" ]; then
    echo -e "   ${GREEN}✅ pre-push${NC} - eseguibile"
else
    echo -e "   ${RED}❌ pre-push${NC} - non eseguibile o mancante"
fi

echo ""
echo "════════════════════════════════════════════════════════════════"
echo ""

