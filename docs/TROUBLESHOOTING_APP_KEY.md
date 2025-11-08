# 🔑 Troubleshooting: APP_KEY Missing

## 📋 Problema

Errore `Illuminate\Encryption\MissingAppKeyException`: "No application encryption key has been specified."

## 🔍 Cause Principali

### 1. **File `.env` non esiste o APP_KEY vuota**
- Il file `.env` viene copiato da `.env.example` che ha `APP_KEY=` vuoto
- Il file `.env` non è stato committato (corretto, è nel `.gitignore`)
- Quando si clona il repository, il `.env` non esiste

### 2. **Server Laravel avviato prima di generare la key**
- Il processo `php artisan serve` viene avviato quando `APP_KEY` è ancora vuota
- PHP carica il `.env` all'avvio e lo mantiene in memoria
- Anche se rigeneri la key dopo, il processo già avviato continua a usare la vecchia configurazione

### 3. **Cache configurazione Laravel**
- Laravel può cacheare la configurazione con `config:cache`
- Se la cache è stata creata quando `APP_KEY` era vuota, continua a usare quella

## ✅ Soluzione Immediata

```bash
cd laravel_backend

# 1. Genera la APP_KEY (se mancante)
php artisan key:generate --force

# 2. Pulisci tutte le cache
php artisan optimize:clear

# 3. Riavvia il server Laravel
# Ferma il processo corrente (Ctrl+C o kill PID)
# Poi riavvia:
php artisan serve --host=0.0.0.0 --port=7000
```

## 🛡️ Prevenzione Automatica

Lo script `start_services.sh` è stato aggiornato per:
- ✅ Verificare se `.env` esiste, altrimenti crearlo da `.env.example`
- ✅ Controllare se `APP_KEY` è vuota o mancante
- ✅ Generare automaticamente la key se manca
- ✅ Bloccare l'avvio del server se la key non può essere generata

**Non serve più intervento manuale!**

## 📝 Setup Iniziale (Prima Installazione)

```bash
cd laravel_backend

# 1. Copia .env.example in .env (se non esiste)
cp .env.example .env

# 2. Genera APP_KEY
php artisan key:generate

# 3. Configura database e altre variabili in .env
# (DB_DATABASE, DB_USERNAME, etc.)

# 4. Avvia i servizi
cd ..
./start_services.sh
```

## 🔄 Setup Automatico con Composer

Se usi `composer install` per la prima volta:

```bash
cd laravel_backend
composer install
# Il post-install script crea automaticamente .env e genera la key
```

Oppure usa lo script `setup` personalizzato:

```bash
cd laravel_backend
composer run setup
```

## ⚠️ Note Importanti

1. **`.env` non è in Git**: È corretto! Il file `.env` contiene informazioni sensibili e non deve essere committato
2. **`.env.example` è il template**: Contiene le variabili ma con valori vuoti/default
3. **Ogni ambiente ha il suo `.env`**: Development, staging, production hanno `.env` separati
4. **APP_KEY deve essere unica**: Ogni installazione deve avere una key diversa per sicurezza

## 🐛 Debug

Se il problema persiste:

```bash
cd laravel_backend

# 1. Verifica che .env esista
ls -la .env

# 2. Verifica che APP_KEY sia presente
grep "^APP_KEY=" .env

# 3. Verifica che Laravel la legga
php artisan tinker --execute="echo config('app.key');"

# 4. Se è vuota, rigenera
php artisan key:generate --force

# 5. Pulisci cache
php artisan optimize:clear

# 6. Verifica di nuovo
php artisan tinker --execute="echo config('app.key');"
```

## 📚 Riferimenti

- [Laravel Documentation: Configuration](https://laravel.com/docs/configuration)
- [Laravel Documentation: Encryption](https://laravel.com/docs/encryption)

---

**Versione**: 1.0.0  
**Data**: 2025-11-05  
**Autore**: Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici



