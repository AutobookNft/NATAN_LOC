# 📋 PIANO DI LAVORO - MULTI-TENANT IMPLEMENTATION

**Data**: 2025-11-02  
**Basato su**: `/home/fabio/NATAN_LOC/Mettere in pratica (ANALISI_AUTHENTICATION_TENANT).md`

---

## ✅ RISPOSTE ALLE DOMANDE CRITICHE

### 1. **Modello User**

- ✅ **Soluzione**: Tabella `users` condivisa tra EGI e NATAN_LOC (stesso DB)
- ✅ **EGI**: Aggiungere campo `tenant_id` alla tabella `users` esistente
- ✅ **NATAN_LOC**: Può avere modello User locale (namespace diverso) ma usa stessa tabella
- ✅ **Registrazione EGI**: Nuovi utenti avranno `tenant_id = 1` (FlorenceEGI default)
- ✅ **Registrazione NATAN_LOC**: Nuovi utenti avranno `tenant_id = ID ente PA/azienda`

### 2. **Autenticazione**

- ✅ **NATAN_LOC**: Usa **Sanctum token-based** (diverso da EGI che usa Jetstream)
- ✅ **Endpoint**: `/api/session` ritorna `{ user, tenant: { id, slug, domain } }`
- ✅ **Login**: Ritorna anche `tenant_id` nel payload token
- ✅ **Frontend**: Ottiene `tenant_id` da `/api/session`, NON più hardcoded

### 3. **Tenant ID**

- ✅ **Risoluzione** (in ordine):
  1. **Subdomain** (`firenze.natan.loc` → cerca in `pa_entities.slug`)
  2. **User autenticato** (`Auth::user()->tenant_id`)
  3. **Header** (`X-Tenant`)
- ✅ **Controller**: Usa `$user->tenant_id` o `TenantResolver::resolve()`
- ✅ **Frontend**: Legge da `/api/session`, NON hardcoded
- ✅ **Database**: Aggiungere `tenant_id` a `natan_chat_messages`, `natan_user_memories`

### 4. **Architettura Multi-Tenant**

- ✅ **Pattern**: Single-DB con colonna `tenant_id` + `stancl/tenancy`
- ✅ **Trait**: `TenantScoped` per Global Scope automatico
- ✅ **Resolver**: `TenantResolver` per detection (subdomain/user/header)
- ✅ **Service Provider**: `TenantServiceProvider` per iniettare `currentTenantId`
- ✅ **Global Scopes**: Isolamento automatico tenant in tutti i modelli

---

## 🎯 PIANO DI LAVORO - FASI

### **FASE 1: Database Schema (EGI + NATAN_LOC)**

**1.1 Migrazione EGI: Aggiungere tenant_id a users**

```
CONTESTO: /home/fabio/EGI
PERCORSO: database/migrations/2025_11_02_000001_add_tenant_id_to_users_table.php
AZIONE: Aggiungere colonna tenant_id (nullable, index, foreign key su pa_entities)
```

**1.2 Migrazione NATAN_LOC: Creare tabella pa_entities**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: database/migrations/2025_11_02_000000_create_pa_entities_table.php
AZIONE: Creare tabella tenants (id, name, slug, domain, settings, timestamps)
```

**1.3 Migrazione NATAN_LOC: Aggiungere tenant_id a natan_chat_messages**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: database/migrations/2025_11_02_000002_add_tenant_id_to_natan_chat_messages.php
AZIONE: Aggiungere colonna tenant_id (index, foreign key su pa_entities)
```

**1.4 Migrazione NATAN_LOC: Aggiungere tenant_id a natan_user_memories**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: database/migrations/2025_11_02_000003_add_tenant_id_to_natan_user_memories.php
AZIONE: Aggiungere colonna tenant_id (index, foreign key su pa_entities)
```

**1.5 MongoDB: Verificare indici tenant_id**

```
CONTESTO: /home/fabio/NATAN_LOC
AZIONE: Verificare che collections MongoDB abbiano indici su tenant_id
Collections: documents, sources, claims, query_audit
```

---

### **FASE 2: Backend Laravel - Tenant Resolution**

**2.1 Creare TenantResolver**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Resolvers/TenantResolver.php
AZIONE: Creare classe con metodo resolve() (subdomain → user → header)
```

