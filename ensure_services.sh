#!/bin/bash

# NATAN_LOC - Service Health Check and Auto-Start
# Verifica e avvia automaticamente i servizi necessari

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Function to check if port is in use
check_port() {
    local port=$1
    if timeout 0.5 bash -c "echo > /dev/tcp/localhost/$port" 2>/dev/null || lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 0  # Port is in use
    else
        return 1  # Port is free
    fi
}

STARTED_SERVICES=0

# 1. Ensure Python FastAPI container is running
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    if command -v docker-compose &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker-compose"
    elif docker compose version &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker compose"
    else
        DOCKER_COMPOSE_CMD=""
    fi

    if [ -n "$DOCKER_COMPOSE_CMD" ] && [ -d "docker" ] && [ -f "docker/docker-compose.yml" ]; then
        if ! docker ps -q -f name=natan_python_fastapi >/dev/null 2>&1; then
            echo -e "${YELLOW}⚠${NC} Python FastAPI container non in esecuzione"
            echo "   Avvio container..."
            pushd docker > /dev/null
            $DOCKER_COMPOSE_CMD up -d python_fastapi >/dev/null 2>&1
            popd > /dev/null
            STARTED_SERVICES=1
            sleep 5
        else
            echo -e "${GREEN}✓${NC} Python FastAPI container in esecuzione"
        fi
    fi
else
    echo -e "${RED}✗${NC} Docker non disponibile. Impossibile verificare il servizio FastAPI."
fi

PYTHON_PORT=8001
echo $PYTHON_PORT > /tmp/natan_python_port.txt

# 2. Check Frontend Vite
if ! check_port 5173; then
    echo -e "${YELLOW}⚠${NC} Frontend Vite non è in esecuzione"
    echo "   Avvio Frontend Vite..."
    
    cd frontend
    
    # Ensure node_modules exists
    if [ ! -d "node_modules" ]; then
        echo "   Installazione dipendenze frontend..."
        npm install >/dev/null 2>&1
    fi
    
    # Update .env with current Python port
    PYTHON_PORT=$(cat /tmp/natan_python_port.txt 2>/dev/null || echo "9000")
    cat > .env << EOF
VITE_API_BASE_URL=http://localhost:$PYTHON_PORT
EOF
    
    # Start service
    nohup npm run dev > /tmp/natan_frontend.log 2>&1 &
    VITE_PID=$!
    echo $VITE_PID > /tmp/natan_frontend.pid
    echo -e "${GREEN}✓${NC} Frontend Vite avviato (PID: $VITE_PID)"
    
    cd ..
    
    STARTED_SERVICES=1
    
    # Wait for service to be ready
    sleep 3
else
    echo -e "${GREEN}✓${NC} Frontend Vite è già in esecuzione"
fi

# Summary
if [ $STARTED_SERVICES -eq 1 ]; then
    PYTHON_PORT=$(cat /tmp/natan_python_port.txt 2>/dev/null || echo "9000")
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e "${GREEN}✅ Servizi avviati automaticamente${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "📡 Servizi disponibili:"
    echo "   • Python FastAPI: http://localhost:$PYTHON_PORT"
    echo "   • Frontend:       http://localhost:5173"
    echo ""
fi

exit 0
















