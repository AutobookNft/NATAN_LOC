# Setup NATAN_LOC Frontend

## Avvio Servizi

1. **Avvia Laravel** (porta 8000):
```bash
cd laravel_backend
php artisan serve
```

2. **Avvia Vite Dev Server** (in un altro terminale):
```bash
cd laravel_backend
npm run dev
```

3. **Avvia Python FastAPI** (se necessario):
```bash
cd python_ai_service
# vedi python_ai_service/README.md
```

## Accesso

- **NATAN Chat**: http://localhost:8000/natan/chat
- **Laravel Welcome**: http://localhost:8000/

## Note

Il frontend TypeScript è integrato in Laravel via Vite.
Non usare più il frontend standalone su localhost:5173.
