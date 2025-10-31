# NATAN_LOC - Configuration Guide

## Environment Variables

### Laravel Backend (.env)

```env
APP_NAME="NATAN_LOC"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database (MariaDB)
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=natan_main
DB_USERNAME=natan_user
DB_PASSWORD=secret_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_DB=0
REDIS_CACHE_DB=1

# MongoDB (via Python FastAPI)
MONGO_URI=mongodb://natan_user:secret_password@localhost:27017/natan_ai_core
MONGO_DB_NAME=natan_ai_core

# Ultra Packages
ULTRA_LOG_MANAGER_LOG_CHANNEL=upload
ULTRA_LOG_MANAGER_LOG_LEVEL=debug
ULTRA_LOG_MANAGER_DEVTEAM_EMAIL=devteam@example.com

# Multi-tenancy
TENANCY_DRIVER=single-database
```

### Python AI Service (.env)

```env
# MongoDB
MONGO_URI=mongodb://natan_user:secret_password@localhost:27017/natan_ai_core
MONGO_DB_NAME=natan_ai_core

# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...

# Anthropic
ANTHROPIC_API_KEY=sk-ant-...

# Ollama (Local Mode)
OLLAMA_HOST=http://localhost:11434

# AI Policy
AI_POLICY_FILE=config/ai_policies.yaml
```

### Docker (.env)

```env
# MongoDB
MONGO_ROOT_PASSWORD=secret_password

# MariaDB
MYSQL_ROOT_PASSWORD=secret
MYSQL_DATABASE=natan_main
MYSQL_USER=natan_user
MYSQL_PASSWORD=secret_password
```

## Setup Steps

1. **Avvia Docker services:**
   ```bash
   cd docker
   cp .env.example .env
   # Modifica .env con le tue password
   docker-compose up -d
   ```

2. **Configura Laravel:**
   ```bash
   cd laravel_backend
   cp .env.example .env
   php artisan key:generate
   # Modifica .env con le configurazioni sopra
   php artisan migrate
   ```

3. **Configura Python:**
   ```bash
   cd python_ai_service
   cp .env.example .env
   # Modifica .env con le configurazioni sopra
   source venv/bin/activate
   uvicorn app.main:app --reload
   ```

## Database Connections

- **MariaDB**: `mysql://natan_user:password@localhost:3306/natan_main`
- **MongoDB**: `mongodb://natan_user:password@localhost:27017/natan_ai_core`
- **Redis**: `redis://localhost:6379`

