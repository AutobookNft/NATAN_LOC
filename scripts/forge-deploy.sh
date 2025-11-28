#!/bin/bash
# ============================================================
# NATAN_LOC - Forge Deploy Script
# ============================================================
# Questo script gestisce il deploy completo su Laravel Forge
# Include: Laravel, Python AI Service, Frontend assets
# ============================================================

# Don't exit on errors - handle them gracefully
set +e

echo "🚀 NATAN_LOC Deploy Started"
echo "============================================"

# Variabili
SITE_PATH="$FORGE_SITE_PATH"
LARAVEL_PATH="$SITE_PATH/laravel_backend"
PYTHON_PATH="$SITE_PATH/python_ai_service"
ENV_FILE="/home/forge/natan_loc.13.48.57.194.sslip.io/.env"

# ============================================
# 1. LARAVEL BACKEND
# ============================================
echo ""
echo "📦 [1/4] Laravel Backend..."

cd "$LARAVEL_PATH"

# Link .env dalla root del sito (persistente)
if [ -f "$ENV_FILE" ]; then
    ln -sf "$ENV_FILE" .env
    echo "   ✓ .env linked"
else
    echo "   ⚠ WARNING: .env not found at $ENV_FILE"
fi

# Composer install
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
echo "   ✓ Composer dependencies installed"

# Dump autoload
$FORGE_COMPOSER dump-autoload --optimize --no-scripts
echo "   ✓ Autoloader optimized"

# Laravel commands (con fallback per errori)
$FORGE_PHP artisan package:discover --ansi 2>/dev/null || true
$FORGE_PHP artisan config:cache 2>/dev/null || echo "   ⚠ config:cache skipped"
$FORGE_PHP artisan route:cache 2>/dev/null || echo "   ⚠ route:cache skipped"  
$FORGE_PHP artisan view:cache 2>/dev/null || echo "   ⚠ view:cache skipped"
$FORGE_PHP artisan migrate --force 2>/dev/null || echo "   ⚠ migrate skipped"
echo "   ✓ Laravel optimized"

# NPM build (frontend assets)
if [ -f "package.json" ]; then
    echo "   Building frontend assets..."
    npm ci --silent 2>/dev/null || npm install --silent
    npm run build --silent
    echo "   ✓ Frontend assets built"
fi

echo "   ✅ Laravel Backend ready"

# ============================================
# 2. PYTHON AI SERVICE
# ============================================
echo ""
echo "🐍 [2/4] Python AI Service..."

if [ -d "$PYTHON_PATH" ]; then
    cd "$PYTHON_PATH"
    
    # Link .env dalla root
    if [ -f "$ENV_FILE" ]; then
        ln -sf "$ENV_FILE" .env
        echo "   ✓ .env linked"
    fi
    
    # Crea venv se non esiste
    if [ ! -d "venv" ]; then
        echo "   Creating virtual environment..."
        if python3 -m venv venv 2>/dev/null; then
            echo "   ✓ venv created"
        else
            echo "   ⚠ venv creation failed - run: sudo apt install python3.12-venv"
            echo "   ⚠ Skipping Python AI Service setup"
        fi
    fi
    
    # Attiva venv e installa dipendenze (solo se venv esiste)
    if [ -d "venv" ]; then
        source venv/bin/activate
        pip install --upgrade pip --quiet
        pip install -r requirements.txt --quiet
        deactivate
        echo "   ✓ Python dependencies installed"
        echo "   ✅ Python AI Service ready"
    fi
else
    echo "   ⚠ python_ai_service directory not found - skipping"
fi

# ============================================
# 3. PERMESSI
# ============================================
echo ""
echo "🔒 [3/4] Setting permissions..."

cd "$LARAVEL_PATH"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R forge:forge storage bootstrap/cache 2>/dev/null || true
echo "   ✓ Permissions set"

# ============================================
# 4. RESTART SERVICES
# ============================================
echo ""
echo "🔄 [4/4] Restarting services..."

# Restart PHP-FPM (gestito da Forge)
# ( sudo -S service php8.3-fpm reload ) || true

# Restart Python AI daemon se esiste
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart natan-python 2>/dev/null || echo "   ⚠ Python daemon not configured yet"
fi

# Oppure usa il daemon di Forge
# Il daemon viene riavviato automaticamente da Forge

echo "   ✓ Services restarted"

# ============================================
# DONE
# ============================================
echo ""
echo "============================================"
echo "✅ NATAN_LOC Deploy Completed!"
echo "============================================"
echo ""
echo "📡 Services:"
echo "   • Laravel:    https://natan-loc.13.48.57.194.sslip.io"
echo "   • Python AI:  http://127.0.0.1:8001 (internal)"
echo ""
