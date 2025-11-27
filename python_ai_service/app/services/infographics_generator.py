"""
Infographics Generator - Genera visualizzazioni professionali dai dati RAG

Usa Groq (gratuito) per:
1. Analizzare dati estratti dal RAG
2. Decidere il tipo di grafico più appropriato
3. Generare configurazione Plotly

Output: HTML interattivo, PNG, SVG o JSON per frontend
"""

import json
import logging
import base64
from typing import Dict, Any, List, Optional, Literal
from enum import Enum
from dataclasses import dataclass

logger = logging.getLogger(__name__)

# Import plotly (installare con: pip install plotly kaleido)
try:
    import plotly.graph_objects as go
    import plotly.express as px
    from plotly.subplots import make_subplots
    PLOTLY_AVAILABLE = True
except ImportError:
    PLOTLY_AVAILABLE = False
    logger.warning("Plotly non installato. Installare con: pip install plotly kaleido")


class ChartType(str, Enum):
    """Tipi di grafici supportati"""
    BAR = "bar"
    LINE = "line"
    PIE = "pie"
    SCATTER = "scatter"
    AREA = "area"
    HISTOGRAM = "histogram"
    HEATMAP = "heatmap"
    TREEMAP = "treemap"
    SUNBURST = "sunburst"
    TABLE = "table"
    INDICATOR = "indicator"  # KPI singoli
    TIMELINE = "timeline"


@dataclass
class ChartConfig:
    """Configurazione per generare un grafico"""
    chart_type: ChartType
    title: str
    data: Dict[str, Any]
    x_label: Optional[str] = None
    y_label: Optional[str] = None
    colors: Optional[List[str]] = None
    show_legend: bool = True
    annotations: Optional[List[str]] = None


