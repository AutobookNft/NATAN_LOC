"""
Infographics Router - API per generazione infografiche da dati RAG

Endpoints:
- POST /infographics/generate - Genera infografica da dati custom
- POST /infographics/from-query - Genera infografica da query RAG
- POST /infographics/from-documents - Genera infografica da documenti
"""

from fastapi import APIRouter, HTTPException
from fastapi.responses import HTMLResponse, JSONResponse
from pydantic import BaseModel, Field
from typing import List, Dict, Any, Optional, Literal
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


# === MODELLI REQUEST/RESPONSE ===

class InfographicRequest(BaseModel):
    """Richiesta generazione infografica da dati custom"""
    data: Dict[str, Any] = Field(
        ...,
        description="Dati per il grafico. Formati supportati: {categories, values}, {time_series}, {kpi}, {documents}"
    )
    question: str = Field(
        ...,
        description="Domanda/descrizione per guidare la scelta del grafico"
    )
    output_format: Literal["html", "png", "svg", "json"] = Field(
        default="html",
        description="Formato output: html (interattivo), png/svg (immagine base64), json (plotly)"
    )
    chart_type: Optional[str] = Field(
        default=None,
        description="Forza tipo grafico: bar, line, pie, scatter, area, indicator, table, treemap"
    )


class InfographicFromQueryRequest(BaseModel):
    """Richiesta infografica da query RAG"""
    question: str = Field(..., description="Domanda in linguaggio naturale")
    tenant_id: int = Field(..., description="ID tenant per isolamento dati")
    output_format: Literal["html", "png", "svg", "json"] = "html"
    group_by: str = Field(
        default="document_type",
        description="Campo per raggruppamento: document_type, category, date, etc."
    )


class InfographicFromDocumentsRequest(BaseModel):
    """Richiesta infografica da lista documenti"""
    documents: List[Dict[str, Any]] = Field(..., description="Lista documenti RAG")
    question: str = Field(..., description="Descrizione per il grafico")
    output_format: Literal["html", "png", "svg", "json"] = "html"
    group_by: str = "document_type"


class InfographicResponse(BaseModel):
    """Risposta con infografica generata"""
    success: bool
    chart: str = Field(..., description="HTML, base64 image, o JSON del grafico")
    chart_type: str
    title: str
    format: str
    data_points: int
    error: Optional[str] = None


# === ENDPOINTS ===

@router.post("/infographics/generate", response_model=InfographicResponse)
async def generate_infographic(request: InfographicRequest):
    """
    Genera infografica da dati custom.
    
    Esempi di data:
    
    1. Bar/Pie chart:
    ```json
    {
        "data": {
            "categories": ["Delibere", "Determine", "Ordinanze"],
            "values": [45, 32, 18]
        },
        "question": "Distribuzione atti per tipologia",
        "output_format": "html"
    }
    ```
    
    2. Line chart (time series):
    ```json
    {
        "data": {
            "time_series": [
                {"date": "2024-01", "value": 10},
                {"date": "2024-02", "value": 15},
                {"date": "2024-03", "value": 23}
            ]
        },
        "question": "Trend mensile delibere",
        "output_format": "html"
    }
    ```
    
    3. KPI Indicators:
    ```json
    {
        "data": {
            "kpi": {
                "Totale Atti": 156,
                "Questo Mese": 23,
                "Media Mensile": 13
            }
        },
        "question": "Indicatori principali",
        "output_format": "html"
    }
    ```
    """
    try:
        from app.services.infographics_generator import InfographicsGenerator
        
        generator = InfographicsGenerator()
        result = await generator.analyze_and_generate(
            data=request.data,
            user_question=request.question,
            output_format=request.output_format
        )
        
        return InfographicResponse(
            success=True,
            chart=result["chart"],
            chart_type=result["chart_type"],
            title=result["title"],
            format=result["format"],
            data_points=result["data_points"]
        )
        
    except ImportError as e:
        logger.error(f"Dipendenza mancante per infografiche: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Dipendenza mancante: {e}. Installare con: pip install plotly kaleido"
        )
    except Exception as e:
        logger.error(f"Errore generazione infografica: {e}")
        return InfographicResponse(
            success=False,
            chart="",
            chart_type="error",
            title="Errore",
            format=request.output_format,
            data_points=0,
            error=str(e)
        )


