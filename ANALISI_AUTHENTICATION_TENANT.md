# 🔍 ANALISI AUTHENTICATION & TENANT MANAGEMENT

**Data**: 2025-11-02  
**Problema**: Verificare come gestisco autenticazione e tenant_id in NATAN_LOC

---

## ❓ DOMANDE CRITICHE

### 1. **Modello User**
**Situazione attuale:**
- Ho creato `app/Models/User.php` in NATAN_LOC con campo `tenant_id`
- Il database EGI condiviso ha già una tabella `users`
- Il database config punta a `database: 'EGI'` (database condiviso)

**Domanda:**
- Devo usare il modello User di EGI (esistente) o quello che ho creato?
- La tabella `users` in EGI ha già il campo `tenant_id` o devo aggiungerlo?
- Il modello User in NATAN_LOC deve estendere quello di EGI?

---

### 2. **Autenticazione**
**Situazione attuale:**
- Frontend NON ha ancora gestione login
- Controller usa `Auth::user()` che ritorna null se non autenticato
- L'handover dice: "Auth gestita tramite EGI condiviso"

**Domanda:**
- Come funziona l'autenticazione condivisa con EGI?
- La sessione Laravel è condivisa tra EGI e NATAN_LOC?
- Devo implementare un sistema di autenticazione temporaneo per NATAN_LOC?

---

### 3. **Tenant ID**
**Situazione attuale:**
- Frontend TypeScript usa `tenantId: number = 1` hardcoded
- Controller NON usa `tenant_id` da nessuna parte (solo `user_id`)
- `NatanChatMessage` NON ha campo `tenant_id` nella tabella
- L'handover dice: "Multi-tenant con tenant_id injection"

**Domanda:**
- Come ottengo il `tenant_id`? Dall'utente autenticato (`user->tenant_id`)?
- Devo aggiungere `tenant_id` alla tabella `natan_chat_messages`?
- Come gestisco il tenant_id quando l'utente non è autenticato?

---

### 4. **Architettura Multi-Tenant**
**Situazione attuale:**
- L'handover dice: "Setup `stancl/tenancy` middleware" e "Tenancy detection (subdomain/user/API)"
- NON ho implementato middleware tenancy
- NON ho implementato tenant detection

**Domanda:**
- Devo implementare `stancl/tenancy` ora o posso usare `tenant_id` manualmente?
- Come rilevo il tenant? Da subdomain, user, o API key?
- Il tenant_id deve essere preso dall'utente autenticato o da altro?

---

## 🔍 ANALISI CODICE ATTUALE

### **User Model (NATAN_LOC)**
```php
// app/Models/User.php
- Ha campo `tenant_id` in fillable
- Ha relazione `tenant()` con PaEntity
- NON estende User di EGI
```

### **NatanConversationController**
```php
// app/Http/Controllers/NatanConversationController.php
- Usa `Auth::user()` per ottenere utente
- NON usa `tenant_id` da nessuna parte
- Salva solo `user_id` in natan_chat_messages
```

### **ChatInterface.ts (Frontend)**
```typescript
// resources/js/natan/components/ChatInterface.ts
- Usa `tenantId: number = 1` hardcoded
- Passa `tenantId` a `sendUseQuery()`
- NON ottiene tenant_id da user o sessione
```

### **NatanChatMessage Model**
```php
// app/Models/NatanChatMessage.php
- NON ha campo `tenant_id` in fillable
- Usa tabella esistente `natan_chat_messages` da EGI
- La tabella NON ha colonna `tenant_id`
```

---

## ⚠️ PROBLEMI IDENTIFICATI

1. **NON sto usando tenant_id correttamente**
   - Frontend usa tenant_id hardcoded (1)
   - Controller NON usa tenant_id
   - Model NON salva tenant_id

2. **NON sto rispettando architettura multi-tenant**
   - L'handover richiede tenant_id injection ovunque
   - NON ho implementato tenant detection
   - NON ho isolamento tenant

3. **Autenticazione incompleta**
   - Frontend non ha login
   - Controller richiede autenticazione ma utente non può autenticarsi
   - Non so come funziona autenticazione condivisa con EGI

---

## ✅ SOLUZIONE PROPOSTA (da confermare)

### **Opzione 1: Usare tenant_id da User**
- User autenticato ha `tenant_id`
- Controller prende `tenant_id` da `$user->tenant_id`
- Frontend passa `tenant_id` dal backend (non hardcoded)
- Aggiungo colonna `tenant_id` a `natan_chat_messages` se non esiste

### **Opzione 2: Tenant detection temporanea**
- Frontend non autenticato: usa tenant_id da query param o meta tag
- Frontend autenticato: usa tenant_id da user
- Controller accetta tenant_id da request o da user

### **Opzione 3: Usare modello User di EGI**
- Elimino modello User in NATAN_LOC
- Uso modello User di EGI direttamente
- Verifico se User di EGI ha tenant_id

---

## 🛑 REGOLA ZERO

**PRIMA DI PROCEDERE, CHIEDO:**

1. Quale modello User devo usare? EGI o NATAN_LOC?
2. Come funziona autenticazione condivisa con EGI?
3. Come ottengo tenant_id? Da user, subdomain, o altro?
4. Devo aggiungere tenant_id a natan_chat_messages?
5. Devo implementare stancl/tenancy o posso gestire manualmente?

