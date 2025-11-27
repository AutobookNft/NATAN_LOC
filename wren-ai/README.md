# 📊 Wren AI - Text-to-SQL & Infografiche

Piattaforma per generare query SQL e visualizzazioni da linguaggio naturale.

## 🚀 Quick Start

```bash
# Avvia Wren AI
cd /home/fabio/NATAN_LOC/wren-ai
docker-compose up -d

# Verifica stato
docker-compose ps

# Apri UI
# http://localhost:3000
```

## 🔗 Servizi

| Servizio | Porta | Descrizione |
|----------|-------|-------------|
| Wren UI | 3000 | Interfaccia web principale |
| Wren Engine | 8080 | Core engine |
| Wren AI Service | 5556 | API AI |
| Ibis Server | 8000 | SQL execution |
| Qdrant | 6333/6334 | Vector DB |

## 🎯 Funzionalità

### 1. Text-to-SQL
Scrivi domande in italiano, ottieni SQL automatico:
```
"Quanti atti deliberativi sono stati pubblicati nel 2024?"
→ SELECT COUNT(*) FROM atti WHERE tipo='delibera' AND YEAR(data)=2024
```

### 2. Visualizzazioni Automatiche
- Grafici a barre, linee, torta
- Dashboard interattive
- Export PNG/SVG

### 3. Connessione Database
Supporta:
- PostgreSQL
- MySQL
- MongoDB (via connector)
- SQLite
- BigQuery
- Snowflake

## 📡 Connessione a MongoDB NATAN

Per connettere Wren AI al database MongoDB di NATAN:

1. Apri http://localhost:3000
2. Crea nuovo progetto
3. Seleziona "MongoDB" come data source
4. Inserisci connection string:
   ```
   mongodb+srv://fabiocherici_db_user:WAqNtAqykc8raY9Y@natan01.v9jk57p.mongodb.net/natan_ai_core
   ```
5. Seleziona collezioni da analizzare

## 🛑 Stop Services

```bash
cd /home/fabio/NATAN_LOC/wren-ai
docker-compose down

# Per rimuovere anche i volumi:
docker-compose down -v
```

## 📋 Log e Debug

```bash
# Tutti i log
docker-compose logs -f

# Log specifico servizio
docker-compose logs -f wren-ui
docker-compose logs -f wren-ai-service
```

## 🔧 Troubleshooting

### Container non parte
```bash
# Verifica risorse
docker stats

# Riavvia
docker-compose restart
```

### Errore API Key
Verifica che `.env` contenga OPENAI_API_KEY valida.

### Porta già in uso
```bash
# Trova processo
lsof -i :3000
# Termina
kill -9 <PID>
```