class InfographicsGenerator:
    """
    Genera infografiche professionali dai dati RAG.
    
    Workflow:
    1. Riceve dati dal RAG (documenti, statistiche, aggregazioni)
    2. Usa Groq per decidere tipo di visualizzazione
    3. Genera grafico con Plotly
    4. Restituisce HTML/PNG/JSON
    """
    
    # Palette colori professionale per PA
    PA_COLORS = [
        "#1e3a5f",  # Blu scuro istituzionale
        "#2e7d32",  # Verde
        "#c62828",  # Rosso
        "#f57c00",  # Arancione
        "#7b1fa2",  # Viola
        "#00838f",  # Teal
        "#455a64",  # Grigio blu
        "#6d4c41",  # Marrone
    ]
    
    # Template per prompt Groq
    CHART_SELECTION_PROMPT = """Analizza i seguenti dati estratti da documenti della Pubblica Amministrazione e suggerisci il tipo di grafico più appropriato.

DATI:
{data_summary}

DOMANDA UTENTE:
{user_question}

TIPI DI GRAFICO DISPONIBILI:
- bar: Per confronti tra categorie
- line: Per trend temporali
- pie: Per proporzioni/percentuali (max 7 categorie)
- scatter: Per correlazioni tra variabili
- area: Per trend cumulativi
- histogram: Per distribuzioni
- heatmap: Per matrici di correlazione
- treemap: Per gerarchie
- table: Per dati tabellari dettagliati
- indicator: Per KPI singoli (numeri importanti)
- timeline: Per eventi cronologici

Rispondi SOLO con un JSON valido nel formato:
{{
    "chart_type": "tipo_scelto",
    "title": "Titolo del grafico",
    "x_label": "Etichetta asse X (se applicabile)",
    "y_label": "Etichetta asse Y (se applicabile)",
    "reasoning": "Breve spiegazione della scelta"
}}"""

    def __init__(self):
        """Inizializza il generatore"""
        if not PLOTLY_AVAILABLE:
            raise ImportError("Plotly richiesto. Installare con: pip install plotly kaleido")
        
        # Non usiamo più Groq per la selezione chart - la logica di fallback è sufficiente
        # perché i dati arrivano già strutturati dalla pipeline
        logger.info("InfographicsGenerator inizializzato (selezione chart basata su struttura dati)")
    
    async def analyze_and_generate(
        self,
        data: Dict[str, Any],
        user_question: str,
        output_format: Literal["html", "png", "svg", "json"] = "html"
    ) -> Dict[str, Any]:
        """
        Analizza dati RAG e genera infografica appropriata.
        
        Args:
            data: Dati estratti dal RAG (documenti, statistiche, aggregazioni)
            user_question: Domanda originale dell'utente
            output_format: Formato output desiderato
            
        Returns:
            Dict con:
            - chart: HTML/base64 PNG/SVG del grafico
            - chart_type: Tipo di grafico generato
            - title: Titolo
            - insights: Insight AI sui dati
        """
        # Seleziona tipo di grafico basandosi sulla struttura dati
        # (più efficiente che chiamare AI - i dati arrivano già strutturati dalla pipeline)
        chart_config = self._fallback_chart_selection(data)
        
        logger.info(f"📊 Chart type selezionato: {chart_config.chart_type.value} - {chart_config.title}")
        
        # Genera il grafico
        fig = self._generate_chart(chart_config, data)
        
        # Converti nel formato richiesto
        output = self._export_chart(fig, output_format)
        
        return {
            "chart": output,
            "chart_type": chart_config.chart_type.value,
            "title": chart_config.title,
            "format": output_format,
            "data_points": len(data.get("values", data.get("items", []))),
        }
    
    def _prepare_data_summary(self, data: Dict[str, Any]) -> str:
        """Prepara un summary dei dati per l'AI"""
        summary_parts = []
        
        # Tipo di dati
        if "categories" in data and "values" in data:
            summary_parts.append(f"Categorie: {data['categories'][:10]}")  # Max 10
            summary_parts.append(f"Valori: {data['values'][:10]}")
            summary_parts.append(f"Numero totale elementi: {len(data['categories'])}")
        
        if "time_series" in data:
            ts = data["time_series"]
            summary_parts.append(f"Serie temporale: {len(ts)} punti")
            if ts:
                summary_parts.append(f"Periodo: {ts[0].get('date', 'N/A')} - {ts[-1].get('date', 'N/A')}")
        
        if "documents" in data:
            docs = data["documents"]
            summary_parts.append(f"Documenti analizzati: {len(docs)}")
            # Estrai categorie se presenti
            categories = {}
            for doc in docs:
                cat = doc.get("category", doc.get("type", "Altro"))
                categories[cat] = categories.get(cat, 0) + 1
            if categories:
                summary_parts.append(f"Per categoria: {categories}")
        
        if "aggregations" in data:
            summary_parts.append(f"Aggregazioni: {data['aggregations']}")
        
        if "kpi" in data:
            summary_parts.append(f"KPI: {data['kpi']}")
        
        return "\n".join(summary_parts) if summary_parts else str(data)[:500]
    
    async def _ai_select_chart_type(
        self,
        data_summary: str,
        user_question: str
    ) -> ChartConfig:
        """Usa Groq per selezionare il tipo di grafico migliore"""
        try:
            prompt = self.CHART_SELECTION_PROMPT.format(
                data_summary=data_summary,
                user_question=user_question
            )
            
            response = await self.groq_adapter.generate(
                messages=[{"role": "user", "content": prompt}],
                temperature=0.3,  # Bassa per consistenza
                max_tokens=500
            )
            
            # Parse risposta JSON
            content = response.get("content", "")
            # Estrai JSON dalla risposta
            json_start = content.find("{")
            json_end = content.rfind("}") + 1
            if json_start >= 0 and json_end > json_start:
                json_str = content[json_start:json_end]
                config_data = json.loads(json_str)
                
                return ChartConfig(
                    chart_type=ChartType(config_data.get("chart_type", "bar")),
                    title=config_data.get("title", "Analisi Dati"),
                    x_label=config_data.get("x_label"),
                    y_label=config_data.get("y_label"),
                    data={}
                )
        except Exception as e:
            logger.warning(f"AI chart selection fallita: {e}, uso fallback")
        
        return self._fallback_chart_selection({})
    
    def _fallback_chart_selection(self, data: Dict[str, Any]) -> ChartConfig:
        """Selezione fallback senza AI"""
        # Logica basata su struttura dati
        
        # Matrice decisionale (table)
        if "headers" in data and "rows" in data:
            return ChartConfig(
                chart_type=ChartType.TABLE,
                title="Matrice Decisionale",
                data=data
            )
        
        # Serie temporale (line chart)
        if "time_series" in data:
            return ChartConfig(
                chart_type=ChartType.LINE,
                title="Trend Temporale",
                data=data
            )
        
        # KPI/Indicatori
        if "kpi" in data:
            return ChartConfig(
                chart_type=ChartType.INDICATOR,
                title="Indicatori Chiave",
                data=data
            )
        
        # Categorie (bar o pie)
        if "categories" in data and "values" in data:
            num_categories = len(data.get("categories", []))
            values = data.get("values", [])
            
            # Se tutti i valori sono percentuali (somma ~100) usa pie
            if num_categories <= 7 and values and 90 <= sum(values) <= 110:
                return ChartConfig(
                    chart_type=ChartType.PIE,
                    title="Distribuzione",
                    data=data
                )
            # Altrimenti bar chart è più leggibile
            return ChartConfig(
                chart_type=ChartType.BAR,
                title="Confronto per Categoria",
                data=data
            )
        
        # Fallback finale: tabella
        return ChartConfig(
            chart_type=ChartType.TABLE,
            title="Riepilogo Dati",
            data=data
        )
    
    def _generate_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Genera il grafico Plotly basato sulla configurazione"""
        
        if config.chart_type == ChartType.BAR:
            return self._create_bar_chart(config, data)
        elif config.chart_type == ChartType.LINE:
            return self._create_line_chart(config, data)
        elif config.chart_type == ChartType.PIE:
            return self._create_pie_chart(config, data)
        elif config.chart_type == ChartType.INDICATOR:
            return self._create_indicator(config, data)
        elif config.chart_type == ChartType.TABLE:
            return self._create_table(config, data)
        elif config.chart_type == ChartType.AREA:
            return self._create_area_chart(config, data)
        elif config.chart_type == ChartType.SCATTER:
            return self._create_scatter_chart(config, data)
        elif config.chart_type == ChartType.TREEMAP:
            return self._create_treemap(config, data)
        else:
            # Default: bar chart
            return self._create_bar_chart(config, data)
    
    def _create_bar_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea grafico a barre"""
        categories = data.get("categories", ["A", "B", "C"])
        values = data.get("values", [10, 20, 30])
        
        fig = go.Figure(data=[
            go.Bar(
                x=categories,
                y=values,
                marker_color=self.PA_COLORS[:len(categories)],
                text=values,
                textposition='auto'
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
            xaxis_title=config.x_label,
            yaxis_title=config.y_label,
            template="plotly_white",
            showlegend=config.show_legend
        )
        
        return fig
    
    def _create_line_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea grafico a linee per serie temporali"""
        time_series = data.get("time_series", [])
        
        if time_series:
            dates = [item.get("date", i) for i, item in enumerate(time_series)]
            values = [item.get("value", 0) for item in time_series]
        else:
            dates = data.get("dates", data.get("categories", []))
            values = data.get("values", [])
        
        fig = go.Figure(data=[
            go.Scatter(
                x=dates,
                y=values,
                mode='lines+markers',
                line=dict(color=self.PA_COLORS[0], width=3),
                marker=dict(size=8),
                fill='tozeroy',
                fillcolor=f'rgba(30, 58, 95, 0.1)'
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
            xaxis_title=config.x_label or "Data",
            yaxis_title=config.y_label or "Valore",
            template="plotly_white",
            hovermode='x unified'
        )
        
        return fig
    
    def _create_pie_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea grafico a torta"""
        categories = data.get("categories", ["A", "B", "C"])
        values = data.get("values", [30, 40, 30])
        
        fig = go.Figure(data=[
            go.Pie(
                labels=categories,
                values=values,
                marker=dict(colors=self.PA_COLORS[:len(categories)]),
                textinfo='label+percent',
                textposition='inside',
                hole=0.3  # Donut chart
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
            template="plotly_white",
            showlegend=True
        )
        
        return fig
    
    def _create_indicator(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea indicatori KPI"""
        kpis = data.get("kpi", data.get("kpis", [{"label": "Totale", "value": 0}]))
        
        if isinstance(kpis, dict):
            kpis = [{"label": k, "value": v} for k, v in kpis.items()]
        
        # Limita a max 4 KPI per visualizzazione
        kpis = kpis[:4]
        num_kpis = len(kpis)
        
        fig = make_subplots(
            rows=1, cols=num_kpis,
            specs=[[{"type": "indicator"}] * num_kpis]
        )
        
        for i, kpi in enumerate(kpis):
            # Costruisci titolo con unità se presente
            label = kpi.get("label", f"KPI {i+1}")
            unit = kpi.get("unit", "")
            title_text = f"{label}" + (f" ({unit})" if unit and unit not in label else "")
            
            # Format del numero in base all'unità
            value = kpi.get("value", 0)
            number_format = {}
            if unit == "€":
                number_format = dict(prefix="€ ", valueformat=",.0f")
            elif unit == "%":
                number_format = dict(suffix="%", valueformat=".1f")
            elif unit == "anni":
                number_format = dict(suffix=" anni", valueformat=".1f")
            else:
                number_format = dict(valueformat=",.0f" if isinstance(value, int) else ",.2f")
            
            fig.add_trace(
                go.Indicator(
                    mode="number+delta" if "delta" in kpi else "number",
                    value=value,
                    title=dict(text=title_text, font=dict(size=14)),
                    delta=dict(reference=kpi.get("reference")) if "reference" in kpi else None,
                    number=dict(
                        font=dict(color=self.PA_COLORS[i % len(self.PA_COLORS)], size=36),
                        **number_format
                    )
                ),
                row=1, col=i+1
            )
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
            template="plotly_white"
        )
        
        return fig
    
    def _create_table(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea tabella dati"""
        # Estrai headers e valori
        if "headers" in data and "rows" in data:
            headers = data["headers"]
            rows = data["rows"]
        elif "documents" in data:
            docs = data["documents"]
            if docs:
                headers = list(docs[0].keys())
                rows = [[doc.get(h, "") for h in headers] for doc in docs]
            else:
                headers = ["Nessun dato"]
                rows = [[]]
        else:
            headers = ["Chiave", "Valore"]
            rows = [[k, str(v)] for k, v in data.items() if k not in ["chart_type"]]
        
        fig = go.Figure(data=[
            go.Table(
                header=dict(
                    values=headers,
                    fill_color=self.PA_COLORS[0],
                    font=dict(color='white', size=12),
                    align='left'
                ),
                cells=dict(
                    values=list(zip(*rows)) if rows else [[]],
                    fill_color='lavender',
                    align='left'
                )
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
        )
        
        return fig
    
    def _create_area_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea grafico ad area"""
        fig = self._create_line_chart(config, data)
        fig.data[0].fill = 'tozeroy'
        return fig
    
    def _create_scatter_chart(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea scatter plot"""
        x_values = data.get("x", data.get("categories", []))
        y_values = data.get("y", data.get("values", []))
        
        fig = go.Figure(data=[
            go.Scatter(
                x=x_values,
                y=y_values,
                mode='markers',
                marker=dict(
                    size=12,
                    color=self.PA_COLORS[0],
                    opacity=0.7
                )
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
            xaxis_title=config.x_label,
            yaxis_title=config.y_label,
            template="plotly_white"
        )
        
        return fig
    
    def _create_treemap(self, config: ChartConfig, data: Dict[str, Any]) -> go.Figure:
        """Crea treemap per dati gerarchici"""
        labels = data.get("labels", data.get("categories", []))
        parents = data.get("parents", [""] * len(labels))
        values = data.get("values", [1] * len(labels))
        
        fig = go.Figure(data=[
            go.Treemap(
                labels=labels,
                parents=parents,
                values=values,
                marker=dict(colors=self.PA_COLORS[:len(labels)])
            )
        ])
        
        fig.update_layout(
            title=dict(text=config.title, font=dict(size=20)),
        )
        
        return fig
    
    def _export_chart(
        self,
        fig: go.Figure,
        output_format: Literal["html", "png", "svg", "json"]
    ) -> str:
        """Esporta il grafico nel formato richiesto"""
        
        if output_format == "html":
            return fig.to_html(include_plotlyjs="cdn", full_html=False)
        
        elif output_format == "json":
            return fig.to_json()
        
        elif output_format in ["png", "svg"]:
            try:
                # Richiede kaleido per export immagini
                img_bytes = fig.to_image(format=output_format, width=1200, height=800)
                return base64.b64encode(img_bytes).decode('utf-8')
            except Exception as e:
                logger.warning(f"Export {output_format} fallito: {e}. Installare kaleido.")
                # Fallback a HTML
                return fig.to_html(include_plotlyjs="cdn", full_html=False)
        
        else:
            return fig.to_html(include_plotlyjs="cdn", full_html=False)
    
    # === METODI DI CONVENIENZA PER DATI RAG ===
    
    async def from_rag_documents(
        self,
        documents: List[Dict[str, Any]],
        user_question: str,
        group_by: str = "category",
        output_format: str = "html"
    ) -> Dict[str, Any]:
        """
        Genera infografica da documenti RAG.
        
        Args:
            documents: Lista documenti dal retriever
            user_question: Domanda utente
            group_by: Campo per raggruppamento (category, type, date, etc.)
            output_format: Formato output
            
        Returns:
            Dict con chart e metadati
        """
        # Raggruppa documenti
        groups = {}
        for doc in documents:
            key = doc.get(group_by, doc.get("document_type", "Altro"))
            groups[key] = groups.get(key, 0) + 1
        
        data = {
            "categories": list(groups.keys()),
            "values": list(groups.values()),
            "documents": documents[:20]  # Sample per context
        }
        
        return await self.analyze_and_generate(data, user_question, output_format)
    
    async def from_rag_statistics(
        self,
        stats: Dict[str, Any],
        user_question: str,
        output_format: str = "html"
    ) -> Dict[str, Any]:
        """
        Genera infografica da statistiche RAG aggregate.
        
        Args:
            stats: Statistiche aggregate (count per categoria, medie, etc.)
            user_question: Domanda utente
            output_format: Formato output
        """
        # Converti stats in formato standard
        if "per_category" in stats:
            data = {
                "categories": list(stats["per_category"].keys()),
                "values": list(stats["per_category"].values())
            }
        elif "time_series" in stats:
            data = {"time_series": stats["time_series"]}
        elif "kpis" in stats or "totals" in stats:
            data = {"kpi": stats.get("kpis", stats.get("totals", {}))}
        else:
            data = stats
        
        return await self.analyze_and_generate(data, user_question, output_format)


# === FUNZIONE DI UTILITÀ ===

async def generate_infographic(
    data: Dict[str, Any],
    question: str,
    output_format: str = "html"
) -> Dict[str, Any]:
    """
    Funzione helper per generare infografiche.
    
    Usage:
        from app.services.infographics_generator import generate_infographic
        
        result = await generate_infographic(
            data={"categories": ["A", "B"], "values": [10, 20]},
            question="Mostra distribuzione",
            output_format="html"
        )
    """
    generator = InfographicsGenerator()
    return await generator.analyze_and_generate(data, question, output_format)
