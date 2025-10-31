# NATAN_LOC - Docker Setup

Docker Compose per servizi database e infrastruttura NATAN_LOC.

## Servizi

- **MongoDB 7**: Database cognitivo (embeddings, chat, logs)
- **MariaDB 11**: Database transazionale (users, tenants, acts)
- **Redis 7**: Cache + Queue

## Setup

```bash
# Copia .env.example
cp .env.example .env

# Modifica .env con le tue password
# Poi avvia i servizi
docker-compose up -d

# Verifica che tutti i servizi siano up
docker-compose ps

# Visualizza logs
docker-compose logs -f
```

## Connessioni

- MongoDB: `mongodb://natan_user:password@localhost:27017/natan_ai_core`
- MariaDB: `mysql://natan_user:password@localhost:3306/natan_main`
- Redis: `redis://localhost:6379`

## MongoDB Collections

Le collections vengono create automaticamente all'avvio tramite `init-mongodb.js`:
- `documents` - Document storage
- `sources` - USE pipeline atomic sources
- `claims` - USE pipeline verified claims
- `query_audit` - USE pipeline audit trail
- `natan_chat_messages` - Chat history
- `ai_logs` - AI operation logs
- `analytics` - Aggregated metrics

## Comandi Utili

```bash
# Avvia servizi
docker-compose up -d

# Ferma servizi
docker-compose down

# Ferma servizi e rimuovi volumi (ATTENZIONE: cancella dati!)
docker-compose down -v

# Ricarica configurazione
docker-compose up -d --force-recreate

# Accesso MongoDB shell
docker exec -it natan_mongodb mongosh -u natan_user -p secret_password

# Accesso MariaDB shell
docker exec -it natan_mariadb mysql -u natan_user -p natan_main
```







