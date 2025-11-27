# FlorenceEGI - Production Deploy Checklist

## 📋 **DEPLOY COMPLETO - STAGING → PRODUCTION**

**Versione:** 2.0 - Post Staging Experience  
**Data:** 30 Luglio 2025  
**Ambiente:** Laravel Forge + AWS  

---

## 🎯 **FASE 1: SETUP SERVER & FORGE**

### **1.1 - Provisioning Server AWS**
- [ ] **Crea server AWS** via Forge Dashboard
- [ ] **Tipo:** App Server (non Load Balancer)
- [ ] **Regione:** Scegli più vicina agli utenti
- [ ] **Size:** t3.small minimum (staging), t3.medium+ (production)
- [ ] **PHP Version:** 8.3
- [ ] **Database:** MySQL 8.0+
- [ ] **Redis:** Abilitato (per cache e queue)

### **1.2 - Setup SSH Keys**
- [ ] **Genera SSH key pair** locale se non esiste
- [ ] **Aggiungi SSH public key** a Forge (Account → SSH Keys)
- [ ] **Verifica accesso SSH:** `ssh forge@[IP-SERVER]`

### **1.3 - Setup Repository**
- [ ] **Collega repository GitHub** in Forge
- [ ] **Branch:** main (o production)
- [ ] **Auto-deployment:** ON per staging, considera MANUAL per production
- [ ] **Test prima connessione:** Deploy Now

---

## 🔧 **FASE 2: CONFIGURAZIONE AMBIENTE**

### **2.1 - Environment Variables (.env)**
```bash
# Application
APP_NAME="FlorenceEGI"
APP_ENV=production  # O staging
APP_KEY=base64:... # php artisan key:generate
APP_DEBUG=false    # SEMPRE false in production
APP_URL=https://[DOMINIO-PRODUCTION]

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=[DB-NAME]
DB_USERNAME=[DB-USER]
DB_PASSWORD=[STRONG-PASSWORD]

# Session & Cache
SESSION_DRIVER=database
SESSION_DOMAIN=[DOMINIO-SENZA-HTTP]  # CRITICO!
CACHE_DRIVER=redis
QUEUE_CONNECTION=database

# Storage
FILESYSTEM_DISK=public

# Algorand Wallet Addresses (FAKE per testing)
NATAN_WALLET_ADDRESS='XKQM7FWJVN4LBZR2X6YDPU3TS9WE5QAZ7VBNM8R4X2LKJHG6FD3SWCVBN2Q'
EPP_WALLET_ADDRESS='MKPN8RWQL5X3VZTY9MBHJ6KUSG7PX4ZN2VBCM5ER7WQPL3YHGF9TSXVBNRA'
FRANGETTE_WALLET_ADDRESS='QRTV3BZNWH6YJ5XPGU4FLSM7KC2EN9VBWQ6ZY3MNXT4PRS7GH5KWJYBLNFC'

# Mail
MAIL_MAILER=smtp
MAIL_HOST=[SMTP-HOST]
MAIL_PORT=587
MAIL_USERNAME=[SMTP-USER]
MAIL_PASSWORD=[SMTP-PASSWORD]

# Ultra Error Manager
ERROR_EMAIL_NOTIFICATIONS_ENABLED=true
ERROR_EMAIL_RECIPIENT=[ADMIN-EMAIL]
ERROR_SLACK_NOTIFICATIONS_ENABLED=false  # Configura se usi Slack
```

### **2.2 - Configurazione Database**
- [ ] **Crea database** via Forge (Database tab)
- [ ] **User e password** strong per production
- [ ] **Verifica connessione** da server

---

## 📦 **FASE 3: ULTRA PACKAGES SETUP**

### **3.1 - Pubblicazione Config Ultra**
```bash
# SSH nel server
ssh forge@[IP-SERVER]

# Pubblica TUTTE le configurazioni Ultra
php artisan vendor:publish --tag=error-manager-config --force
php artisan vendor:publish --tag=utm-config --force
php artisan vendor:publish --tag=uum-config --force
php artisan vendor:publish --tag=ultra-log-config --force

# Pubblica migrazioni Ultra
php artisan vendor:publish --tag=error-manager-migrations --force
```

### **3.2 - CRITICO: Sincronizzazione Config**
⚠️ **PROBLEMA IDENTIFICATO:** vendor:publish sovrascrive i config del repo!

**SOLUZIONE:**
```bash
# DOPO vendor:publish, forza ripristino dal repo
git checkout origin/main -- config/error-manager.php
git checkout origin/main -- config/ultra_log_manager.php
git checkout origin/main -- config/translation-manager.php

# Verifica che i config siano aggiornati
grep "RECORD_EGI_NOT_FOUND_IN_RESERVATION_CONTROLLER" config/error-manager.php
```

### **3.3 - Migrazioni Database**
```bash
# Esegui TUTTE le migrazioni
php artisan migrate --force

# Verifica tabelle Ultra esistano
php artisan migrate:status | grep -E "(ultra|error|log)"
```

---

## ⚙️ **FASE 4: QUEUE WORKERS & DAEMON**

### **4.1 - Setup Queue Worker**
**Forge Dashboard** → **Server** → **Daemons** → **New Daemon**

**Configurazione Daemon:**
```bash
Command: php /home/forge/default/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
Directory: /home/forge/default
User: forge
Processes: 1  # Aumenta per high traffic
Auto-restart: ✅ CHECKED
```

### **4.2 - Test Queue Worker**
```bash
# Verifica worker attivo
php artisan queue:work database --once

# Monitor code in tempo reale
php artisan queue:monitor database
```

---

## 🔐 **FASE 5: SECURITY & PERFORMANCE**

