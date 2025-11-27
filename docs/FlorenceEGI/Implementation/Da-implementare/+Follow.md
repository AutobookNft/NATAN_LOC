## **📋 RIEPILOGO REQUIREMENTS DEFINITIVI**

### **🔐 ACCESSO & PERMESSI**

- ✅ **Tutti** gli utenti autenticati possono seguire **tutti** (Creator ↔ Collector)
- ✅ **Consenso obbligatorio** per essere seguiti (nuovo GDPR consent)
- ✅ **Doppio controllo**: UI nascosta + backend validation
- ✅ **Blocco utenti** + **Unfollow** sempre possibile

### **🎨 UI LOCATIONS**

- ✅ **Creator Home**: pulsante già presente (da rendere funzionale)
- ✅ **Collector Home**: aggiungere pulsante +Segui
- ✅ **Creator-card**: aggiungere pulsante
- ✅ **Collector-card**: da creare + aggiungere pulsante

### **📱 COMMUNITY PAGE**

- ✅ **Carousel "Chi seguo"** (pubblico)
- ✅ **Widget Analytics** (follower count, following count, etc.)
- ✅ **Tutte le interazioni follow** concentrate qui

### **🔔 NOTIFICHE**

- ✅ **Creator**: nuova/update collezione → notifica follower
- ✅ **Collector**: nuovo articolo → notifica follower
- ✅ **Sistema configurabile** (immediate/digest)

---

## **🗃️ STRUTTURA DATABASE PROPOSTA**

sql

```sql
-- Tabella principale follows
CREATE TABLE follows (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    follower_id BIGINT UNSIGNED NOT NULL,
    followed_id BIGINT UNSIGNED NOT NULL,
    followed_type ENUM('creator', 'collector') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_follow (follower_id, followed_id, followed_type),
    INDEX idx_follower (follower_id),
    INDEX idx_followed (followed_id, followed_type)
);

-- Tabella blocchi utenti  
CREATE TABLE user_blocks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    blocker_id BIGINT UNSIGNED NOT NULL,
    blocked_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_block (blocker_id, blocked_id)
);

-- Aggiungere consenso alla tabella users
ALTER TABLE users ADD COLUMN can_be_followed BOOLEAN DEFAULT FALSE;

-- Notifiche follow
CREATE TABLE follow_notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    triggered_by_id BIGINT UNSIGNED NOT NULL,
    type ENUM('new_collection', 'updated_collection', 'new_article') NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL, -- ID della collezione/articolo
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_unread (user_id, read_at)
);
```

---

## **🎯 PIANO IMPLEMENTAZIONE**

### **FASE 1: Database & Backend Logic**

1. **Migration** con tabelle sopra
2. **Model** `Follow` con relationships
3. **Service** `FollowService` con logiche business
4. **Controller** `FollowController` con API endpoints

### **FASE 2: Consent & Privacy**

1. **Migration** `can_be_followed` column
2. **Consent form** in Settings utente
3. **Middleware** per controllo consenso
4. **Policy** per follow permissions

### **FASE 3: UI Components**

1. **Follow Button** component riutilizzabile
2. **Following Carousel** component
3. **Analytics Widget** component
4. **Aggiornare** Creator/Collector Home pages

### **FASE 4: Notifiche**

1. **Job** per invio notifiche follow
2. **Mail templates** personalizzate
3. **Settings** notifiche utente
4. **Integration** con eventi collezioni/articoli

---

## **❓ DOMANDE TECNICHE FINALI**

1. **Soft delete** per follows (mantenere storico) o hard delete?
2. **Cache** Redis per follower count o calcolo real-time?
3. **Rate limiting** - max follow/unfollow per ora?
4. **Email** immediata o batch notifiche (es. digest giornaliero)?
5. **Mobile first** - come gestire carousel su mobile?

**Procediamo con la migrazione database o vuoi approfondire altri aspetti?** 🚀