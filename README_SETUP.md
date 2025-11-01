# Setup NATAN_LOC Frontend

## Avvio Servizi

1. **Avvia Laravel** (porta 9000):
```bash
cd laravel_backend
php artisan serve --port=9000
```

2. **Avvia Vite Dev Server** (in un altro terminale):
```bash
cd laravel_backend
npm run dev
```

3. **Avvia Python FastAPI** (se necessario, porta alternativa):
```bash
cd python_ai_service
# vedi python_ai_service/README.md
# Se necessario, configurare porta diversa da 9000
```

## Accesso

- **NATAN Chat**: http://localhost:9000/natan/chat
- **Laravel Welcome**: http://localhost:9000/

## Note

Il frontend TypeScript è integrato in Laravel via Vite.
Non usare più il frontend standalone su localhost:5173.
