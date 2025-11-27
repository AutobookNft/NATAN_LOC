# ✅ Debug FlorenceEGI - PHP 8.4 + Xdebug + VSCode

Questa guida ti permette di ripristinare **rapidamente** un ambiente di debug funzionante per Laravel (CLI), con PHP 8.4 e Xdebug.

---

## 🔧 1. Verifica PHP CLI

```bash
php -v
```

Deve mostrare:

```
with Xdebug v3.x.x
```

---

## 📦 2. Installa i moduli PHP CLI richiesti

```bash
sudo apt install php8.4-xdebug php8.4-mysql php8.4-curl php8.4-bcmath php8.4-zip
```

---

## ⚙️ 3. Configura Xdebug per CLI

Apri:

```bash
sudo nano /etc/php/8.4/cli/conf.d/20-xdebug.ini
```

Inserisci:

```ini
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.discover_client_host=true
```

Salva (`Ctrl+O`, `Invio`, `Ctrl+X`)

---

## 🧪 4. Riavvia il server Laravel

```bash
php artisan serve --port=8004
```

Apri [http://localhost:8004](http://localhost:8004)

---

## 🧠 5. Configura VSCode (`.vscode/launch.json`)

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "type": "chrome",
      "request": "launch",
      "name": "Debug Laravel Frontend (via Vite)",
      "url": "http://localhost:8004",
      "webRoot": "${workspaceFolder}/resources",
      "sourceMaps": true,
      "trace": true,
      "skipFiles": ["<node_internals>/**"]
    },
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "log": true,
      "pathMappings": {
        "/home/fabio/EGI": "${workspaceFolder}"
      },
      "xdebugSettings": {
        "max_children": 256,
        "max_data": 1024,
        "max_depth": 5
      }
    }
  ]
}
```

---

## 🧷 6. Procedura di debug

1. Premi `F5` su **"Listen for Xdebug"**
2. Metti breakpoint in un file PHP (es. `web.php`)
3. Visita una pagina da browser
4. VSCode si ferma al breakpoint

---

## ✅ Comandi di verifica utili

```bash
php -m | grep xdebug
php -i | grep xdebug
php --ini
```

---

## 🧱 Backup consigliato

Esegui backup di:

- `.vscode/launch.json`
- `/etc/php/8.4/cli/conf.d/20-xdebug.ini`
- File `.code-workspace` se usato