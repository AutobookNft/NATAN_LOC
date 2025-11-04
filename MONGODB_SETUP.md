# 🔧 MongoDB Setup e Troubleshooting

## Problema: MongoDB non disponibile

**Cosa succede:**
- MongoDB non è installato o non è in esecuzione sul sistema
- Il servizio Python tenta di connettersi a `localhost:27017` ma non trova nulla
- Il sistema ha **fallback automatico** quindi funziona comunque (senza vector search)

## Perché MongoDB non è sempre disponibile?

1. **Non installato**: MongoDB non è installato sul sistema WSL
2. **Non avviato**: MongoDB è installato ma il servizio non è in esecuzione
3. **Configurazione**: Porta o host configurati in modo diverso
4. **Docker**: Se usi Docker, il container potrebbe non essere avviato

## Soluzioni

### Opzione 1: Installare MongoDB su WSL (Ubuntu)

```bash
# Install MongoDB Community Edition
curl -fsSL https://www.mongodb.org/static/pgp/server-7.0.asc | sudo gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg --dearmor

echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/7.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-7.0.list

sudo apt-get update
sudo apt-get install -y mongodb-org

# Avvia MongoDB
sudo systemctl start mongod
sudo systemctl enable mongod  # Avvio automatico al boot
```

### Opzione 2: Usare Docker (Consigliato per sviluppo)

```bash
# Crea file docker-compose.yml nella root del progetto
cat > docker-compose.mongodb.yml << 'EOF'
version: '3.8'
services:
  mongodb:
    image: mongo:7.0
    container_name: natan_mongodb
    restart: unless-stopped
    ports:
      - "27017:27017"
    environment:
      MONGO_INITDB_ROOT_USERNAME: natan_user
      MONGO_INITDB_ROOT_PASSWORD: natan_pass
      MONGO_INITDB_DATABASE: natan_ai_core
    volumes:
      - mongodb_data:/data/db
    networks:
      - natan_network

volumes:
  mongodb_data:

networks:
  natan_network:
    driver: bridge
EOF

# Avvia MongoDB
docker-compose -f docker-compose.mongodb.yml up -d

# Verifica
docker ps | grep mongo
```

### Opzione 3: MongoDB Atlas (Cloud - Consigliato per produzione)

1. Crea account su [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Crea un cluster gratuito
3. Ottieni connection string
4. Aggiorna `.env`:

```env
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=your_username
MONGO_DB_PASSWORD=your_password
MONGODB_URI=mongodb+srv://username:password@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority
```

## Verifica Connessione

```bash
# Test rapido
cd python_ai_service
python3 -c "from app.services.mongodb_service import MongoDBService; print('MongoDB connesso:', MongoDBService.is_connected())"
```

## Avvio Automatico MongoDB

### Con systemd (se installato nativamente):

```bash
sudo systemctl enable mongod
sudo systemctl start mongod
```

### Con Docker:

```bash
# Aggiungi a start_services.sh
docker-compose -f docker-compose.mongodb.yml up -d
```

## Impatto sul Sistema

**Con MongoDB:**
- ✅ Vector search funziona
- ✅ Semantic search per conversational responses
- ✅ Migliori performance su grandi dataset
- ✅ Embeddings salvati e indicizzati

**Senza MongoDB (fallback):**
- ✅ Sistema funziona comunque
- ⚠️ Solo pattern matching (no semantic search)
- ⚠️ Embeddings non salvati (generati ogni volta)
- ⚠️ Performance degradano con molte risposte

## Raccomandazione

Per sviluppo locale: **Docker** (Opzione 2)
- Facile da gestire
- Isolato dal sistema
- Facile da riavviare

Per produzione: **MongoDB Atlas** (Opzione 3)
- Gestito automaticamente
- Backup automatici
- Scalabile
















