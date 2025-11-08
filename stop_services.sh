#!/bin/bash

# NATAN_LOC - Service Stop Script
# Ferma tutti i servizi avviati da start_services.sh

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "🛑 Stopping NATAN_LOC Services..."
echo ""

# Stop Docker FastAPI container
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    if command -v docker-compose &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker-compose"
    elif docker compose version &> /dev/null; then
        DOCKER_COMPOSE_CMD="docker compose"
    else
        DOCKER_COMPOSE_CMD=""
    fi

    if [ -n "$DOCKER_COMPOSE_CMD" ] && [ -d "docker" ] && [ -f "docker/docker-compose.yml" ]; then
        echo "Stopping FastAPI container..."
        pushd docker > /dev/null
        $DOCKER_COMPOSE_CMD stop python_fastapi >/dev/null 2>&1 || true
        popd > /dev/null
        echo -e "${GREEN}✓${NC} Python FastAPI container stopped"
    fi
fi

rm -f /tmp/natan_python_port.txt 2>/dev/null || true

# Stop Laravel
if [ -f "/tmp/natan_laravel.pid" ]; then
    LARAVEL_PID=$(cat /tmp/natan_laravel.pid)
    if ps -p $LARAVEL_PID > /dev/null 2>&1; then
        echo "Stopping Laravel (PID: $LARAVEL_PID)..."
        kill $LARAVEL_PID 2>/dev/null || true
        rm /tmp/natan_laravel.pid
        echo -e "${GREEN}✓${NC} Laravel stopped"
    else
        rm /tmp/natan_laravel.pid
    fi
else
    echo -e "${YELLOW}⚠${NC} Laravel PID file not found"
fi

# Stop Vite dev server
if [ -f "/tmp/natan_frontend.pid" ]; then
    VITE_PID=$(cat /tmp/natan_frontend.pid)
    if ps -p $VITE_PID > /dev/null 2>&1; then
        echo "Stopping Vite dev server (PID: $VITE_PID)..."
        kill $VITE_PID 2>/dev/null || true
        rm /tmp/natan_frontend.pid
        echo -e "${GREEN}✓${NC} Vite dev server stopped"
    else
        rm /tmp/natan_frontend.pid
    fi
else
    echo -e "${YELLOW}⚠${NC} Vite dev server PID file not found"
fi

# Kill processes on ports if still running
if lsof -Pi :7000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "Killing process on port 7000..."
    kill $(lsof -ti:7000) 2>/dev/null || true
fi

if lsof -Pi :5173 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "Killing process on port 5173..."
    kill $(lsof -ti:5173) 2>/dev/null || true
fi

echo ""
echo -e "${GREEN}✓ All services stopped${NC}"








