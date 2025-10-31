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

# Stop Python FastAPI
if [ -f "/tmp/natan_python.pid" ]; then
    PYTHON_PID=$(cat /tmp/natan_python.pid)
    if ps -p $PYTHON_PID > /dev/null 2>&1; then
        echo "Stopping Python FastAPI (PID: $PYTHON_PID)..."
        kill $PYTHON_PID 2>/dev/null || true
        rm /tmp/natan_python.pid
        echo -e "${GREEN}✓${NC} Python FastAPI stopped"
    else
        rm /tmp/natan_python.pid
    fi
else
    echo -e "${YELLOW}⚠${NC} Python FastAPI PID file not found"
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
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "Killing process on port 8000..."
    kill $(lsof -ti:8000) 2>/dev/null || true
fi

if lsof -Pi :5173 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "Killing process on port 5173..."
    kill $(lsof -ti:5173) 2>/dev/null || true
fi

echo ""
echo -e "${GREEN}✓ All services stopped${NC}"



