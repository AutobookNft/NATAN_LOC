#!/bin/bash

# NATAN_LOC - Service Startup Script
# Avvia tutti i servizi necessari per testare il frontend

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 Starting NATAN_LOC Services..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to check if port is in use
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        return 0  # Port is in use
    else
        return 1  # Port is free
    fi
}

# Function to find first available port starting from a base port
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
    echo ""  # No free port found
    return 1
}

# 1. Check/Start MongoDB
echo "📦 Checking MongoDB..."
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    if check_port 27017; then
        echo -e "${GREEN}✓${NC} MongoDB (27017) is running"
    else
        echo -e "${YELLOW}⚠${NC} MongoDB (27017) not running - starting Docker services..."
        if [ -d "docker" ] && [ -f "docker/docker-compose.yml" ]; then
            cd docker
            # Check if docker-compose or docker compose (v2)
            if command -v docker-compose &> /dev/null; then
                docker-compose up -d mongodb mariadb redis
            elif docker compose version &> /dev/null; then
                docker compose up -d mongodb mariadb redis
            else
                echo -e "${RED}✗${NC} Docker Compose not found"
                cd ..
                exit 1
            fi
            echo "Waiting 5 seconds for services to start..."
            sleep 5
            cd ..
        else
            echo -e "${RED}✗${NC} Docker compose file not found in docker/ directory"
            echo -e "${YELLOW}⚠${NC} MongoDB will not be available - semantic search disabled"
        fi
    fi
else
    echo -e "${YELLOW}⚠${NC} Docker not available - assuming databases are running externally"
    echo "   Make sure MongoDB (27017), MariaDB (3306), Redis (6379) are accessible"
fi

# 2. Python FastAPI Service
echo ""
echo "🐍 Starting Python FastAPI service..."

# Find first available port starting from 8001
PYTHON_PORT=$(find_free_port 8001)
if [ -z "$PYTHON_PORT" ]; then
    echo -e "${RED}✗${NC} No free port found in range 8001-8100"
    exit 1
fi

if check_port $PYTHON_PORT; then
    echo -e "${GREEN}✓${NC} Python FastAPI ($PYTHON_PORT) is already running"
else
    cd python_ai_service
    
    # Check if venv exists, create if not
    if [ ! -d "venv" ]; then
        echo "Creating Python virtual environment..."
        python3 -m venv venv
    fi
    
    # Activate venv and install dependencies
    source venv/bin/activate
    echo "Installing/updating Python dependencies..."
    pip install -q --upgrade pip
    pip install -q -r requirements.txt
    
    # Create .env if it doesn't exist
    if [ ! -f ".env" ]; then
        echo "Creating .env file for Python service..."
        cat > .env << EOF
# MongoDB
MONGO_DB_HOST=localhost
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=secret_password

# AI Provider Keys (optional for testing)
OPENAI_API_KEY=${OPENAI_API_KEY:-}
ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY:-}
OLLAMA_BASE_URL=http://localhost:11434
EOF
        echo -e "${YELLOW}⚠${NC} Created .env file - you may need to add API keys"
    fi
    
    # Start FastAPI in background
    echo "Starting Python FastAPI on http://localhost:$PYTHON_PORT..."
    nohup uvicorn app.main:app --host 0.0.0.0 --port $PYTHON_PORT --reload > /tmp/natan_python.log 2>&1 &
    PYTHON_PID=$!
    echo $PYTHON_PID > /tmp/natan_python.pid
    echo -e "${GREEN}✓${NC} Python FastAPI started (PID: $PYTHON_PID)"
    echo "   Logs: /tmp/natan_python.log"
    sleep 2
    
    deactivate
    cd ..
    
    # Save port to file for frontend
    echo $PYTHON_PORT > /tmp/natan_python_port.txt
fi

# Load saved port if service was already running
if [ -f "/tmp/natan_python_port.txt" ]; then
    PYTHON_PORT=$(cat /tmp/natan_python_port.txt)
fi

# 3. Laravel Backend
echo ""
echo "🔵 Starting Laravel backend..."

