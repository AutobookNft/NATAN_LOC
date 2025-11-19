# ⚡ MongoDB Atlas - Quick Setup Guide

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC MongoDB Atlas Setup

---

## 🚀 Setup Rapido (Hai già la Connection String)

### **Opzione 1: Script Automatico (RACCOMANDATO)**

```bash
cd python_ai_service
./scripts/configure_mongodb_atlas.sh
```

Lo script ti chiederà:
1. La connection string MongoDB Atlas
2. Se vuoi testare la connessione

**✅ Vantaggi:**
- Backup automatico del `.env`
- Validazione connection string
- Aggiunge `tls=true` se mancante
- Test connessione opzionale

---

### **Opzione 2: Manuale**

**1. Apri il file `.env`:**
```bash
cd python_ai_service
nano .env
```

**2. Aggiungi o modifica la riga:**
```env
MONGODB_URI=mongodb+srv://natan_user:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

**⚠️ IMPORTANTE:**
- Sostituisci `YOUR_PASSWORD` con la password reale
- Sostituisci `cluster0.xxxxx.mongodb.net` con il tuo cluster
- Assicurati che ci sia `/natan_ai_core` (database name)
- Assicurati che ci sia `&tls=true` alla fine

**3. Salva e esci** (Ctrl+X, Y, Enter)

---

## 🧪 Test Connessione

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

## 📋 Formato Connection String

**Template completo:**
```
mongodb+srv://USERNAME:PASSWORD@CLUSTER.mongodb.net/DATABASE?retryWrites=true&w=majority&tls=true
```

**Esempio:**
```
mongodb+srv://natan_user:MySecurePass123@cluster0.abc123.mongodb.net/natan_ai_core?retryWrites=true&w=majority&tls=true
```

**Componenti:**
- `mongodb+srv://` - Protocollo MongoDB Atlas
- `natan_user` - Username database
- `MySecurePass123` - Password database
- `cluster0.abc123.mongodb.net` - Cluster hostname
- `natan_ai_core` - Database name
- `retryWrites=true&w=majority` - Opzioni replica set
- `tls=true` - SSL/TLS abilitato

---

## 🔍 Troubleshooting

### **Errore: "Authentication failed"**
- Verifica username/password nella connection string
- Verifica che il database user esista in Atlas

### **Errore: "Connection timeout"**
- Verifica IP whitelist in Atlas (deve includere `13.48.57.194`)
- Verifica che il cluster sia attivo

### **Errore: "SSL/TLS error"**
- Verifica che `certifi` sia installato: `pip install certifi`
- Verifica che connection string abbia `tls=true`

---

## ✅ Checklist

- [ ] Connection string configurata in `.env`
- [ ] Password corretta nella connection string
- [ ] Database name (`natan_ai_core`) incluso
- [ ] `tls=true` presente nella connection string
- [ ] IP whitelist configurata in Atlas (`13.48.57.194`)
- [ ] `certifi` installato (`pip install certifi`)
- [ ] Test connessione eseguito con successo

---

**Versione**: 1.0.0  
**Status**: QUICK SETUP GUIDE

