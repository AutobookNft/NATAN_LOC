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

# Function to find first available port
find_free_port() {
    local base_port=$1
    local port=$base_port
    while [ $port -lt $((base_port + 100)) ]; do
        if ! check_port $port; then
            echo $port
            return 0
        fi
        port=$((port + 1))
    done
    echo ""
    return 1
}

STARTED_SERVICES=0

# 1. Check Python FastAPI
PYTHON_PORT=$(cat /tmp/natan_python_port.txt 2>/dev/null || echo "9000")
if ! check_port $PYTHON_PORT; then
    echo -e "${YELLOW}⚠${NC} Python FastAPI non è in esecuzione su porta $PYTHON_PORT"
    echo "   Avvio Python FastAPI..."
    
    cd python_ai_service
    
    # Ensure venv exists
    if [ ! -d "venv" ]; then
        echo "   Creazione virtual environment..."
        python3 -m venv venv
    fi
    
    # Activate and install dependencies if needed
    source venv/bin/activate
    if ! python3 -c "import fastapi" 2>/dev/null; then
        echo "   Installazione dipendenze..."
        pip install -q --upgrade pip
        pip install -q -r requirements.txt
    fi
    
    # Create .env if it doesn't exist
    if [ ! -f ".env" ]; then
        cat > .env << EOF
# MongoDB
MONGO_DB_HOST=localhost
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secret_password

# AI Provider Keys
OPENAI_API_KEY=${OPENAI_API_KEY:-}
ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY:-}
OLLAMA_BASE_URL=http://localhost:11434
EOF
    fi
    
    # Find free port if needed
    if check_port $PYTHON_PORT; then
        PYTHON_PORT=$(find_free_port 9000)
        if [ -z "$PYTHON_PORT" ]; then
            echo -e "${RED}✗${NC} Nessuna porta libera trovata"
            exit 1
        fi
    fi
    
    # Start service
    nohup python3 -m uvicorn app.main:app --host 127.0.0.1 --port $PYTHON_PORT --reload > /tmp/natan_python.log 2>&1 &
    PYTHON_PID=$!
    echo $PYTHON_PID > /tmp/natan_python.pid
    echo $PYTHON_PORT > /tmp/natan_python_port.txt
    echo -e "${GREEN}✓${NC} Python FastAPI avviato su porta $PYTHON_PORT (PID: $PYTHON_PID)"
    
    deactivate
    cd ..
    
    STARTED_SERVICES=1
    
    # Wait for service to be ready
    echo "   Attendo che il servizio sia pronto..."
    sleep 5
    
    # Verify it's responding
    if curl -s http://127.0.0.1:$PYTHON_PORT/healthz >/dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} Python FastAPI risponde correttamente"
    else
        echo -e "${YELLOW}⚠${NC} Python FastAPI avviato ma non risponde ancora"
    fi
else
    echo -e "${GREEN}✓${NC} Python FastAPI è già in esecuzione su porta $PYTHON_PORT"
fi

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
















