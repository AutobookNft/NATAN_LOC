# NATAN_LOC – Setup Locale per Demo Offline

Documento operativo per il copilot dell'IDE installato sul portatile di demo.  
Obiettivo: predisporre **tutti** i servizi necessari a far girare NATAN_LOC in locale (senza deployment cloud) per la presentazione.

---

## 1. Scenario e Assunzioni

- Sistema target: **Ubuntu 22.04/24.04** nativo oppure **WSL2 Ubuntu 22.04**.  
  (Per Windows usare comandi equivalenti dai rispettivi package manager; se non disponibili chiedere istruzioni aggiuntive.)
- Docker è opzionale ma consigliato per gestire database e FastAPI; se non disponibile, seguire installazione nativa indicata.
- È richiesto accesso Internet almeno una volta per installare dipendenze e (se usati) scaricare modelli/chiavi LLM.

---

## 2. Dipendenze di Sistema (APT)

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl wget unzip build-essential pkg-config libssl-dev \
    software-properties-common cmake jq
```

### 2.1 PHP 8.2 + Estensioni Laravel

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-common \
    php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath \
    php8.2-gd php8.2-intl php8.2-sqlite3 php8.2-mysql
```

Verifica:

```bash
php -v
php -m | grep -E "mbstring|curl|gd|bcmath|pdo_mysql"
```

### 2.2 Composer

```bash
EXPECTED_CHECKSUM="$(curl -fsSL https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '$EXPECTED_CHECKSUM') { exit(0); } else { exit(1); }"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
composer --version
```

### 2.3 Node.js 20 LTS (via nvm)

```bash
curl -fsSL https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
source ~/.nvm/nvm.sh
nvm install 20
nvm alias default 20
node -v
npm -v
```

### 2.4 Python 3.12 + Virtualenv

```bash
sudo add-apt-repository -y ppa:deadsnakes/ppa
sudo apt update
sudo apt install -y python3.12 python3.12-venv python3.12-dev python3-pip
python3.12 --version
```

### 2.5 Docker (Opzionale ma Preferito)

```bash
curl -fsSL https://get.docker.com | sudo bash
sudo usermod -aG docker "$USER"
newgrp docker
docker --version
```

Per Docker Compose V2 (se non già incluso):

```bash
sudo apt install -y docker-compose-plugin
docker compose version
```

---

## 3. Database & Servizi di Supporto

### 3.1 Avvio tramite Docker Compose (consigliato)

```bash
cd /path/al/progetto/NATAN_LOC/docker
cp .env.example .env   # se non esiste
# opzionale: aggiornare password/chiavi nel file .env
docker compose up -d mongodb mariadb redis python_fastapi
```

Servizi esposti:

- MongoDB → `localhost:27017`
- MariaDB → `localhost:3306`
- Redis → `localhost:6379`
- FastAPI → `localhost:8001`

Verifica salute:

```bash
docker ps
curl -f http://localhost:8001/healthz
```

### 3.2 Installazione nativa (solo se Docker non utilizzabile)

1. **MongoDB**: seguire `MONGODB_SETUP.md` oppure `MONGOSH_INSTALL_GUIDE.md`.  
   Configurare utente/password `natan_user / secret_password` e DB `natan_ai_core`.
2. **MariaDB**: `sudo apt install mariadb-server` → creare DB, utente e password come in `docs/SETUP_CONFIG.md`.
3. **Redis**: `sudo apt install redis-server` → abilitare avvio automatico (`sudo systemctl enable --now redis-server`).
4. **Python FastAPI**: avviare manualmente (vedi sezione 6) assicurandosi che variabili d’ambiente puntino all’istanza MongoDB nativa.

---

## 4. Clonazione e Struttura

```bash
git clone git@github.com:fabiocherici/NATAN_LOC.git
cd NATAN_LOC
```

Cartelle principali:

- `laravel_backend/` → backend + interfaccia chat integrata
- `python_ai_service/` → servizi AI (RAG, embeddings, bridging Anthropic/OpenAI)
- `docker/` → infrastruttura container (MongoDB, MariaDB, Redis, FastAPI)
- `docs/` → guide di configurazione (es. `SETUP_CONFIG.md`, `MONGODB_SETUP.md`)

---

## 5. Configurazione Laravel Backend

```bash
cd laravel_backend
cp .env.example .env        # se non esiste
composer install
npm install
php artisan key:generate
```

Aggiornare `.env` secondo `docs/SETUP_CONFIG.md`:

```
APP_URL=http://localhost:9000
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=natan_main
DB_USERNAME=natan_user
DB_PASSWORD=secret_password
REDIS_HOST=127.0.0.1
MONGO_URI=mongodb://natan_user:secret_password@localhost:27017/natan_ai_core
```

Eseguire migrazioni (MariaDB deve essere attiva, anche via Docker):

```bash
php artisan migrate
```

Costruire assets (primo giro):

```bash
npm run build
```

---

## 6. Configurazione Python AI Service

```bash
cd ../python_ai_service
python3.12 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
cp .env.example .env   # se disponibile; altrimenti creare partendo da docs/SETUP_CONFIG.md
```