@router.post("/infographics/from-query", response_model=InfographicResponse)
async def generate_from_query(request: InfographicFromQueryRequest):
    """
    Genera infografica eseguendo prima una query RAG.
    
    Workflow:
    1. Esegue retrieval con la domanda
    2. Aggrega i documenti trovati
    3. Genera infografica appropriata
    
    Esempio:
    ```json
    {
        "question": "Quante delibere sono state pubblicate per categoria?",
        "tenant_id": 1,
        "output_format": "html",
        "group_by": "category"
    }
    ```
    """
    try:
        from app.services.infographics_generator import InfographicsGenerator
        from app.services.mongodb_service import MongoDBService
        
        # 1. Recupera documenti dal RAG
        if not MongoDBService.is_connected():
            raise HTTPException(status_code=503, detail="MongoDB non connesso")
        
        # Query documenti per tenant
        query_filter = {"tenant_id": request.tenant_id}
        documents = MongoDBService.find_documents("documents", query_filter, limit=1000)
        
        if not documents:
            return InfographicResponse(
                success=True,
                chart="<div>Nessun documento trovato per questo tenant</div>",
                chart_type="empty",
                title="Nessun Dato",
                format=request.output_format,
                data_points=0
            )
        
        # 2. Genera infografica
        generator = InfographicsGenerator()
        result = await generator.from_rag_documents(
            documents=documents,
            user_question=request.question,
            group_by=request.group_by,
            output_format=request.output_format
        )
        
        return InfographicResponse(
            success=True,
            chart=result["chart"],
            chart_type=result["chart_type"],
            title=result["title"],
            format=result["format"],
            data_points=result["data_points"]
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Errore generazione da query: {e}")
        return InfographicResponse(
            success=False,
            chart="",
            chart_type="error",
            title="Errore",
            format=request.output_format,
            data_points=0,
            error=str(e)
        )


@router.post("/infographics/from-documents", response_model=InfographicResponse)
async def generate_from_documents(request: InfographicFromDocumentsRequest):
    """
    Genera infografica da lista di documenti già recuperati.
    
    Utile quando hai già i documenti dal RAG e vuoi visualizzarli.
    
    Esempio:
    ```json
    {
        "documents": [
            {"title": "Delibera 1", "category": "Urbanistica", "date": "2024-01-15"},
            {"title": "Delibera 2", "category": "Bilancio", "date": "2024-01-20"},
            {"title": "Delibera 3", "category": "Urbanistica", "date": "2024-02-01"}
        ],
        "question": "Distribuzione delibere per categoria",
        "output_format": "html",
        "group_by": "category"
    }
    ```
    """
    try:
        from app.services.infographics_generator import InfographicsGenerator
        
        if not request.documents:
            return InfographicResponse(
                success=True,
                chart="<div>Nessun documento fornito</div>",
                chart_type="empty",
                title="Nessun Dato",
                format=request.output_format,
                data_points=0
            )
        
        generator = InfographicsGenerator()
        result = await generator.from_rag_documents(
            documents=request.documents,
            user_question=request.question,
            group_by=request.group_by,
            output_format=request.output_format
        )
        
        return InfographicResponse(
            success=True,
            chart=result["chart"],
            chart_type=result["chart_type"],
            title=result["title"],
            format=result["format"],
            data_points=result["data_points"]
        )
        
    except Exception as e:
        logger.error(f"Errore generazione da documenti: {e}")
        return InfographicResponse(
            success=False,
            chart="",
            chart_type="error",
            title="Errore",
            format=request.output_format,
            data_points=0,
            error=str(e)
        )


@router.get("/infographics/chart-types")
async def get_chart_types():
    """
    Restituisce i tipi di grafici disponibili con descrizione.
    """
    return {
        "chart_types": [
            {"type": "bar", "name": "Grafico a Barre", "use_case": "Confronto tra categorie"},
            {"type": "line", "name": "Grafico a Linee", "use_case": "Trend temporali"},
            {"type": "pie", "name": "Grafico a Torta", "use_case": "Proporzioni (max 7 categorie)"},
            {"type": "area", "name": "Grafico ad Area", "use_case": "Trend cumulativi"},
            {"type": "scatter", "name": "Scatter Plot", "use_case": "Correlazioni"},
            {"type": "indicator", "name": "KPI Indicator", "use_case": "Numeri singoli importanti"},
            {"type": "table", "name": "Tabella", "use_case": "Dati dettagliati"},
            {"type": "treemap", "name": "Treemap", "use_case": "Dati gerarchici"},
            {"type": "heatmap", "name": "Heatmap", "use_case": "Matrici di valori"},
        ],
        "output_formats": ["html", "png", "svg", "json"],
        "note": "Il sistema AI seleziona automaticamente il tipo migliore se non specificato"
    }


@router.get("/infographics/demo", response_class=HTMLResponse)
async def demo_infographic():
    """
    Genera un'infografica demo per test.
    """
    try:
        from app.services.infographics_generator import InfographicsGenerator
        
        generator = InfographicsGenerator()
        
        # Dati demo
        demo_data = {
            "categories": ["Delibere", "Determine", "Ordinanze", "Decreti", "Regolamenti"],
            "values": [45, 32, 18, 12, 8]
        }
        
        result = await generator.analyze_and_generate(
            data=demo_data,
            user_question="Distribuzione atti amministrativi per tipologia",
            output_format="html"
        )
        
        html = f"""
        <!DOCTYPE html>
        <html>
        <head>
            <title>Demo Infografica NATAN</title>
            <style>
                body {{ font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }}
                .container {{ max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }}
                h1 {{ color: #1e3a5f; }}
                .meta {{ color: #666; margin-bottom: 20px; }}
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🎨 Demo Infografica NATAN_LOC</h1>
                <div class="meta">
                    <p><strong>Tipo grafico:</strong> {result['chart_type']}</p>
                    <p><strong>Punti dati:</strong> {result['data_points']}</p>
                </div>
                {result['chart']}
            </div>
        </body>
        </html>
        """
        
        return HTMLResponse(content=html)
        
    except Exception as e:
        return HTMLResponse(
            content=f"<html><body><h1>Errore</h1><p>{str(e)}</p><p>Installare: pip install plotly kaleido</p></body></html>",
            status_code=500
        )
