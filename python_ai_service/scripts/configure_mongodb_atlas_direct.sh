#!/bin/bash

# ========================================
# 🔧 MongoDB Atlas Connection String Configuration (Direct)
# ========================================
# Script per configurare MongoDB Atlas connection string nel .env
# Versione non interattiva - accetta connection string come parametro
#
# Usage: ./configure_mongodb_atlas_direct.sh "mongodb+srv://..."
#
# @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
# @version 1.0.0
# @date 2025-01-28
# ========================================

set -euo pipefail

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

ENV_FILE=".env"
ENV_PATH=$(pwd)

echo -e "${BLUE}🔧 MongoDB Atlas Connection String Configuration${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}\n"

# Check if connection string provided
if [ $# -eq 0 ]; then
    echo -e "${RED}❌ Errore: Connection string non fornita${NC}"
    echo -e "${YELLOW}Usage: $0 \"mongodb+srv://user:pass@cluster.mongodb.net/database?options\"${NC}"
    exit 1
fi

MONGODB_URI_INPUT="$1"

# Check if .env exists
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${YELLOW}⚠️  File .env non trovato. Creo da env.example...${NC}"
    if [ -f "env.example" ]; then
        cp env.example "$ENV_FILE"
        echo -e "${GREEN}✅ File .env creato${NC}\n"
    else
        echo -e "${YELLOW}❌ File env.example non trovato. Creo .env vuoto...${NC}"
        touch "$ENV_FILE"
    fi
fi

# Validate connection string
if [[ ! "$MONGODB_URI_INPUT" =~ ^mongodb\+srv:// ]]; then
    echo -e "${YELLOW}⚠️  La connection string dovrebbe iniziare con 'mongodb+srv://'${NC}"
    echo -e "${YELLOW}Continuo comunque...${NC}\n"
fi

# Ensure connection string has tls=true
if [[ ! "$MONGODB_URI_INPUT" =~ tls=true ]]; then
    if [[ "$MONGODB_URI_INPUT" =~ \? ]]; then
        MONGODB_URI_INPUT="${MONGODB_URI_INPUT}&tls=true"
    else
        MONGODB_URI_INPUT="${MONGODB_URI_INPUT}?tls=true"
    fi
    echo -e "${GREEN}✅ Aggiunto tls=true alla connection string${NC}"
fi

# Ensure database name is included
if [[ ! "$MONGODB_URI_INPUT" =~ /[^/]+\? ]] && [[ ! "$MONGODB_URI_INPUT" =~ /[^/]+$ ]]; then
    echo -e "${YELLOW}⚠️  Database name non trovato nella connection string${NC}"
    echo -e "${YELLOW}Assicurati che la connection string includa il database name (es: /natan_ai_core)${NC}\n"
fi

# Backup .env
BACKUP_FILE="${ENV_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
cp "$ENV_FILE" "$BACKUP_FILE"
echo -e "${GREEN}✅ Backup .env creato: $BACKUP_FILE${NC}"

# Remove old MongoDB configuration
sed -i '/^MONGODB_URI=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_HOST=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_PORT=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_DATABASE=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_USERNAME=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_PASSWORD=/d' "$ENV_FILE"

# Remove empty lines at end
sed -i '/^$/d' "$ENV_FILE"

# Add new MongoDB configuration
echo "" >> "$ENV_FILE"
echo "# MongoDB Atlas Configuration (AWS Production)" >> "$ENV_FILE"
echo "# Configured: $(date '+%Y-%m-%d %H:%M:%S')" >> "$ENV_FILE"
echo "MONGODB_URI=$MONGODB_URI_INPUT" >> "$ENV_FILE"

echo -e "\n${GREEN}✅ MongoDB Atlas connection string configurata!${NC}"
echo -e "${BLUE}📝 File aggiornato: $ENV_PATH/$ENV_FILE${NC}"
echo -e "${BLUE}💾 Backup salvato: $BACKUP_FILE${NC}\n"

echo -e "${GREEN}🎉 Configurazione completata!${NC}"
echo -e "${BLUE}💡 Esegui: python3 scripts/test_mongodb_atlas_connection.py per testare${NC}"

