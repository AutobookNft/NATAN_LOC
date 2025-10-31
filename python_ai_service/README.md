# NATAN_LOC - Python AI Gateway Service

FastAPI microservice per operazioni AI (embeddings, chat, RAG).

## Setup

```bash
# Create virtual environment
python3.12 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Copy .env.example to .env and configure
cp .env.example .env
# Edit .env with your configuration

# Run development server
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

## Endpoints

- `GET /healthz` - Health check
- `POST /api/v1/embed` - Generate embeddings
- `POST /api/v1/chat` - LLM inference
- `POST /api/v1/rag/search` - Vector similarity search

## Structure

```
app/
├── main.py              # FastAPI app
├── routers/             # API routes
│   ├── health.py
│   ├── embeddings.py
│   ├── chat.py
│   └── rag.py
├── services/            # Business logic (USE pipeline)
├── models/              # Pydantic models
└── config/              # Configuration
```







