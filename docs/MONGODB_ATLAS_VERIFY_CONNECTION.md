# 🔍 MongoDB Atlas - Verifica Connessione

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Cluster**: Natan01

---

## 🎯 Metodi di Verifica

### **Metodo 1: Dal Dashboard Atlas (UI)**

1. **Clicca su "Connect"** (pulsante verde accanto a "Natan01")
2. **Scegli "Connect your application"**
3. **Driver**: Seleziona `Python`
4. **Version**: `3.6 or later`
5. **Verifica connection string** - Dovrebbe essere simile a quella configurata

**✅ Se la connection string corrisponde** → Configurazione corretta

---

### **Metodo 2: Test da Codice Python (Automatico)**

**Esegui test connessione:**
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

### **Metodo 3: Verifica Network Access (IP Whitelist)**

1. **Dashboard Atlas** → **Security** (sidebar sinistra)
2. **Database & Network Access**
3. **Network Access** tab
4. Verifica che ci sia:
   - IP corrente (per sviluppo): `188.217.59.169`
   - IP EC2 (per produzione): `13.48.57.194`

**Se manca IP EC2:**
- Clicca **"Add IP Address"**
- Inserisci: `13.48.57.194`
- Comment: `Laravel Forge EC2 - florenceegi-staging`
- Clicca **"Confirm"**

---

### **Metodo 4: Test da Atlas Shell (Opzionale)**

1. **Dashboard Atlas** → **Clusters** → **Natan01**
2. **Connect** → **MongoDB Shell**
3. Segui istruzioni per connettere
4. Esegui: `db.runCommand({ ping: 1 })`
5. Dovresti vedere: `{ "ok" : 1 }`

---

## ✅ Checklist Verifica

- [ ] Connection string corrisponde tra Atlas e `.env`
- [ ] Test Python passa con successo
- [ ] IP whitelist configurata correttamente
- [ ] Cluster "Natan01" mostra "Data Size: 176 KB" (o simile)
- [ ] Nessun errore di connessione

---

**Versione**: 1.0.0  
**Status**: VERIFICATION GUIDE