**2.2 Creare Trait TenantScoped**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Traits/TenantScoped.php
AZIONE: Creare trait con Global Scope e auto-set tenant_id in creating()
```

**2.3 Creare TenantServiceProvider**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Providers/TenantServiceProvider.php
AZIONE: Registrare resolver e iniettare currentTenantId nel container
```

**2.4 Configurare stancl/tenancy**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: config/tenancy.php
AZIONE: Configurare tenant_finder, central_domains, middleware
```

**2.5 Installare pacchetto stancl/tenancy**

```
CONTESTO: /home/fabio/NATAN_LOC
AZIONE: composer require stancl/tenancy
```

---

### **FASE 3: Modelli - Applicare TenantScoped**

**3.1 Aggiornare NatanChatMessage**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Models/NatanChatMessage.php
AZIONE: Aggiungere use TenantScoped, aggiungere tenant_id a fillable
```

**3.2 Aggiornare User Model (NATAN_LOC)**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Models/User.php
AZIONE: Verificare che tenant_id sia in fillable (già presente)
```

**3.3 Aggiornare altri modelli tenant-aware**

```
CONTESTO: /home/fabio/NATAN_LOC
AZIONE: Applicare TenantScoped a tutti i modelli che necessitano isolamento
```

---

### **FASE 4: Controllers - Usare tenant_id**

**4.1 Aggiornare NatanConversationController**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Http/Controllers/NatanConversationController.php
AZIONE:
- Ottenere tenant_id da $user->tenant_id o TenantResolver
- Salvare tenant_id quando crea NatanChatMessage
- Verificare tenant_id nelle query
```

**4.2 Aggiornare UseOrchestrator**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Services/USE/UseOrchestrator.php
AZIONE: Verificare che tenant_id sia passato correttamente a Python API
```

**4.3 Creare endpoint /api/session**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: routes/api.php o routes/web.php
AZIONE: Creare endpoint GET /api/session che ritorna { user, tenant }
```

**4.4 Creare LoginController (Sanctum)**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Http/Controllers/Auth/LoginController.php
AZIONE: Creare login endpoint che ritorna token + tenant_id
```

---

### **FASE 5: Frontend - Rimuovere hardcoded tenantId**

**5.1 Creare servizio Session**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: resources/js/natan/services/sessionService.ts
AZIONE: Creare servizio per chiamare /api/session e ottenere tenant_id
```

**5.2 Aggiornare ChatInterface.ts**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: resources/js/natan/components/ChatInterface.ts
AZIONE:
- Rimuovere tenantId hardcoded
- Caricare tenant_id da sessionService all'inizializzazione
- Passare tenant_id corretto a sendUseQuery()
```

**5.3 Aggiornare app.ts**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: resources/js/natan/app.ts
AZIONE: Caricare tenant_id da session invece di hardcoded
```

**5.4 Aggiornare main.ts**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: resources/js/natan/main.ts
AZIONE: Caricare tenant_id da /api/session invece di meta tag hardcoded
```

---

### **FASE 6: EGI - Modifiche Minime**

**6.1 Aggiornare RegisterController (EGI)**

```
CONTESTO: /home/fabio/EGI
PERCORSO: app/Http/Controllers/Auth/RegisteredUserController.php (o equivalente)
AZIONE: Aggiungere $user->tenant_id = 1; durante registrazione
```

**6.2 Aggiornare User Model (EGI)**

```
CONTESTO: /home/fabio/EGI
PERCORSO: app/Models/User.php
AZIONE: Aggiungere 'tenant_id' a fillable (se non presente)
```

---

### **FASE 7: Testing**

**7.1 Test Tenant Isolation**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: tests/Feature/TenantIsolationTest.php
AZIONE: Creare test per verificare che tenant A non vede dati tenant B
```

**7.2 Test Tenant Resolution**

```
CONTESTO: /home/fabio/NATAN_LOC
AZIONE: Testare risoluzione tenant da subdomain, user, header
```

**7.3 Test Frontend Session**

```
CONTESTO: /home/fabio/NATAN_LOC
AZIONE: Verificare che frontend ottiene tenant_id correttamente da /api/session
```

---

## 🔮 FASI FUTURE (DA IMPLEMENTARE)

### **FASE 8: Multi-Tenant Gerarchico (Succursali/Branches)**

> ⚠️ **STATO**: FUTURA IMPLEMENTAZIONE - Non ancora sviluppato

**Obiettivo**: Supportare strutture gerarchiche tenant (es. Comune → Uffici, Azienda → Filiali)