### **5.1 - SSL Certificate**
- [ ] **Forge Dashboard** → **Sites** → **SSL** → **LetsEncrypt**
- [ ] **Force HTTPS:** ON
- [ ] **Verifica certificato** via browser

### **5.2 - Performance Optimization**
```bash
# Cache applicazione
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ottimizzazione Composer
composer install --optimize-autoloader --no-dev  # Solo production
```

### **5.3 - File Permissions**
```bash
# Assicura permissions corrette
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R forge:www-data storage/
chown -R forge:www-data bootstrap/cache/
```

---

## 🧪 **FASE 6: TESTING & VERIFICA**

### **6.1 - Test Funzionalità Core**
- [ ] **Registrazione utenti** (test completo ecosystem)
- [ ] **Login/Logout**
- [ ] **Upload EGI** (test queue processing)
- [ ] **Prenotazioni** (test API)
- [ ] **Like system** (test AJAX)

### **6.2 - Test Ultra Systems**
- [ ] **Error Manager:** Genera errore test, verifica log e email
- [ ] **Queue Processing:** Upload immagine, verifica thumbnails
- [ ] **Log Manager:** Verifica log scritti correttamente
- [ ] **Translation Manager:** Test cambio lingua

### **6.3 - Verifica Configurazioni**
```bash
# Verifica config Ultra caricati
php artisan tinker
config('error-manager.errors.RECORD_EGI_NOT_FOUND_IN_RESERVATION_CONTROLLER')
# Deve restituire array, NON null

# Verifica tabelle database
php artisan migrate:status

# Verifica queue worker
ps aux | grep "queue:work"
```

---

## 🚨 **FASE 7: TROUBLESHOOTING COMUNI**

### **7.1 - Config Non Aggiornati**
**Problema:** UEM non trova error codes definiti
**Soluzione:**
```bash
# Forza ripristino config dal repo
rm config/error-manager.php
git checkout origin/main -- config/error-manager.php
php artisan config:cache
```

### **7.2 - Errore 419 CSRF**
**Problema:** Session domain non corretto
**Soluzione:**
```bash
# Nel .env
SESSION_DOMAIN=[DOMINIO-SENZA-HTTP-E-PORTA]
# Es: SESSION_DOMAIN=florenceegi.com (NON https://florenceegi.com)
```

### **7.3 - PHP 8.3 Deprecation**
**Problema:** Parametri opzionali prima di required
**Soluzione:** Verificare file Service classes:
```php
// SBAGLIATO
public function __construct(?Logger $logger = null, RequiredService $service)

// CORRETTO  
public function __construct(RequiredService $service, ?Logger $logger = null)
```

### **7.4 - Component Blade Missing**
**Problema:** `Unable to locate component [icon]`
**Soluzione:** Usare IconRepository pattern come implementato

### **7.5 - Queue Worker Non Parte**
**Problema:** Job non processati
**Soluzione:**
```bash
# Restart daemon da Forge Dashboard
# O manualmente:
sudo supervisorctl restart forge-daemon-[ID]
```

---

## 📋 **FASE 8: POST-DEPLOY CHECKLIST**

### **8.1 - Monitoring Setup**
- [ ] **Forge Monitoring:** Attivato per CPU, Memory, Disk
- [ ] **Log Rotation:** Configurato per evitare dischi pieni
- [ ] **Backup Database:** Schedulato automatico
- [ ] **SSL Renewal:** Auto-renewal attivo

### **8.2 - Documentation**
- [ ] **Credenziali produzione** salvate in vault sicuro
- [ ] **URL admin/dashboard** documentati
- [ ] **Procedure backup/restore** testate
- [ ] **Contatti supporto** aggiornati

### **8.3 - Team Handover**
- [ ] **Accesso Forge** condiviso con team
- [ ] **Repository** accesso configurato
- [ ] **Credenziali database** condivise sicuramente
- [ ] **Procedure emergenza** documentate

---

## 🎯 **COMANDI RAPID-FIRE POST-DEPLOY**

**Sequenza rapida dopo primo deploy:**
```bash
# 1. SSH nel server
ssh forge@[IP-SERVER]

# 2. Setup Ultra
php artisan vendor:publish --tag=error-manager-config --force
php artisan vendor:publish --tag=error-manager-migrations --force
git checkout origin/main -- config/error-manager.php

# 3. Database
php artisan migrate --force

# 4. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Verifica
php artisan tinker
config('error-manager.errors.UNDEFINED_ERROR_CODE')
exit

# 6. Test registrazione utente
```

---

## ⚡ **TIPS PRODUCTION-READY**

### **Performance:**
- **Redis cache** per session e cache
- **Queue worker** sempre attivo  
- **Opcache** attivato in PHP
- **Static assets** su CDN (futuro)

### **Security:**
- **APP_DEBUG=false** SEMPRE
- **Strong passwords** database
- **SSL certificate** attivo
- **Firewall** configurato

### **Monitoring:**
- **Error logs** monitorati quotidianamente
- **Queue jobs** verificati regolarmente  
- **Disk space** monitorato
- **SSL expiry** notificato

---

## 🚨 **EMERGENCY CONTACTS**

- **Forge Support:** [https://forge.laravel.com/support]
- **AWS Support:** [Account specifico]
- **Domain Provider:** [Provider specifico]
- **SMTP Provider:** [Provider specifico]

---

**🎯 NOTA IMPORTANTE:** Questo documento è basato sull'esperienza staging del 30 Luglio 2025. Aggiornare con nuove scoperte durante deploy production.

**✅ SUCCESS CRITERIA:** Deploy completato quando tutti i test Fase 6 passano senza errori!