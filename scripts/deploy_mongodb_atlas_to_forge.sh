#!/bin/bash

# ========================================
# 🚀 Deploy MongoDB Atlas Configuration to Forge EC2
# ========================================
# Script per deployare configurazione MongoDB Atlas su Forge EC2
#
# @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
# @version 1.0.0
# @date 2025-01-28
# ========================================

set -euo pipefail

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
FORGE_HOST="13.48.57.194"
FORGE_USER="forge"
FORGE_PATH="/home/forge/default"
PYTHON_SERVICE_PATH="$FORGE_PATH/python_ai_service"

echo -e "${BLUE}🚀 MongoDB Atlas Deployment to Forge EC2${NC}"
echo -e "${BLUE}═══════════════════════════════════════════${NC}\n"

# Check if .env exists locally
if [ ! -f "python_ai_service/.env" ]; then
    echo -e "${RED}❌ File python_ai_service/.env non trovato!${NC}"
    echo -e "${YELLOW}💡 Configura prima MongoDB Atlas connection string localmente${NC}"
    exit 1
fi

# Read MongoDB URI from local .env
MONGODB_URI=$(grep "^MONGODB_URI=" python_ai_service/.env | cut -d'=' -f2- | tr -d '"' || echo "")

if [ -z "$MONGODB_URI" ]; then
    echo -e "${RED}❌ MONGODB_URI non trovato in python_ai_service/.env${NC}"
    exit 1
fi

echo -e "${CYAN}📋 Configuration:${NC}"
echo -e "  Forge Host: ${FORGE_HOST}"
echo -e "  Forge Path: ${FORGE_PATH}"
echo -e "  MongoDB URI: ${MONGODB_URI:0:50}...${NC}\n"

# Check SSH access
echo -e "${CYAN}🔍 Checking SSH access to Forge...${NC}"
if ! ssh -o ConnectTimeout=5 -o BatchMode=yes ${FORGE_USER}@${FORGE_HOST} "echo 'SSH OK'" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  SSH access requires password or key${NC}"
    echo -e "${CYAN}💡 You'll need to run these commands manually:${NC}\n"
    echo -e "${BLUE}Manual deployment steps:${NC}"
    echo -e "  1. ssh ${FORGE_USER}@${FORGE_HOST}"
    echo -e "  2. cd ${PYTHON_SERVICE_PATH}"
    echo -e "  3. nano .env  # Add MONGODB_URI=${MONGODB_URI:0:50}..."
    echo -e "  4. source venv/bin/activate"
    echo -e "  5. pip install certifi"
    echo -e "  6. python3 scripts/test_mongodb_atlas_connection.py"
    exit 0
fi

echo -e "${GREEN}✅ SSH access OK${NC}\n"

# Deploy .env (MongoDB URI only)
echo -e "${CYAN}📤 Deploying MongoDB Atlas configuration...${NC}"

# Create remote .env backup
ssh ${FORGE_USER}@${FORGE_HOST} "cd ${PYTHON_SERVICE_PATH} && cp .env .env.backup.\$(date +%Y%m%d_%H%M%S) 2>/dev/null || true"

# Add MongoDB URI to remote .env
ssh ${FORGE_USER}@${FORGE_HOST} << EOF
cd ${PYTHON_SERVICE_PATH}

# Remove old MongoDB config
sed -i '/^MONGODB_URI=/d' .env
sed -i '/^MONGO_DB_HOST=/d' .env
sed -i '/^MONGO_DB_PORT=/d' .env
sed -i '/^MONGO_DB_DATABASE=/d' .env
sed -i '/^MONGO_DB_USERNAME=/d' .env
sed -i '/^MONGO_DB_PASSWORD=/d' .env

# Add new MongoDB Atlas config
echo "" >> .env
echo "# MongoDB Atlas Configuration (AWS Production)" >> .env
echo "# Deployed: $(date '+%Y-%m-%d %H:%M:%S')" >> .env
echo "MONGODB_URI=${MONGODB_URI}" >> .env

echo "✅ .env updated"
EOF

echo -e "${GREEN}✅ Configuration deployed${NC}\n"

# Install dependencies
echo -e "${CYAN}📦 Installing dependencies...${NC}"
ssh ${FORGE_USER}@${FORGE_HOST} << EOF
cd ${PYTHON_SERVICE_PATH}
source venv/bin/activate
pip install certifi -q
echo "✅ Dependencies installed"
EOF

echo -e "${GREEN}✅ Dependencies installed${NC}\n"

# Test connection
echo -e "${CYAN}🧪 Testing MongoDB Atlas connection...${NC}"
ssh ${FORGE_USER}@${FORGE_HOST} << EOF
cd ${PYTHON_SERVICE_PATH}
source venv/bin/activate
python3 scripts/test_mongodb_atlas_connection.py
EOF

echo -e "\n${GREEN}🎉 Deployment completed!${NC}"
echo -e "${BLUE}💡 Restart Python FastAPI service if needed${NC}"