Valorizzare `.env` con:

```
MONGO_URI=mongodb://natan_user:secret_password@localhost:27017/natan_ai_core
MONGO_DB_NAME=natan_ai_core
OPENAI_API_KEY=...
ANTHROPIC_API_KEY=...
OLLAMA_HOST=http://localhost:11434   # opzionale se si usa LLM locale
```

Avvio servizio (porta 8001):

```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8001
```

**Nota offline**: se l’accesso Internet durante la demo non è garantito, preparare in anticipo:

- Chiavi valide per OpenAI/Anthropic **oppure** configurare un LLM locale via Ollama.
- Pre-caricare embeddings e documenti (vedi sezione 7).

---

## 7. Popolamento Dataset RAG (facoltativo ma raccomandato)

Lo script `python_ai_service/app/scripts/import_egi_docs_to_mongodb.py` importa documenti EGI (path predefinito `/home/fabio/EGI/docs`).

1. Copiare i file sorgente della documentazione EGI sul portatile (aggiornare il path nello script o creare la stessa struttura).
2. Garantire che `OPENAI_API_KEY` e/o `ANTHROPIC_API_KEY` siano attivi (necessari per generare embedding).
3. Eseguire:
   ```bash
   cd python_ai_service
   source venv/bin/activate
   python app/scripts/import_egi_docs_to_mongodb.py
   ```
4. Verifica import:
   ```bash
   python3 <<'EOF'
   from app.services.mongodb_service import MongoDBService
   MongoDBService._client = None
   MongoDBService._connected = False
   print("MongoDB connesso:", MongoDBService.is_connected())
   docs = MongoDBService.find_documents("documents", {"tenant_id": 1})
   print("Documenti indexati:", len(docs))
   EOF
   ```

Se necessario aggiornare `EGI/docs` → modificare costante nel file di script o passare percorso via variabile d’ambiente (aggiungere supporto prima della demo).

---

## 8. Avvio Servizi per la Demo

### Opzione A – tutto con Docker + processi locali

1. `docker compose up -d mongodb mariadb redis python_fastapi`
2. Terminale 1:
   ```bash
   cd laravel_backend
   php artisan serve --host=0.0.0.0 --port=9000
   ```
3. Terminale 2:
   ```bash
   cd laravel_backend
   npm run dev
   ```
4. Accesso web: `http://localhost:9000/natan/chat`

### Opzione B – tutto manuale senza Docker

1. Avviare MongoDB/MariaDB/Redis tramite `systemctl` (vedi sezione 3.2).
2. Terminale 1 (Python):
   ```bash
   cd python_ai_service
   source venv/bin/activate
   uvicorn app.main:app --host 0.0.0.0 --port 8001
   ```
3. Terminale 2 (Laravel backend):
   ```bash
   cd laravel_backend
   php artisan serve --host=0.0.0.0 --port=9000
   ```
4. Terminale 3 (Vite hot-reload):
   ```bash
   cd laravel_backend
   npm run dev
   ```

---

## 9. Checklist Pre-Demo

- [ ] `docker ps` (o `systemctl status`) conferma che MongoDB, MariaDB, Redis sono attivi.
- [ ] `curl -f http://localhost:8001/healthz` → FastAPI OK.
- [ ] `php artisan migrate:status` → tutte le migrazioni applicate.
- [ ] `npm run build` completato almeno una volta (per fallback senza dev server).
- [ ] Account demo e dati di esempio presenti (seed manuale o import script).
- [ ] Variabili `.env` impostate su valori coerenti (nessuna key mancante).
- [ ] Browser pronto su `http://localhost:9000/natan/chat`.

---

## 10. Troubleshooting Rapido

- **APP_KEY mancante** → eseguire `php artisan key:generate` (vedi `docs/TROUBLESHOOTING_APP_KEY.md`).
- **Rate limit o errori RAG** → controllare log in `fastapi.log` e `storage/logs/laravel.log`.
- **MongoDB non raggiungibile** → usare script `mongodb_connect.sh` o verificare credenziali in `docs/SETUP_CONFIG.md`.
- **Dipendenze mancanti** → rieseguire `composer install`, `npm install`, `pip install -r requirements.txt`.

---

## 11. Riferimenti Utili

- `docs/SETUP_CONFIG.md` – Variabili d’ambiente dettagliate.
- `MONGODB_SETUP.md` / `MONGOSH_INSTALL_GUIDE.md` – Installazione MongoDB.
- `INSTALL_MONGOSH.md`, `MONGODB_COMPASS_INSTALL.md` – Strumenti client Mongo.
- `README_SETUP.md` – Quick start frontend integrato.
- `start_services.sh` / `stop_services.sh` – Automazione di avvio/arresto (aggiornare porte se necessario).

---

**Nota finale**: tutte le credenziali demo devono rimanere nelle `.env` locali del portatile. Nessun secret deve essere committato. Prima della presentazione eseguire uno smoke test completo (query layer 0 + query analitica → RAG) per confermare la catena end-to-end.
