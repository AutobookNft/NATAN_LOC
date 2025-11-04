# 🚀 NATAN_LOC - Guida Avvio Servizi

## Quick Start

Per avviare **tutti i servizi** del progetto NATAN_LOC:

```bash
cd /home/fabio/NATAN_LOC
./start_services.sh
```

## Servizi Avviati

Lo script `start_services.sh` avvia automaticamente:

1. **🐳 Docker Services** (se non già avviati):
   - MongoDB (porta 27017)
   - MariaDB (porta 3306)
   - Redis (porta 6379)

2. **🔵 Laravel Backend** (porta 7000):
   - Framework PHP per API e viste
   - URL: http://localhost:7000
   - Logs: `/tmp/natan_laravel.log`

3. **🐍 Python FastAPI** (porta 8001):
   - Servizio AI per USE pipeline
   - URL: http://localhost:8001
   - API Docs: http://localhost:8001/docs
   - Logs: `/tmp/natan_python.log`

4. **⚡ Frontend Vite** (porta 5173):
   - Development server per frontend
   - URL: http://localhost:5173
   - Logs: `/tmp/natan_frontend.log`

## Stop Servizi

Per fermare tutti i servizi:

```bash
./stop_services.sh
```

## Verifica Servizi

Per verificare e avviare automaticamente i servizi se non sono attivi:

```bash
./ensure_services.sh
```

## Requisiti

- **Docker** e **Docker Compose** (per MongoDB, MariaDB, Redis)
- **PHP 8.2+** con Composer
- **Python 3.11+** con pip
- **Node.js 18+** con npm

## Primo Setup

Se è la prima volta che avvii il progetto:

1. **Configura Laravel:**
   ```bash
   cd laravel_backend
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   cd ..
   ```

2. **Installa dipendenze Python:**
   ```bash
   cd python_ai_service
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   deactivate
   cd ..
   ```

3. **Installa dipendenze Frontend:**
   ```bash
   cd frontend
   npm install
   cd ..
   ```

4. **Avvia servizi:**
   ```bash
   ./start_services.sh
   ```

## Configurazione Database

Le credenziali di default (in `docker/docker-compose.yml`):

- **MongoDB:**
  - Host: `localhost:27017`
  - Database: `natan_ai_core`
  - User: `natan_user`
  - Password: `secret_password`

- **MariaDB:**
  - Host: `localhost:3306`
  - Database: `natan_main`
  - User: `natan_user`
  - Password: `secret_password`
  - Root Password: `secret`

- **Redis:**
  - Host: `localhost:6379`
  - No password (default)

## Troubleshooting

### Porta già in uso
Se una porta è già occupata, lo script cerca automaticamente una porta libera.

### Docker non disponibile
Se Docker non è installato, lo script avviserà ma continuerà con gli altri servizi.

### Logs
Controlla i log per errori:
```bash
tail -f /tmp/natan_laravel.log
tail -f /tmp/natan_python.log
tail -f /tmp/natan_frontend.log
```

### Processi in background
Se i servizi non partono, verifica se ci sono già processi in esecuzione:
```bash
ps aux | grep -E "php artisan|uvicorn|vite"
```