**8.1 Migrazione: Aggiungere parent_tenant_id**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: database/migrations/xxxx_add_parent_tenant_id_to_tenants_table.php
AZIONE: Aggiungere colonna parent_tenant_id (nullable, FK su tenants.id, index)
Schema:
  - parent_tenant_id: unsignedBigInteger, nullable
  - level: tinyint (0=root, 1=branch, 2=sub-branch)
  - path: varchar (es. "1/5/12" per navigazione rapida)
```

**8.2 Aggiornare Modello Tenant**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Models/Tenant.php
AZIONE: Aggiungere relazioni gerarchiche
  - parent(): BelongsTo (tenant padre)
  - children(): HasMany (tenant figli/succursali)
  - descendants(): tutti i discendenti ricorsivi
  - ancestors(): tutti gli antenati fino al root
  - isRoot(): bool
  - isBranch(): bool
```

**8.3 Modificare TenantScope per Gerarchie**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Traits/TenantScoped.php
AZIONE: Aggiungere opzione per includere/escludere figli
  - withChildren(): include dati dei tenant figli
  - withDescendants(): include tutti i discendenti
  - onlyOwn(): solo dati del tenant corrente (default)
```

**8.4 Esempi d'uso**

```
Struttura:
  Comune di Firenze (tenant_id=5, parent=null, level=0)
  ├── Ufficio Anagrafe (tenant_id=12, parent=5, level=1)
  ├── Ufficio Tributi (tenant_id=13, parent=5, level=1)
  └── Polizia Municipale (tenant_id=14, parent=5, level=1)

Query esempi:
  - Anagrafe vede solo suoi dati: NatanChatMessage::all()
  - Comune vede tutti: NatanChatMessage::withDescendants()->get()
```

---

### **FASE 9: Comunicazioni Infra-Tenant**

> ⚠️ **STATO**: FUTURA IMPLEMENTAZIONE - Non ancora sviluppato

**Obiettivo**: Permettere comunicazioni tra tenant diversi (messaggi, notifiche, broadcast)

**9.1 Migrazione: Creare tabella tenant_communications**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: database/migrations/xxxx_create_tenant_communications_table.php
AZIONE: Creare tabella per messaggi cross-tenant
Schema:
  - id: bigint PK
  - sender_tenant_id: FK tenants
  - receiver_tenant_id: FK tenants (nullable per broadcast)
  - type: enum('direct', 'broadcast', 'notification', 'alert')
  - subject: varchar
  - message: text
  - metadata: JSON (allegati, priorità, categoria)
  - read_at: timestamp nullable
  - expires_at: timestamp nullable
  - created_at, updated_at

Indici:
  - (sender_tenant_id, created_at)
  - (receiver_tenant_id, read_at)
  - (type, created_at)
```

**9.2 Creare Modello TenantCommunication**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Models/TenantCommunication.php
AZIONE: Modello con bypass controllato di TenantScope
  - sender(): BelongsTo Tenant
  - receiver(): BelongsTo Tenant (nullable)
  - scopeForTenant($tenantId): messaggi ricevuti da un tenant
  - scopeUnread(): messaggi non letti
  - scopeBroadcasts(): solo broadcast
  - markAsRead(): segna come letto
```

**9.3 Supporto Broadcast Padre → Figli**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Services/TenantBroadcastService.php
AZIONE: Service per invio broadcast gerarchico
Metodi:
  - broadcastToChildren(int $parentTenantId, string $message, array $options)
  - broadcastToDescendants(int $rootTenantId, string $message, array $options)
  - broadcastToSiblings(int $tenantId, string $message, array $options)
  - notifyParent(int $childTenantId, string $message, array $options)

Esempio uso:
  // Comune invia a tutti gli uffici
  TenantBroadcastService::broadcastToChildren(
      parentTenantId: 5, // Comune Firenze
      message: "Nuova circolare ministeriale disponibile",
      options: ['type' => 'notification', 'priority' => 'high']
  );
```

**9.4 API Endpoints Comunicazioni**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: routes/api.php
AZIONE: Endpoints per gestione comunicazioni
  - GET  /api/tenant/communications        → lista messaggi ricevuti
  - GET  /api/tenant/communications/unread → solo non letti
  - POST /api/tenant/communications/send   → invia messaggio diretto
  - POST /api/tenant/communications/broadcast → broadcast ai figli
  - PATCH /api/tenant/communications/{id}/read → segna come letto