LARAVEL_PORT=7000
if check_port $LARAVEL_PORT; then
    echo -e "${GREEN}✓${NC} Laravel ($LARAVEL_PORT) is already running"
else
    cd laravel_backend
    
    # Check if .env exists
    if [ ! -f ".env" ]; then
        echo -e "${YELLOW}⚠${NC} .env file not found - copying from .env.example"
        if [ -f ".env.example" ]; then
            cp .env.example .env
            php artisan key:generate >/dev/null 2>&1 || true
            echo -e "${YELLOW}⚠${NC} Please configure .env file before continuing"
        else
            echo -e "${RED}✗${NC} .env.example not found"
            cd ..
            exit 1
        fi
    fi
    
    # Check if APP_KEY is set and not empty
    APP_KEY=$(grep "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2- | tr -d '[:space:]' || echo "")
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ] || [ "$APP_KEY" = "null" ]; then
        echo -e "${YELLOW}⚠${NC} APP_KEY is missing or empty - generating new key..."
        php artisan key:generate --force >/dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓${NC} APP_KEY generated successfully"
        else
            echo -e "${RED}✗${NC} Failed to generate APP_KEY"
            cd ..
            exit 1
        fi
    else
        echo -e "${GREEN}✓${NC} APP_KEY is configured"
    fi
    
    # Install dependencies if needed
    if [ ! -d "vendor" ]; then
        echo "Installing Laravel dependencies..."
        composer install --no-interaction --prefer-dist
    fi
    
    # Run migrations if needed (optional - comment out if you want to run manually)
    # php artisan migrate --force
    
    # Start Laravel in background
    echo "Starting Laravel on http://localhost:$LARAVEL_PORT..."
    nohup php artisan serve --host=0.0.0.0 --port=$LARAVEL_PORT > /tmp/natan_laravel.log 2>&1 &
    LARAVEL_PID=$!
    echo $LARAVEL_PID > /tmp/natan_laravel.pid
    echo -e "${GREEN}✓${NC} Laravel started (PID: $LARAVEL_PID)"
    echo "   Logs: /tmp/natan_laravel.log"
    sleep 2
    
    cd ..
fi

# 4. Frontend Vite Dev Server
echo ""
echo "⚡ Starting Frontend Vite dev server..."

if check_port 5173; then
    echo -e "${GREEN}✓${NC} Vite dev server (5173) is already running"
else
    cd frontend
    
    # Check if node_modules exists
    if [ ! -d "node_modules" ]; then
        echo "Installing frontend dependencies..."
        npm install
    fi
    
    # Update .env with current Python port
    # Load port from saved file or use default
    if [ -f "/tmp/natan_python_port.txt" ]; then
        PYTHON_PORT=$(cat /tmp/natan_python_port.txt)
    else
        PYTHON_PORT=8001
    fi
    
    # Create or update .env file for frontend
    cat > .env << EOF
VITE_API_BASE_URL=http://localhost:$PYTHON_PORT
EOF
    echo "Updated frontend .env with Python port: $PYTHON_PORT"
    
    # Start Vite in background
    echo "Starting Vite dev server on http://localhost:5173..."
    nohup npm run dev > /tmp/natan_frontend.log 2>&1 &
    VITE_PID=$!
    echo $VITE_PID > /tmp/natan_frontend.pid
    echo -e "${GREEN}✓${NC} Vite dev server started (PID: $VITE_PID)"
    echo "   Logs: /tmp/natan_frontend.log"
    cd ..
fi

# Summary
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✓ All services started!${NC}"
echo ""
echo "📡 Services:"
echo "   • Laravel Backend: http://localhost:7000"
echo "   • Python FastAPI:  http://localhost:8001"
echo "   • Frontend:        http://localhost:5173"
echo "   • API Docs:        http://localhost:8001/docs"
echo "   • Health Check:    http://localhost:8001/healthz"
echo ""
echo "📝 Logs:"
echo "   • Laravel:  tail -f /tmp/natan_laravel.log"
echo "   • Python:   tail -f /tmp/natan_python.log"
echo "   • Frontend: tail -f /tmp/natan_frontend.log"
echo ""
echo "🛑 To stop services:"
echo "   ./stop_services.sh"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"




