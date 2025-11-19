# 🔌 MongoDB Atlas - Ottenere Connection String

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC MongoDB Atlas Setup

---

## 📋 Step 2: Ottenere Connection String

Sei alla schermata **"Choose a connection method"** - Step 2 di 3.

### **✅ Cosa Fare:**

1. **Clicca su "Drivers"** (icona con codice binario 1011)
   - Questo ti darà la connection string per Python

2. **Nella schermata successiva:**
   - **Driver**: Seleziona `Python`
   - **Version**: Seleziona `3.6 or later` (o la versione più recente)

3. **Copia la Connection String**
   - Dovrebbe essere simile a:
   ```
   mongodb+srv://natan_user:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
   ```

4. **Personalizza la Connection String:**
   - Sostituisci `<password>` con la password del database user
   - Aggiungi il database name: `/natan_ai_core`
   - Aggiungi `&tls=true` per sicurezza
   
   **Connection String Finale:**
   ```
   mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
   ```

---

## 🔐 Step 3: Configurare nel Progetto

Una volta ottenuta la connection string, aggiorna il file `.env`:

### **Opzione A: Connection String Completa (RACCOMANDATO)**

```env
MONGODB_URI=mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

### **Opzione B: Componenti Separati**

```env
MONGO_DB_HOST=cluster0.xxxxx.mongodb.net
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=natan_ai_core
MONGO_DB_USERNAME=natan_user
MONGO_DB_PASSWORD=YOUR_PASSWORD
```

**💡 Nota**: Il codice Python supporta entrambe le opzioni. L'opzione A (MONGODB_URI) è preferita.

---

## 🧪 Step 4: Test Connessione

Dopo aver configurato `.env`, testa la connessione:

```bash
cd python_ai_service
source venv/bin/activate
python3 scripts/test_mongodb_atlas_connection.py
```

**Output atteso:**
```
✅ MongoDB connection successful!
✅ Test document inserted
✅ Test document deleted
```

---

## 📝 Checklist

- [ ] Cliccato su "Drivers"
- [ ] Selezionato Python come driver
- [ ] Copiata connection string
- [ ] Personalizzata connection string (password + database name)
- [ ] Aggiornato `.env` con connection string
- [ ] Testato connessione

---

**Versione**: 1.0.0  
**Status**: GUIDE