```

**9.5 Controller Comunicazioni**

```
CONTESTO: /home/fabio/NATAN_LOC
PERCORSO: app/Http/Controllers/TenantCommunicationController.php
AZIONE: Controller con autorizzazioni
  - index(): lista messaggi (solo per tenant corrente)
  - send(): invia (verifica permessi cross-tenant)
  - broadcast(): solo tenant padre può fare broadcast
  - markRead(): solo destinatario può segnare letto
```

---

## 📊 PRIORITÀ E SEQUENZA

### **PRIORITÀ ALTA (Oggi)**

1. ✅ Fase 1: Migrazioni database (schema base)
2. ✅ Fase 2.1-2.3: TenantResolver + Trait + ServiceProvider
3. ✅ Fase 4.3: Endpoint /api/session
4. ✅ Fase 5: Rimuovere hardcoded tenantId nel frontend

### **PRIORITÀ MEDIA (Questa settimana)**

5. ✅ Fase 2.4-2.5: Configurare stancl/tenancy
6. ✅ Fase 3: Applicare TenantScoped ai modelli
7. ✅ Fase 4.1-4.2: Aggiornare controllers
8. ✅ Fase 6: Modifiche EGI (minime)

### **PRIORITÀ BASSA (Prossima settimana)**

9. ✅ Fase 4.4: LoginController Sanctum (se serve login dedicato)
10. ✅ Fase 7: Test completi

### **FUTURA IMPLEMENTAZIONE (Da pianificare)**

11. 🔮 Fase 8: Multi-Tenant Gerarchico (succursali/branches)
12. 🔮 Fase 9: Comunicazioni Infra-Tenant (direct + broadcast)

---

## ⚠️ ATTENZIONI CRITICHE

1. **NON modificare EGI oltre quanto necessario** (solo tenant_id in users)
2. **Testare isolamento tenant** prima di passare in produzione
3. **Verificare MongoDB indici** su tenant_id per performance
4. **Backup database** prima di eseguire migrazioni
5. **Frontend senza login**: Per ora, gestire tenant_id da subdomain o default

---

## ✅ CHECKLIST ESECUZIONE

### **EGI (Minimo impatto)**

- [ ] Migrazione: add tenant_id to users
- [ ] User Model: aggiungere tenant_id a fillable
- [ ] RegisterController: impostare tenant_id = 1

### **NATAN_LOC (Multi-tenant completo)**

- [ ] Migrazione: create pa_entities
- [ ] Migrazione: add tenant_id to natan_chat_messages
- [ ] Migrazione: add tenant_id to natan_user_memories
- [ ] Installare stancl/tenancy
- [ ] Creare TenantResolver
- [ ] Creare Trait TenantScoped
- [ ] Creare TenantServiceProvider
- [ ] Configurare config/tenancy.php
- [ ] Aggiornare NatanChatMessage (Trait + fillable)
- [ ] Aggiornare NatanConversationController (usare tenant_id)
- [ ] Creare endpoint /api/session
- [ ] Aggiornare ChatInterface.ts (rimuovere hardcoded)
- [ ] Creare sessionService.ts
- [ ] Aggiornare app.ts e main.ts
- [ ] Test isolamento tenant
- [ ] Verificare MongoDB indici

### **FUTURA IMPLEMENTAZIONE (Fase 8-9)**

- [ ] Migrazione: add parent_tenant_id to tenants
- [ ] Modello Tenant: relazioni parent/children/descendants
- [ ] TenantScope: supporto gerarchico (withChildren, withDescendants)
- [ ] Migrazione: create tenant_communications
- [ ] Modello TenantCommunication
- [ ] TenantBroadcastService (broadcast padre→figli)
- [ ] API endpoints comunicazioni
- [ ] TenantCommunicationController
- [ ] Test comunicazioni cross-tenant
- [ ] Test broadcast gerarchico

---

## 🚀 ORDINE DI ESECUZIONE CONSIGLIATO

1. **Database EGI** (solo tenant_id users)
2. **Database NATAN_LOC** (pa_entities + tenant_id su tabelle)
3. **Backend NATAN_LOC** (Resolver + Trait + ServiceProvider)
4. **Controllers NATAN_LOC** (usare tenant_id)
5. **Frontend NATAN_LOC** (session API + rimuovere hardcoded)
6. **Testing** (isolamento + resolution)

---

**Pronto per esecuzione!**
