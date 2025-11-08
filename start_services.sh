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

# Docker-based services (databases + FastAPI)
echo "📦 Starting MongoDB, MariaDB, Redis, FastAPI (Docker)..."
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    if command -v docker-compose &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker-compose"
    elif docker compose version &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker compose"
    else
        echo -e "${RED}✗${NC} Docker Compose not found. Install docker-compose or Docker Compose V2."
        exit 1
    fi

    if [ -d "docker" ] && [ -f "docker/docker-compose.yml" ]; then
        pushd docker > /dev/null
        $DOCKER_COMPOSE_CMD up -d mongodb mariadb redis python_fastapi
        popd > /dev/null
        echo -e "${GREEN}✓${NC} Containers are running"
    else
        echo -e "${RED}✗${NC} docker/docker-compose.yml not found"
        exit 1
    fi
else
    echo -e "${RED}✗${NC} Docker is not running or not installed. Aborting."
    exit 1
fi

PYTHON_PORT=8001
echo $PYTHON_PORT > /tmp/natan_python_port.txt

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




