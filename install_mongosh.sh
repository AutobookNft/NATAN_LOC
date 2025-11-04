#!/bin/bash

# Script automatico per installare mongosh localmente

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "📦 MongoDB Shell (mongosh) Installer"
echo ""

# Cerca il file scaricato
SEARCH_DIRS=(
    "$HOME/Downloads"
    "$HOME"
    "/mnt/c/Users/$USER/Downloads"
    "/mnt/c/Users/*/Downloads"
)

MONGO_FILE=""
for dir in "${SEARCH_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        found=$(find "$dir" -maxdepth 1 -name "*mongosh*.tgz" -o -name "*mongosh*.tar.gz" 2>/dev/null | head -1)
        if [ -n "$found" ]; then
            MONGO_FILE="$found"
            break
        fi
    fi
done

if [ -z "$MONGO_FILE" ]; then
    echo -e "${YELLOW}⚠️ File mongosh non trovato automaticamente${NC}"
    echo ""
    read -p "Inserisci il percorso completo del file mongosh (es. /path/to/mongosh-*.tgz): " MONGO_FILE
fi

if [ ! -f "$MONGO_FILE" ]; then
    echo -e "${RED}❌ File non trovato: $MONGO_FILE${NC}"
    exit 1
fi

echo -e "${GREEN}✅ File trovato: $MONGO_FILE${NC}"
echo ""

# Estrai
echo "📦 Estrazione in corso..."
EXTRACT_DIR=$(mktemp -d)
cd "$EXTRACT_DIR"

if [[ "$MONGO_FILE" == *.zip ]]; then
    unzip -q "$MONGO_FILE"
else
    tar -xzf "$MONGO_FILE"
fi

# Trova la directory estratta
MONGOSH_DIR=$(find . -maxdepth 1 -type d -name "mongosh-*" | head -1)

if [ -z "$MONGOSH_DIR" ]; then
    echo -e "${RED}❌ Directory estratta non trovata${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Estratto: $MONGOSH_DIR${NC}"
echo ""

# Installa in /opt o ~/mongosh
read -p "Dove installare? [1=/opt/mongosh (richiede sudo), 2=~/mongosh]: " install_choice

if [ "$install_choice" = "1" ]; then
    INSTALL_DIR="/opt/mongosh"
    echo "Installing to $INSTALL_DIR (richiede sudo)..."
    sudo rm -rf "$INSTALL_DIR"
    sudo mv "$MONGOSH_DIR" "$INSTALL_DIR"
    sudo chown -R $USER:$USER "$INSTALL_DIR" 2>/dev/null || true
    
    # Crea symlink
    if [ ! -L "/usr/local/bin/mongosh" ]; then
        sudo ln -s "$INSTALL_DIR/bin/mongosh" /usr/local/bin/mongosh
        echo -e "${GREEN}✅ Symlink creato in /usr/local/bin/mongosh${NC}"
    fi
else
    INSTALL_DIR="$HOME/mongosh"
    echo "Installing to $INSTALL_DIR..."
    rm -rf "$INSTALL_DIR"
    mv "$MONGOSH_DIR" "$INSTALL_DIR"
    
    # Aggiungi a PATH
    if ! grep -q "$INSTALL_DIR/bin" "$HOME/.bashrc" 2>/dev/null; then
        echo "" >> "$HOME/.bashrc"
        echo "# MongoDB Shell" >> "$HOME/.bashrc"
        echo "export PATH=\"$INSTALL_DIR/bin:\$PATH\"" >> "$HOME/.bashrc"
        echo -e "${GREEN}✅ PATH aggiornato in ~/.bashrc${NC}"
        echo -e "${YELLOW}💡 Esegui: source ~/.bashrc${NC}"
    fi
fi

# Verifica
if [ -f "$INSTALL_DIR/bin/mongosh" ]; then
    echo ""
    echo -e "${GREEN}✅ Installazione completata!${NC}"
    echo ""
    echo "📍 Installato in: $INSTALL_DIR"
    echo ""
    
    # Test versione
    if command -v mongosh &> /dev/null || [ -f "$INSTALL_DIR/bin/mongosh" ]; then
        echo "🔍 Verifica versione:"
        "$INSTALL_DIR/bin/mongosh" --version
        
        echo ""
        echo "🔌 Per connetterti a MongoDB NATAN_LOC:"
        echo "mongosh \"mongodb://natan_user:secret_password@localhost:27017/natan_ai_core?authSource=admin\""
    else
        echo -e "${YELLOW}⚠️ Aggiungi al PATH: export PATH=\"$INSTALL_DIR/bin:\$PATH\"${NC}"
    fi
else
    echo -e "${RED}❌ Errore installazione${NC}"
    exit 1
fi

# Cleanup
cd /
rm -rf "$EXTRACT_DIR"
















