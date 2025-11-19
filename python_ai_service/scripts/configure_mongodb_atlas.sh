#!/bin/bash

# ========================================
# 🔧 MongoDB Atlas Connection String Configuration
# ========================================
# Script per configurare MongoDB Atlas connection string nel .env
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
NC='\033[0m'

ENV_FILE=".env"
ENV_PATH=$(pwd)

echo -e "${BLUE}🔧 MongoDB Atlas Connection String Configuration${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}\n"

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

echo -e "${BLUE}Inserisci la MongoDB Atlas connection string:${NC}"
echo -e "${YELLOW}Esempio: mongodb+srv://natan_user:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority${NC}\n"

read -p "Connection String: " MONGODB_URI_INPUT

# Validate connection string
if [[ ! "$MONGODB_URI_INPUT" =~ ^mongodb\+srv:// ]]; then
    echo -e "${YELLOW}⚠️  La connection string dovrebbe iniziare con 'mongodb+srv://'${NC}"
    read -p "Vuoi continuare comunque? (y/n): " CONTINUE
    if [ "$CONTINUE" != "y" ]; then
        echo "Operazione annullata."
        exit 1
    fi
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

# Backup .env
cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
echo -e "${GREEN}✅ Backup .env creato${NC}"

# Remove old MongoDB configuration
sed -i '/^MONGODB_URI=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_HOST=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_PORT=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_DATABASE=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_USERNAME=/d' "$ENV_FILE"
sed -i '/^MONGO_DB_PASSWORD=/d' "$ENV_FILE"

# Add new MongoDB configuration
echo "" >> "$ENV_FILE"
echo "# MongoDB Atlas Configuration (AWS Production)" >> "$ENV_FILE"
echo "MONGODB_URI=$MONGODB_URI_INPUT" >> "$ENV_FILE"

echo -e "\n${GREEN}✅ MongoDB Atlas connection string configurata!${NC}"
echo -e "${BLUE}📝 File aggiornato: $ENV_PATH/$ENV_FILE${NC}\n"

echo -e "${BLUE}🧪 Vuoi testare la connessione ora? (y/n):${NC}"
read -p "> " TEST_CONNECTION

if [ "$TEST_CONNECTION" = "y" ]; then
    echo -e "\n${BLUE}🔍 Testing MongoDB connection...${NC}"
    if [ -f "scripts/test_mongodb_atlas_connection.py" ]; then
        python3 scripts/test_mongodb_atlas_connection.py
    else
        echo -e "${YELLOW}⚠️  Script di test non trovato. Esegui manualmente:${NC}"
        echo -e "   python3 scripts/test_mongodb_atlas_connection.py"
    fi
fi

echo -e "\n${GREEN}🎉 Configurazione completata!${NC}"

