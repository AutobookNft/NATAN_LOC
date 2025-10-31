# NATAN Frontend

Frontend TypeScript puro per NATAN_LOC - Assistente AI per la Pubblica Amministrazione.

## Stack

- **TypeScript** - Type safety e sviluppo moderno
- **Vite** - Build tool veloce e moderno
- **Tailwind CSS** - Utility-first CSS framework
- **Vanilla JS/TS** - Nessun framework (React/Vue/Angular) per controllo totale

## Setup

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

## Struttura

```
frontend/
├── src/
│   ├── components/       # Componenti modulari
│   │   ├── ChatInterface.ts
│   │   ├── Message.ts
│   │   ├── ClaimRenderer.ts
│   │   └── UrsBadge.ts
│   ├── services/         # Servizi API
│   │   └── api.ts
│   ├── types/           # TypeScript types
│   │   └── index.ts
│   ├── app.ts           # Main App component
│   ├── main.ts          # Entry point
│   └── styles.css       # Tailwind + custom styles
├── public/              # Static assets
├── index.html           # HTML entry
└── package.json
```

## Componenti

### ChatInterface
Gestisce l'interfaccia chat principale:
- Input utente
- Invio messaggi
- Visualizzazione risposte con claims USE
- Gestione stato loading

### Message
Rendering messaggi user/assistant con:
- Claims verificati
- Blocked claims
- Source links
- URS metadata

### ClaimRenderer
Rendering claims USE con:
- URS badges colorati (A/B/C/X)
- Source links clickable
- URS breakdown (collapsible)
- Inference badges

### UrsBadge
Badge Ultra Reliability Score con:
- Colori semantici (A=verde, B=blu, C=giallo, X=rosso)
- Score numerico
- ARIA labels per accessibilità

## API Integration

Il frontend comunica con:
1. **Python FastAPI** (`/api/v1/use/query`) - USE Pipeline
2. **Laravel Backend** - Autenticazione, tenant management

Vedi `src/services/api.ts` per i dettagli.

## Accessibilità

- WCAG 2.1 AA compliant
- ARIA labels su tutti i componenti interattivi
- Keyboard navigation (Shift+Enter per nuova riga)
- Screen reader support
- Focus visible states

## Build

Il build output viene generato in `../laravel_backend/public/dist` per l'integrazione con Laravel Blade.

## Environment Variables

Crea un file `.env` nella root del frontend:

```
VITE_API_BASE_URL=http://localhost:8000
```






