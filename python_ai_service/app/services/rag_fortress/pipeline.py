"""
Pipeline orchestrator RAG-Fortress
Coordina tutti i componenti per generare risposta zero-allucinazione
"""

import logging
from typing import Dict, Optional, List
from .retriever import HybridRetriever
from .evidence_verifier import EvidenceVerifier
from .claim_extractor import ClaimExtractor
from .gap_detector import GapDetector
from .constrained_synthesizer import ConstrainedSynthesizer
from .hostile_factchecker import HostileFactChecker
from .urs_calculator import URSCalculator
from app.services.ai_router import AIRouter
from app.services.providers.api_errors import APIError, APIErrorType

logger = logging.getLogger(__name__)

class RAGFortressPipeline:
    """
    Pipeline completa RAG-Fortress Zero-Hallucination
    """
    
    def __init__(self):
        self.retriever = HybridRetriever()
        self.evidence_verifier = EvidenceVerifier()
        self.claim_extractor = ClaimExtractor()
        self.gap_detector = GapDetector()
        self.synthesizer = ConstrainedSynthesizer()
        self.fact_checker = HostileFactChecker()
        self.urs_calculator = URSCalculator()
        self.ai_router = AIRouter()
    
    def _build_document_citation(self, ev: Dict, index: int) -> str:
        """
        Costruisce citazione precisa per un documento PA.
        
        Da: [DOC 1]
        A:  [Prot. 18822/2025 - Manutenzione impianti sollevamento]
        
        Args:
            ev: Evidenza documento con metadata
            index: Indice progressivo per fallback
            
        Returns:
            Stringa citazione formattata
        """
        protocol = ev.get("protocol_number", "")
        title = ev.get("title", "")
        date = ev.get("protocol_date", "")
        
        # Costruisci citazione più precisa possibile
        if protocol:
            # Formato: [Prot. 18822/2025 - Titolo breve]
            short_title = title[:60] + "..." if len(title) > 60 else title
            if short_title:
                return f"[Prot. {protocol} - {short_title}]"
            return f"[Prot. {protocol}]"
        elif title:
            # Solo titolo se no protocollo
            short_title = title[:80] + "..." if len(title) > 80 else title
            return f"[{short_title}]"
        else:
            # Fallback numerico
            return f"[DOC {index}]"
    
    # Categorie note del patrimonio comunale (per rilevare gap copertura)
    KNOWN_ASSET_CATEGORIES = [
        "Viabilità e Ponti",
        "Edilizia Scolastica", 
        "Impianti Sportivi",
        "Verde Pubblico",
        "Illuminazione Pubblica",
        "Cimiteri",
        "Edifici Comunali",
        "Mercati",
        "Patrimonio Culturale"
    ]
    
    def _build_methodology_header(
        self, 
        num_docs_analyzed: int, 
        total_docs_available: int,
        date_range: str = None,
        doc_types: List[str] = None,
        asset_categories_found: List[str] = None
    ) -> str:
        """
        Costruisce header metodologico obbligatorio per report PA.
        Include esplicitamente cosa È e cosa NON È coperto.
        
        Args:
            num_docs_analyzed: Numero documenti effettivamente analizzati
            total_docs_available: Totale documenti disponibili nel DB
            date_range: Range temporale (es: "Gen-Nov 2025")
            doc_types: Lista tipologie documenti trovate
            asset_categories_found: Categorie patrimonio coperte dai documenti
            
        Returns:
            Header markdown formattato con warning su copertura parziale
        """
        coverage_pct = (num_docs_analyzed / total_docs_available * 100) if total_docs_available > 0 else 0
        
        # Identifica categorie MANCANTI
        categories_found = set(asset_categories_found or [])
        categories_missing = [cat for cat in self.KNOWN_ASSET_CATEGORIES if cat not in categories_found]
        
        # Messaggio esplicito sulla copertura
        coverage_warning = ""
        if coverage_pct < 10:
            coverage_warning = f"\n> 🔴 **COPERTURA LIMITATA**: Analizzato solo il {coverage_pct:.1f}% degli atti. I dati potrebbero non rappresentare il quadro completo."
        elif coverage_pct < 30:
            coverage_warning = f"\n> 🟠 **COPERTURA PARZIALE**: Analizzato il {coverage_pct:.1f}% degli atti. Alcune aree potrebbero non essere rappresentate."
        
        # Costruisci riga tipologie non coperte
        not_covered_line = ""
        if categories_missing:
            not_covered_line = f"\n| **⚠️ Categorie NON presenti** | {', '.join(categories_missing[:5])}{'...' if len(categories_missing) > 5 else ''} |"
        
        header = f"""---
## ⚠️ NOTA METODOLOGICA - Limiti del Report

| Metrica | Valore |
|---------|--------|
| **Atti analizzati** | {num_docs_analyzed} su {total_docs_available} disponibili ({coverage_pct:.1f}%) |
| **Periodo coperto** | {date_range or 'Non specificato'} |
| **Tipologie incluse** | {', '.join(doc_types) if doc_types else 'Varie'} |
| **Categorie patrimonio coperte** | {', '.join(categories_found) if categories_found else 'Da verificare'} |{not_covered_line}
{coverage_warning}

> ⚡ **AVVERTENZA IMPORTANTE**:  
> Questo report analizza un **campione** degli atti disponibili.  
> NON rappresenta necessariamente l'intero patrimonio comunale.  
> Categorie come "{categories_missing[0] if categories_missing else 'altre aree'}" potrebbero non essere coperte.  
> NON sostituisce la progettazione tecnica e la validazione degli uffici competenti.

---

"""
        return header
    
    def _extract_doc_types_from_evidences(self, evidences: List[Dict]) -> List[str]:
        """
        Estrae tipologie di documenti dalle evidenze analizzate.
        
        Returns:
            Lista di tipologie uniche (es: ['Deliberazione', 'Determinazione', 'Progetto'])
        """
        types = set()
        for ev in evidences:
            title = ev.get("title", "").lower()
            # Rileva tipologia dal titolo
            if "delibera" in title:
                types.add("Deliberazioni")
            elif "determina" in title:
                types.add("Determinazioni")
            elif "progetto" in title or "pnrr" in title:
                types.add("Progetti")
            elif "manutenzione" in title:
                types.add("Manutenzioni")
            elif "accordo quadro" in title:
                types.add("Accordi Quadro")
            elif "approvazione" in title:
                types.add("Approvazioni")
        
        return list(types) if types else ["Atti amministrativi vari"]
    
    def _extract_asset_categories_from_evidences(self, evidences: List[Dict]) -> List[str]:
        """
        Estrae le categorie di patrimonio comunale coperte dai documenti.
        Serve per mostrare all'utente cosa È e cosa NON È coperto.
        
        Returns:
            Lista di categorie patrimonio identificate
        """
        categories = set()
        
        # Keywords per ogni categoria
        category_keywords = {
            "Viabilità e Ponti": ["pont", "strada", "viabil", "cavalcavia", "guard rail", "giunti", "asfalto", "rotonda", "marciapiede", "sottopasso"],
            "Edilizia Scolastica": ["scuol", "scolastic", "infanzia", "primaria", "secondaria", "nido", "asilo", "istituto"],
            "Impianti Sportivi": ["sport", "palestra", "piscina", "stadio", "campo", "impianto sportivo", "polisportiv"],
            "Verde Pubblico": ["verde", "parco", "giardino", "alberi", "potatura", "manutenzione verde", "aiuole"],
            "Illuminazione Pubblica": ["illuminazione", "lampioni", "pubblica illuminazione", "led", "pali luce"],
            "Cimiteri": ["cimitero", "cimiteriale", "tombe", "loculi", "cappelle"],
            "Edifici Comunali": ["palazzo", "uffici comunal", "sede", "municipio", "anagrafe"],
            "Mercati": ["mercato", "mercati", "san lorenzo", "sant'ambrogio", "banchi"],
            "Patrimonio Culturale": ["museo", "teatro", "biblioteca", "monumento", "unesco", "restauro", "palazzo storico"]
        }
        
        for ev in evidences:
            # Gestisci sia stringhe che dizionari
            title_raw = ev.get("title", "")
            title = title_raw.lower() if isinstance(title_raw, str) else str(title_raw).lower()
            
            content_raw = ev.get("content", "")
            if isinstance(content_raw, str):
                content = content_raw.lower()[:500]
            elif isinstance(content_raw, dict):
                # Se content è un dict, estrai text o converti in stringa
                content = str(content_raw.get("text", content_raw)).lower()[:500]
            else:
                content = str(content_raw).lower()[:500]
            
            text = f"{title} {content}"
            
            for category, keywords in category_keywords.items():
                if any(kw in text for kw in keywords):
                    categories.add(category)
        
        return list(categories)
    
    def _extract_date_range_from_evidences(self, evidences: List[Dict]) -> str:
        """
        Estrae range temporale dalle date dei documenti.
        
        Returns:
            Stringa range (es: "Gen 2024 - Nov 2025")
        """
        dates = []
        for ev in evidences:
            date = ev.get("protocol_date", "")
            if date:
                # Prova a parsare la data
                try:
                    # Formato atteso: DD/MM/YYYY o YYYY-MM-DD
                    if "/" in date:
                        parts = date.split("/")
                        if len(parts) == 3:
                            dates.append(f"{parts[2]}-{parts[1]}")
                    elif "-" in date:
                        parts = date.split("-")
                        if len(parts) >= 2:
                            dates.append(f"{parts[0]}-{parts[1]}")
                except:
                    pass
        
        if not dates:
            return "2024-2025"
        
        dates.sort()
        if len(dates) == 1:
            return dates[0]
        return f"{dates[0]} - {dates[-1]}"
    
    def _extract_claim_numbers(self, text: str) -> List[str]:
        """Estrae numeri di claim citate nel testo"""
        import re
        claims = re.findall(r'\(CLAIM_\d+\)', text)
        return list(set(claims))  # Rimuovi duplicati
    
    def _has_citations(self, text: str) -> bool:
        """Verifica se il testo contiene citazioni claim"""
        return "(CLAIM_" in text
    
    def _build_sources_list(self, evidences: List[Dict]) -> List[Dict]:
        """
        Costruisce lista di fonti complete con metadata per visualizzazione con link
        
        Args:
            evidences: Lista di evidenze verificate
            
        Returns:
            Lista di oggetti Source con url, title, document_id
        """
        sources_dict = {}
        for ev in evidences:
            source_str = ev.get("source", "")
            if not source_str:
                continue
            
            metadata = ev.get("metadata", {})
            document_id = metadata.get("document_id") or ev.get("evidence_id", "")
            
            if document_id:
                source_url = f"#doc-{document_id}"
            else:
                source_url = metadata.get("url") or source_str
            
            source_title = metadata.get("title") or metadata.get("oggetto") or source_str
            
            if source_str not in sources_dict:
                sources_dict[source_str] = {
                    "url": source_url,
                    "title": source_title,
                    "document_id": document_id,
                    "type": "internal" if document_id else "external"
                }
        
        return list(sources_dict.values())
    
    def _is_generative_query(self, question: str) -> bool:
        """
        Rileva se la query è generativa/creativa e richiede AI anche senza documenti
        
        Args:
            question: Domanda dell'utente
            
        Returns:
            True se la query è generativa/creativa
        """
        lower_question = question.lower().strip()
        
        # Parole chiave che indicano richieste creative/analitiche
        generative_keywords = [
            # Creazione e generazione
            'crea', 'creare', 'genera', 'generare', 'costruisci', 'costruire',
            'sviluppa', 'sviluppare', 'progetta', 'progettare', 'disegna', 'disegnare',
            'elabora', 'elaborare', 'formula', 'formulare', 'definisci', 'definire',
            
            # Matrici e strutture complesse
            'matrice', 'matrici', 'tabella', 'tabelle', 'grafico', 'grafici',
            'schema', 'schemi', 'diagramma', 'diagrammi', 'modello', 'modelli',
            
            # Analisi complesse
            'analizza', 'analizzare', 'analisi', 'valuta', 'valutare', 'valutazione',
            'confronta', 'confrontare', 'confronto', 'paragona', 'paragonare',
            'calcola', 'calcolare', 'calcolo', 'misura', 'misurare', 'misurazione',
            'quantifica', 'quantificare', 'quantificazione', 'stima', 'stimare', 'stima',
            
            # Prioritizzazione e decisioni
            'prioritizza', 'prioritizzare', 'priorità', 'prioritizzazione',
            'ordina', 'ordinare', 'ordine', 'classifica', 'classificare', 'classificazione',
            'raccomanda', 'raccomandare', 'raccomandazione', 'suggerisci', 'suggerire',
            'consiglia', 'consigliare', 'consiglio', 'proponi', 'proporre', 'proposta',
            
            # Strategia e pianificazione
            'strategia', 'strategie', 'pianifica', 'pianificare', 'pianificazione',
            'piano', 'piani', 'roadmap', 'road map',
            
            # Sintesi e riassunti
            'riassumi', 'riassumere', 'riassunto', 'sintetizza', 'sintetizzare', 'sintesi',
            'riepiloga', 'riepilogare', 'riepilogo', 'compendi', 'compendio',
            
            # Spiegazioni complesse
            'spiega', 'spiegare', 'spiegazione', 'illustra', 'illustrare', 'illustrazione',
            'descrivi', 'descrivere', 'descrizione', 'dettaglia', 'dettagliare', 'dettaglio',
            'commenta', 'commentare', 'commento', 'interpreta', 'interpretare', 'interpretazione',
            
            # Calcoli complessi
            'sroi', 'roi', 'npv', 'irr', 'payback', 'break-even', 'break even',
            'impatto', 'impatti', 'beneficio', 'benefici', 'costo', 'costi',
            'fattibilità', 'fattibilita', 'fattibile', 'viabilità', 'viabilita', 'viabile',
        ]
        
        # Pattern specifici che richiedono AI
        import re
        generative_patterns = [
            r'crea.*matrice', r'crea.*tabella', r'crea.*grafico', r'crea.*schema', r'crea.*modello',
            r'matrice.*decision', r'matrice.*priorit', r'prioritizza.*progetti', r'analizza.*impatto',
            r'calcola.*sroi', r'calcola.*roi', r'strategia.*per', r'piano.*per',
        ]
        
        has_keyword = any(keyword in lower_question for keyword in generative_keywords)
        matches_pattern = any(re.search(pattern, question, re.IGNORECASE) for pattern in generative_patterns)
        
        return has_keyword or matches_pattern
    
    def _extract_search_entities(self, question: str) -> List[str]:
        """
        Estrae entità chiave dalla query per ricerca documenti più efficace
        
        Args:
            question: Domanda dell'utente
            
        Returns:
            Lista di entità chiave per la ricerca
        """
        lower_question = question.lower()
        entities = []
        
        # Entità comuni nei documenti PA
        pa_entities = [
            'progetto', 'progetti', 'delibera', 'delibere', 'determina', 'determine',
            'atto', 'atti', 'ordinanza', 'ordinanze', 'regolamento', 'regolamenti',
            'bando', 'bandi', 'gara', 'gare', 'appalto', 'appalti',
            'intervento', 'interventi', 'iniziativa', 'iniziative', 'programma', 'programmi',
            'investimento', 'investimenti', 'finanziamento', 'finanziamenti',
            'lavoro', 'lavori', 'opera', 'opere', 'infrastruttura', 'infrastrutture',
        ]
        
        # Cerca entità nella query
        for entity in pa_entities:
            if entity in lower_question:
                entities.append(entity)
        
        return entities
    
    async def _expand_generative_query(self, question: str) -> List[str]:
        """
        Espande query generativa per migliorare il retrieval di documenti rilevanti
        Genera multiple query di ricerca per massimizzare il recupero
        
        Args:
            question: Domanda originale dell'utente
            
        Returns:
            Lista di query per ricerca documenti
        """
        entities = self._extract_search_entities(question)
        queries = []
        
        # Query 1: Query originale (sempre)
        queries.append(question)
        
        # Query 2: Se ci sono entità, cerca documenti che le contengano (max 1 query)
        if entities:
            queries.append(f"{question} {' '.join(entities[:5])}")  # Max 5 entità per evitare query troppo lunghe
        
        # Query 3: Query generica SINGOLA per progetti/delibere (se rilevante)
        # OTTIMIZZAZIONE: Ridotto da 3 a 1 query per evitare timeout
        lower_question = question.lower()
        if 'progetto' in lower_question or 'priorit' in lower_question or 'deliber' in lower_question:
            queries.append('progetti delibere atti')  # Query generica unica
        
        logger.info(f"Query espansa per ricerca documenti: {queries}")
        return queries
    
    async def rag_fortress(
        self,
        question: str,
        tenant_id: str,
        user_id: Optional[int] = None,
        mode: str = "strict",
        messages: Optional[List] = None
    ) -> Dict:
        """
        Pipeline completa RAG-Fortress
        
        Args:
            question: Domanda dell'utente (ultimo messaggio)
            tenant_id: ID del tenant
            user_id: ID dell'utente per memorie personalizzate (opzionale)
            mode: Modalità pipeline ("strict" o "generative")
                - "strict": Verifica rigorosa con URS, rifiuta se URS < 90
                - "generative": Sintesi generativa con contesto tenant, no verifica URS rigorosa
            messages: Cronologia completa dei messaggi della conversazione (opzionale)
            
        Returns:
            Dict con:
            - answer: Risposta generata
            - urs_score: Score URS 0-100 (None per mode="generative")
            - urs_explanation: Spiegazione score
            - claims_used: Lista di claim utilizzate
            - sources: Lista di fonti
            - hallucinations_found: Lista di allucinazioni
            - gaps_detected: Lista di gap
        """
        try:
            logger.info(f"Avvio pipeline RAG-Fortress per tenant {tenant_id}")
            
            # Costruisci contesto conversazionale se presente
            enriched_question = question
            if messages and len(messages) > 1:
                conversation_context = []
                for msg in messages[:-1]:  # Escludi ultimo messaggio (domanda corrente)
                    # Supporta sia dict che oggetti Pydantic
                    if hasattr(msg, 'role'):
                        role = msg.role
                        content = msg.content
                    else:
                        role = msg.get("role", "user")
                        content = msg.get("content", "")
                    
                    if role == "user":
                        conversation_context.append(f"Utente: {content}")
                    elif role == "assistant":
                        conversation_context.append(f"Assistente: {content}")
                
                if conversation_context:
                    context_str = "\n".join(conversation_context)
                    enriched_question = f"[CONTESTO CONVERSAZIONE]\n{context_str}\n\n[DOMANDA CORRENTE]\n{question}"
                    logger.info(f"Aggiunto contesto conversazionale: {len(conversation_context)} messaggi precedenti")
            
            # STEP 1: Retrieve evidenze (over-retrieve 100)
            logger.info("STEP 1: Retrieval evidenze...")
            
            # Se la query è generativa, espandila per migliorare il retrieval
            is_generative = self._is_generative_query(question)
            search_queries = [enriched_question]  # Usa domanda arricchita con contesto
            
            if is_generative:
                search_queries = await self._expand_generative_query(question)
                logger.info(f"Query generativa rilevata, uso {len(search_queries)} query per retrieval: {search_queries}")
            
            # Esegui ricerche multiple e combina risultati
            all_evidences = []
            seen_ids = set()
            
            for search_query in search_queries:
                # Per query generative, abbassa la soglia di rilevanza
                temp_threshold = self.retriever.relevance_threshold
                if is_generative:
                    self.retriever.relevance_threshold = 0.0  # Accetta tutti i risultati per query generative
                
                try:
                    # Per query generative, usa top_k moderato per bilanciare recall e velocità
                    # OTTIMIZZAZIONE: Ridotto da 200 a 100 per evitare timeout con fallback manuale
                    retrieval_top_k = 100 if not is_generative else 100
                    
                    query_evidences = await self.retriever.retrieve_evidence(
                        question=search_query,
                        tenant_id=str(tenant_id),
                        user_id=user_id,  # Passa user_id per memorie personalizzate
                        top_k=retrieval_top_k
                    )
                    
                    # Aggiungi solo evidenze non duplicate
                    for ev in query_evidences:
                        ev_id = ev.get("evidence_id") or ev.get("_id")
                        if ev_id and ev_id not in seen_ids:
                            seen_ids.add(ev_id)
                            all_evidences.append(ev)
                finally:
                    # Ripristina soglia originale
                    self.retriever.relevance_threshold = temp_threshold
            
            # OTTIMIZZAZIONE: Ridotto da 100 a 50 per evitare timeout
            evidences = all_evidences[:50]  # Limita a 50 evidenze totali (bilanciamento performance/qualità)
            logger.info(f"Trovate {len(evidences)} evidenze totali dopo ricerche multiple")
            
            if not evidences:
                # Se la query è generativa E non ci sono evidenze, NON generare template
                # Dice chiaramente che servono documenti reali (messaggio generico per qualsiasi query)
                if is_generative or mode == "generative":
                    logger.warning("Query generativa senza evidenze: NON genero template, richiedo documenti reali")
                    return {
                        "answer": "Per rispondere a questa domanda con dati verificati, è necessario che nel database siano presenti documenti rilevanti.\n\nAttualmente non sono stati trovati documenti pertinenti nel database.\n\n**Cosa puoi fare:**\n1. Verifica che i documenti siano stati importati nel sistema\n2. Assicurati che i documenti contengano informazioni pertinenti alla tua domanda\n3. Riprova dopo aver importato i documenti necessari\n\n**Nota:** Il sistema non genera risposte senza fonti verificate, ma richiede dati reali per garantire affidabilità.",
                        "urs_score": 0.0,
                        "urs_explanation": "Nessun documento trovato nel database. Impossibile generare risposta verificata senza fonti.",
                        "claims_used": [],
                        "sources": [],
                        "hallucinations_found": [],
                        "gaps_detected": ["GAP_01: Nessun documento trovato nel database"]
                    }
                
                logger.warning("Nessuna evidenza recuperata")
                return {
                    "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                    "urs_score": 0.0,
                    "urs_explanation": "Nessuna evidenza recuperata dal database.",
                    "claims_used": [],
                    "sources": [],
                    "hallucinations_found": [],
                    "gaps_detected": ["GAP_01: Nessuna informazione disponibile"]
                }
            
            # MODE GENERATIVE: Sintesi diretta con contesto tenant (no verifiche rigorose)
            if mode == "generative":
                logger.info(f"Modalità GENERATIVE attiva: sintesi generativa con {len(evidences)} documenti tenant come contesto")
                return await self._generative_synthesis_with_context(
                    question=question,
                    evidences=evidences,
                    tenant_id=tenant_id,
                    messages=messages
                )
            
            # MODE STRICT: Pipeline completa con verifiche rigorose
            # STEP 2: Verifica evidenze
            logger.info("STEP 2: Verifica evidenze...")
            verified_evidences = await self.evidence_verifier.verify_evidence(
                user_question=question,
                evidences=evidences
            )
            
            # Filtra solo evidenze direttamente rilevanti
            relevant_evidences = [
                ev for ev in verified_evidences
                if ev.get("is_directly_relevant", False) and ev.get("relevance_score", 0) >= 7.0
            ]
            
            if not relevant_evidences:
                # Se la query è generativa, usa comunque le evidenze trovate (anche se non direttamente rilevanti)
                # Questo permette di generare risposte con dati reali invece di template
                if is_generative and verified_evidences:
                    logger.info("Query generativa: uso evidenze verificate anche se non direttamente rilevanti per generare risposta con dati reali")
                    # Usa le prime 20 evidenze verificate con score più alto
                    relevant_evidences = sorted(
                        verified_evidences,
                        key=lambda x: x.get("relevance_score", 0),
                        reverse=True
                    )[:20]
                else:
                    logger.warning("Nessuna evidenza direttamente rilevante")
                    return {
                        "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                        "urs_score": 0.0,
                        "urs_explanation": "Nessuna evidenza direttamente rilevante trovata.",
                        "claims_used": [],
                        "sources": [],
                        "hallucinations_found": [],
                        "gaps_detected": ["GAP_01: Nessuna evidenza rilevante disponibile"]
                    }
            
            # STEP 3: Estrai claim atomiche
            logger.info("STEP 3: Estrazione claim...")
            claims = await self.claim_extractor.extract_atomic_claims(relevant_evidences)
            
            if not claims or claims == ["[NO_CLAIMS]"]:
                logger.warning("Nessuna claim estratta")
                return {
                    "answer": "Non dispongo di informazioni verificate sufficienti per rispondere alla domanda posta.",
                    "urs_score": 0.0,
                    "urs_explanation": "Nessuna claim fattuale estratta dalle evidenze.",
                    "claims_used": [],
                    "sources": [],
                    "hallucinations_found": [],
                    "gaps_detected": ["GAP_01: Nessuna informazione fattuale verificabile"]
                }
            
            # STEP 4: Rileva gap
            logger.info("STEP 4: Rilevamento gap...")
            gaps = await self.gap_detector.detect_gaps(question, claims)
            
            # STEP 5: Sintetizza risposta
            logger.info("STEP 5: Sintesi risposta...")
            answer = await self.synthesizer.synthesize_response(
                user_question=question,
                claims=claims,
                gaps=gaps
            )
            
            # STEP 6: Fact-checking ostile
            logger.info("STEP 6: Fact-checking ostile...")
            hallucinations = await self.fact_checker.hostile_check(answer, claims)
            
            # Se ci sono allucinazioni gravi, rifiuta risposta
            if hallucinations and hallucinations != ["NESSUNA_ALLUCINAZIONE"]:
                logger.warning(f"Allucinazioni trovate: {hallucinations}")
                # Calcola URS comunque
                claim_numbers = self._extract_claim_numbers(answer)
                urs_result = self.urs_calculator.calculate_urs(
                    claims_used=len(claim_numbers),
                    total_claims=len(claims),
                    gaps=gaps,
                    hallucinations=hallucinations,
                    citations_present=self._has_citations(answer)
                )
                
                # Se URS < 90, rifiuta risposta
                if urs_result["score"] < 90:
                    return {
                        "answer": "Non posso fornire una risposta verificata sufficientemente affidabile. Si prega di riformulare la domanda o consultare direttamente i documenti ufficiali.",
                        "urs_score": urs_result["score"],
                        "urs_explanation": f"{urs_result['explanation']} Risposta rifiutata per affidabilità insufficiente.",
                        "claims_used": claim_numbers,
                        "sources": self._build_sources_list(relevant_evidences),
                        "hallucinations_found": hallucinations,
                        "gaps_detected": gaps
                    }
            
            # STEP 7: Calcola URS finale
            logger.info("STEP 7: Calcolo URS...")
            claim_numbers = self._extract_claim_numbers(answer)
            urs_result = self.urs_calculator.calculate_urs(
                claims_used=len(claim_numbers),
                total_claims=len(claims),
                gaps=gaps,
                hallucinations=hallucinations if hallucinations != ["NESSUNA_ALLUCINAZIONE"] else [],
                citations_present=self._has_citations(answer)
            )
            
            # Estrai fonti complete con metadata per visualizzazione con link
            sources_dict = {}
            for ev in relevant_evidences:
                source_str = ev.get("source", "")
                if not source_str:
                    continue
                
                # Costruisci oggetto Source completo
                metadata = ev.get("metadata", {})
                document_id = metadata.get("document_id") or ev.get("evidence_id", "")
                
                # Se document_id esiste, crea link interno (#doc-{document_id})
                if document_id:
                    source_url = f"#doc-{document_id}"
                else:
                    # Altrimenti usa source originale o metadata.url
                    source_url = metadata.get("url") or source_str
                
                source_title = metadata.get("title") or metadata.get("oggetto") or source_str
                
                # Usa source_str come chiave per evitare duplicati
                if source_str not in sources_dict:
                    sources_dict[source_str] = {
                        "url": source_url,
                        "title": source_title,
                        "document_id": document_id,
                        "type": "internal" if document_id else "external"
                    }
            
            # Converti dict in lista
            sources = list(sources_dict.values())
            
            logger.info(f"Pipeline completata - URS: {urs_result['score']}/100, Sources: {len(sources)}")
            
            return {
                "answer": answer,
                "urs_score": urs_result["score"],
                "urs_explanation": urs_result["explanation"],
                "claims_used": claim_numbers,
                "sources": sources,  # Ora è array di oggetti con url, title, document_id
                "hallucinations_found": hallucinations if hallucinations != ["NESSUNA_ALLUCINAZIONE"] else [],
                "gaps_detected": gaps if gaps != ["FULL_COVERAGE"] else []
            }
        
        except APIError as api_err:
            # Errore API con messaggio user-friendly
            logger.error(f"Errore API nella pipeline RAG-Fortress: {api_err.error_type.value} - {api_err.message}")
            return {
                "answer": api_err.user_message,
                "urs_score": 0.0,
                "urs_explanation": f"Errore servizio AI: {api_err.error_type.value}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": [],
                "error_type": api_err.error_type.value,
                "error_provider": api_err.provider
            }
            
        except Exception as e:
            logger.error(f"Errore nella pipeline RAG-Fortress: {e}", exc_info=True)
            return {
                "answer": "Si è verificato un errore durante l'elaborazione della domanda. Si prega di riprovare.",
                "urs_score": 0.0,
                "urs_explanation": f"Errore tecnico: {str(e)}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }
    
    async def _multi_step_synthesis(
        self,
        question: str,
        evidences: List[Dict],
        tenant_id: str,
        messages: Optional[List] = None
    ) -> Dict:
        """
        Sintesi multi-step per processare grandi volumi di documenti (>10)
        
        Strategia:
        1. Divide documenti in batch da 10
        2. Estrae informazioni chiave da ogni batch
        3. Aggrega risultati e crea sintesi finale
        
        Args:
            question: Domanda dell'utente
            evidences: Lista completa di evidenze (può essere >100)
            tenant_id: ID del tenant
            messages: Cronologia messaggi (opzionale)
            
        Returns:
            Dict con risposta aggregata da tutti i documenti
        """
        try:
            batch_size = 10
            total_docs = len(evidences)
            num_batches = (total_docs + batch_size - 1) // batch_size  # Ceiling division
            
            logger.info(f"🔄 MULTI-STEP: Processamento {total_docs} documenti in {num_batches} batch da {batch_size}")
            
            # Genera messaggio di avviso per l'utente (usa parametri keyword per nuova signature)
            processing_notice = self._generate_processing_notice(
                total_docs=total_docs,  # backward compatibility
                num_batches=num_batches, 
                question=question
            )
            logger.info(f"📢 Processing notice: {processing_notice}")
            
            # STEP 1: Estrai informazioni chiave da ogni batch
            batch_summaries = []
            all_sources = []
            
            for batch_idx in range(num_batches):
                start_idx = batch_idx * batch_size
                end_idx = min(start_idx + batch_size, total_docs)
                batch = evidences[start_idx:end_idx]
                
                logger.info(f"📦 Batch {batch_idx + 1}/{num_batches}: documenti {start_idx + 1}-{end_idx}")
                
                # Estrai contenuto batch
                batch_docs = []
                batch_sources = []
                for i, ev in enumerate(batch, start=start_idx + 1):
                    content = ev.get("content", {})
                    
                    if isinstance(content, dict):
                        text = content.get("text") or content.get("full_text") or content.get("raw_text") or ""
                    elif isinstance(content, str):
                        text = content
                    else:
                        text = str(content) if content else ""
                    
                    if text:
                        # Limita lunghezza documento a 2000 caratteri per batch processing
                        text_preview = text[:2000] + "..." if len(text) > 2000 else text
                        
                        # Usa citazione precisa invece di [DOC X]
                        citation = self._build_document_citation(ev, i)
                        batch_docs.append(f"{citation}\n{text_preview}\n")
                        
                        # Raccogli metadata
                        doc_id = ev.get("document_id", "unknown")
                        doc_title = ev.get("title", "Documento senza titolo")
                        protocol = ev.get("protocol_number", "")
                        date = ev.get("protocol_date", "")
                        
                        doc_url = f"/natan/documents/view/{doc_id}" if doc_id != "unknown" else ""
                        
                        batch_sources.append({
                            "document_id": doc_id,
                            "title": doc_title,
                            "url": doc_url,
                            "protocol_number": protocol,
                            "date": date,
                            "doc_number": i,
                            "citation": citation  # Salva citazione per riferimento
                        })
                
                all_sources.extend(batch_sources)
                
                # Chiedi all'AI di estrarre informazioni chiave dal batch
                batch_context = "\n\n".join(batch_docs)
                
                adapter_context = {
                    "tenant_id": int(tenant_id),
                    "persona": "strategic",
                    "task_class": "extraction"
                }
                
                adapter = self.ai_router.get_chat_adapter(adapter_context)
                
                extraction_prompt = [
                    {
                        "role": "system",
                        "content": """Sei un assistente esperto che estrae informazioni chiave da documenti della Pubblica Amministrazione.

Analizza i documenti forniti ed estrai:
1. Progetti/iniziative menzionate
2. Date e scadenze rilevanti
3. Budget e finanziamenti
4. Stakeholder e responsabili
5. Criticità e vincoli

Rispondi in formato strutturato e conciso, max 500 parole per batch."""
                    },
                    {
                        "role": "user",
                        "content": f"""Domanda utente: {question}

Documenti batch {batch_idx + 1}/{num_batches}:

{batch_context}

Estrai le informazioni chiave rilevanti per rispondere alla domanda dell'utente."""
                    }
                ]
                
                result = await adapter.generate(
                    extraction_prompt,
                    temperature=0.3,
                    max_tokens=800
                )
                
                batch_summary = result["content"].strip()
                batch_summaries.append(f"**BATCH {batch_idx + 1} (Documenti {start_idx + 1}-{end_idx}):**\n{batch_summary}")
                
                logger.info(f"✅ Batch {batch_idx + 1} processato: {len(batch_summary)} caratteri estratti")
            
            # STEP 2: Aggrega tutti i batch e crea sintesi finale
            logger.info(f"🔄 Aggregazione {len(batch_summaries)} batch per sintesi finale")
            
            aggregated_context = "\n\n".join(batch_summaries)
            
            # Costruisci cronologia conversazione se presente
            conversation_history = ""
            if messages and len(messages) > 1:
                for msg in messages[:-1]:
                    if hasattr(msg, 'role'):
                        role = msg.role
                        content = msg.content
                    else:
                        role = msg.get("role", "user")
                        content = msg.get("content", "")
                    
                    if role == "user":
                        conversation_history += f"Utente: {content}\n"
                    elif role == "assistant":
                        conversation_history += f"Assistente: {content}\n"
                
                if conversation_history:
                    conversation_history = f"\n\n**CRONOLOGIA CONVERSAZIONE:**\n{conversation_history}\n"
            
            # Estrai metadata per header metodologico
            doc_types = self._extract_doc_types_from_evidences(evidences)
            date_range = self._extract_date_range_from_evidences(evidences)
            asset_categories = self._extract_asset_categories_from_evidences(evidences)
            
            # Recupera totale documenti disponibili per calcolo copertura
            from app.services.mongodb_service import MongoDBService
            try:
                total_available = MongoDBService.count_documents(
                    "documents", 
                    {"tenant_id": int(tenant_id), "embedding": {"$exists": True}}
                )
            except:
                total_available = total_docs * 10  # Fallback stima
            
            # Costruisci header metodologico CON categorie patrimonio coperte/mancanti
            methodology_header = self._build_methodology_header(
                num_docs_analyzed=total_docs,
                total_docs_available=total_available,
                date_range=date_range,
                doc_types=doc_types,
                asset_categories_found=asset_categories
            )
            
            # Prompt finale per sintesi CON SISTEMA SEMAFORO e HEADER METODOLOGICO
            final_prompt = [
                {
                    "role": "system",
                    "content": f"""Sei un assistente esperto per la Pubblica Amministrazione italiana.

Hai a disposizione informazioni estratte da {total_docs} documenti su {total_available} totali disponibili.

=== STRUTTURA OBBLIGATORIA DELLA RISPOSTA ===

La risposta DEVE seguire questa struttura ESATTA:

**SEZIONE 1 - HEADER METODOLOGICO** (OBBLIGATORIO, inseriscilo all'inizio):
{methodology_header}

**SEZIONE 2 - LEGENDA SEMAFORO** (subito dopo l'header):
---
**📊 LEGENDA AFFIDABILITÀ DATI**

🟢 **VERIFICATO** = Dato presente nei documenti ufficiali
🟠 **STIMATO** = Elaborazione/stima basata su dati parziali  
🔴 **PROPOSTA AI** = Suggerimento del sistema, NON nei documenti
---

**SEZIONE 3 - PARTE A: STATO ATTUALE (solo fatti 🟢/🟠)**
Qui descrivi SOLO ciò che emerge dai documenti. NO proposte.

**SEZIONE 4 - PARTE B: PROPOSTE E RACCOMANDAZIONI (solo 🔴)**
Qui metti le tue proposte, CHIARAMENTE SEPARATE dai fatti.

=== TONO OBBLIGATORIO PER PROPOSTE 🔴 ===

Le proposte AI devono usare SEMPRE linguaggio CONDIZIONALE e PRUDENTE:

VERBI DA USARE:
- ✅ "Si potrebbe valutare...", "Sarebbe opportuno considerare..."
- ✅ "Una possibile soluzione potrebbe essere...", "Si suggerisce di..."
- ❌ MAI: "Bisogna fare...", "È necessario...", "Occorre implementare..."

NUMERI DA USARE:
- ✅ "Budget indicativo: €150.000-250.000 (da validare con uffici tecnici)"
- ✅ "Stima preliminare soggetta a verifica: €X-Y"
- ❌ MAI numeri puntuali senza range

DISCLAIMER DA AGGIUNGERE:
- Alla fine di ogni proposta significativa, aggiungi: "(proposta da validare)"
- Per KPI: "Target indicativo, da calibrare su dati storici reali"

=== REGOLE CITAZIONI ===

Usa citazioni PRECISE, non generiche:
- ✅ CORRETTO: "€500.000 per guard rail [Prot. 00457 - Accordo Quadro Manutenzione]"  
- ❌ SBAGLIATO: "€500.000 [DOC 1-10]"

=== REGOLE NUMERI 🔴 ===

MAI numeri puntuali per proposte AI. USA SEMPRE RANGE:
- ✅ CORRETTO: "🔴 €150.000-250.000 (stima indicativa, da validare)"
- ❌ SBAGLIATO: "🔴 €200.000"

CONTESTO CONVERSAZIONALE:{conversation_history}

NON includere sezione fonti - verrà aggiunta automaticamente."""
                },
                {
                    "role": "user",
                    "content": f"""Informazioni estratte da {total_docs} documenti:

{aggregated_context}

---

Domanda dell'utente:
{question}

CHECKLIST FINALE:
☐ Header metodologico inserito all'inizio
☐ Legenda semaforo presente
☐ PARTE A (fatti) separata da PARTE B (proposte)
☐ Citazioni precise con protocollo
☐ Numeri 🔴 come RANGE, non puntuali

Elabora la risposta seguendo la struttura obbligatoria."""
                }
            ]
            
            result = await adapter.generate(
                final_prompt,
                temperature=0.7,
                max_tokens=4000
            )
            
            answer = result["content"].strip()
            
            logger.info(f"✅ Sintesi finale: {len(answer)} caratteri da {total_docs} documenti")
            
            # Post-processing: Aggiungi link cliccabili
            answer_with_links = self._add_clickable_links_to_answer(answer, all_sources)
            
            return {
                "answer": answer_with_links,
                "urs_score": None,
                "urs_explanation": f"Modalità GENERATIVA MULTI-STEP: sintesi da {total_docs} documenti processati in {num_batches} batch. Non applicate verifiche URS rigorose.",
                "claims_used": [],
                "sources": all_sources[:50],  # Limita fonti a prime 50 per visualizzazione
                "hallucinations_found": [],
                "gaps_detected": [],
                "processing_notice": processing_notice  # Includi messaggio di progresso
            }
        
        except APIError as api_err:
            # Errore API con messaggio user-friendly
            logger.error(f"Errore API durante sintesi multi-step: {api_err.error_type.value} - {api_err.message}")
            return {
                "answer": api_err.user_message,
                "urs_score": 0.0,
                "urs_explanation": f"Errore servizio AI: {api_err.error_type.value}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": [],
                "error_type": api_err.error_type.value,
                "error_provider": api_err.provider
            }
            
        except Exception as e:
            logger.error(f"Errore durante sintesi multi-step: {e}", exc_info=True)
            return {
                "answer": "Si è verificato un errore durante l'elaborazione multi-step della richiesta. Si prega di riprovare.",
                "urs_score": 0.0,
                "urs_explanation": f"Errore tecnico multi-step: {str(e)}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }
    
    def _generate_processing_notice(
        self, 
        total_available: int = None,
        docs_retrieved: int = None,
        docs_to_process: int = None,
        num_batches: int = None, 
        question: str = "",
        # Backward compatibility
        total_docs: int = None
    ) -> str:
        """
        Genera messaggio informativo per l'utente durante processamento lungo
        
        Args:
            total_available: Numero TOTALE documenti disponibili nel database
            docs_retrieved: Numero documenti estratti da vector search (max 100)
            docs_to_process: Numero documenti che verranno effettivamente processati (max 50)
            num_batches: Numero di batch
            question: Domanda dell'utente
            total_docs: (deprecated) Per backward compatibility
            
        Returns:
            Messaggio rassicurante e informativo con numeri REALI del flusso
        """
        # Backward compatibility
        if total_docs is not None and docs_to_process is None:
            docs_to_process = total_docs
            total_available = total_docs
            docs_retrieved = total_docs
        
        if docs_to_process is None:
            docs_to_process = 50
        if docs_retrieved is None:
            docs_retrieved = min(total_available or 100, 100)
        if total_available is None:
            total_available = docs_to_process
        if num_batches is None:
            num_batches = (docs_to_process + 9) // 10
            
        # Stima tempo: ~15 secondi per batch + 20 secondi aggregazione finale
        estimated_seconds = (num_batches * 15) + 20
        estimated_minutes = estimated_seconds // 60
        
        if estimated_minutes < 1:
            time_str = f"circa {estimated_seconds} secondi"
        elif estimated_minutes == 1:
            time_str = "circa un minuto"
        else:
            time_str = f"circa {estimated_minutes} minuti"
        
        # Personalizza messaggio in base al tipo di query
        query_lower = question.lower()
        
        if "matrice" in query_lower or "tabella" in query_lower:
            task_type = "la matrice decisionale completa"
        elif "analisi" in query_lower or "analizza" in query_lower:
            task_type = "l'analisi approfondita"
        elif "priorit" in query_lower:
            task_type = "la prioritizzazione strategica"
        elif "confronto" in query_lower or "confronta" in query_lower:
            task_type = "il confronto dettagliato"
        else:
            task_type = "l'elaborazione completa"
        
        # Costruisci la parte relativa ai documenti con TRASPARENZA TOTALE sul flusso
        if total_available > docs_to_process:
            # Messaggio che spiega ESATTAMENTE il flusso di retrieval
            docs_info = f"📂 **Archivio disponibile:** {total_available} documenti"
            
            # Spiega il processo di selezione
            if total_available > docs_retrieved:
                selection_note = f"\n🔍 **Ricerca semantica:** dai {total_available} documenti, estraggo i {docs_retrieved} più simili alla tua richiesta"
            else:
                selection_note = f"\n🔍 **Ricerca semantica:** analizzo tutti i {docs_retrieved} documenti disponibili"
            
            if docs_retrieved > docs_to_process:
                processing_note = f"\n📋 **Analisi approfondita:** elaboro i {docs_to_process} più rilevanti per garantire qualità"
            else:
                processing_note = f"\n📋 **Analisi approfondita:** elaboro tutti i {docs_to_process} documenti trovati"
            
            docs_info = docs_info + selection_note + processing_note
            docs_note = ""
        else:
            docs_info = f"📋 **Documenti da analizzare:** {docs_to_process}"
            docs_note = ""
        
        messages = [
            f"🔍 **Analisi approfondita in corso**\n\n{docs_info}\n\n📊 **Processamento intelligente:**\n- Estrazione dati da {num_batches} gruppi di documenti\n- Analisi semantica e correlazioni\n- Aggregazione e sintesi finale\n\n⏱️ **Tempo stimato:** {time_str}{docs_note}\n\n✅ Il risultato sarà basato su dati reali e verificati!",
            
            f"🚀 **Elaborazione profonda attivata**\n\n{docs_info}\n\n🔬 **Processo in corso:**\n• {num_batches} cicli di estrazione dati\n• Verifica coerenza e completezza\n• Sintesi intelligente per {task_type}\n\n⌛ Richiederà {time_str}{docs_note}",
            
            f"📚 **Analisi documentale completa**\n\n{docs_info}\n\n✨ **Cosa sto facendo:**\n- Lettura ed estrazione da {num_batches} batch documentali\n- Correlazione informazioni cross-documento\n- Creazione {task_type}\n\n🕐 Tempo previsto: {time_str}{docs_note}"
        ]
        
        # Ruota tra i 3 messaggi in base al numero di documenti
        message_index = (docs_to_process // 10) % len(messages)
        return messages[message_index]
    
    async def _generative_synthesis_with_context(
        self,
        question: str,
        evidences: List[Dict],
        tenant_id: str,
        messages: Optional[List] = None
    ) -> Dict:
        """
        Sintesi generativa usando documenti tenant come contesto
        
        Modalità GENERATIVE: usa i documenti recuperati come contesto per sintesi AI
        senza applicare verifiche rigorose URS. Ideale per query creative/generative
        che richiedono elaborazione dei dati tenant.
        
        Args:
            question: Domanda dell'utente
            evidences: Lista di evidenze recuperate dal tenant
            tenant_id: ID del tenant
            messages: Cronologia completa dei messaggi (opzionale)
            
        Returns:
            Dict con risposta generata dall'AI con contesto tenant
        """
        try:
            logger.info(f"Sintesi generativa con {len(evidences)} documenti tenant come contesto")
            
            # APPROCCIO MULTI-STEP per processare tutti i documenti
            # Se abbiamo molti documenti (>10), usiamo strategia batch + aggregazione
            if len(evidences) > 10:
                logger.info(f"Modalità MULTI-STEP attivata: {len(evidences)} documenti, processamento in batch")
                return await self._multi_step_synthesis(
                    question=question,
                    evidences=evidences,
                    tenant_id=tenant_id,
                    messages=messages
                )
            
            # APPROCCIO STANDARD per ≤10 documenti
            # Estrai contenuti e metadati dalle evidenze
            context_docs = []
            sources = []
            for i, ev in enumerate(evidences[:10], 1):  # Limita a 10 documenti per context window
                content = ev.get("content", {})
                metadata = ev.get("metadata", {})
                
                # Handle both dict and string content
                if isinstance(content, dict):
                    # Try multiple field names: text, full_text, raw_text
                    text = content.get("text") or content.get("full_text") or content.get("raw_text") or ""
                elif isinstance(content, str):
                    text = content
                else:
                    text = str(content) if content else ""
                
                if text:
                    # Usa citazione precisa invece di generico [DOCUMENTO X]
                    citation = self._build_document_citation(ev, i)
                    context_docs.append(f"{citation}\n{text}\n")
                    
                    # Estrai informazioni documento
                    doc_id = ev.get("document_id", "unknown")
                    doc_title = ev.get("title", "Documento senza titolo")
                    protocol = ev.get("protocol_number", "")
                    
                    # Date può essere in metadata o top-level
                    date = ev.get("protocol_date", "")
                    if not date and isinstance(metadata, dict):
                        date = metadata.get("data_atto", "")
                    
                    doc_url = f"/natan/documents/view/{doc_id}" if doc_id != "unknown" else ""
                    
                    source_info = {
                        "document_id": doc_id,
                        "title": doc_title,
                        "url": doc_url,
                        "protocol_number": protocol,
                        "date": date,
                        "citation": citation
                    }
                    sources.append(source_info)
            
            # Costruisci contesto
            context = "\n\n".join(context_docs)
            
            # Estrai metadata per header metodologico
            doc_types = self._extract_doc_types_from_evidences(evidences)
            date_range = self._extract_date_range_from_evidences(evidences)
            asset_categories = self._extract_asset_categories_from_evidences(evidences)
            
            # Recupera totale documenti
            from app.services.mongodb_service import MongoDBService
            try:
                total_available = MongoDBService.count_documents(
                    "documents", 
                    {"tenant_id": int(tenant_id), "embedding": {"$exists": True}}
                )
            except:
                total_available = len(evidences) * 10
            
            # Costruisci header metodologico CON categorie patrimonio coperte/mancanti
            methodology_header = self._build_methodology_header(
                num_docs_analyzed=len(evidences),
                total_docs_available=total_available,
                date_range=date_range,
                doc_types=doc_types,
                asset_categories_found=asset_categories
            )
            
            # Usa AI Router per generazione con contesto
            adapter_context = {
                "tenant_id": int(tenant_id),
                "persona": "strategic",
                "task_class": "generative_with_context"
            }
            
            adapter = self.ai_router.get_chat_adapter(adapter_context)
            
            # Costruisci cronologia conversazione se presente
            conversation_history = ""
            if messages and len(messages) > 1:
                for msg in messages[:-1]:
                    if hasattr(msg, 'role'):
                        role = msg.role
                        content = msg.content
                    else:
                        role = msg.get("role", "user")
                        content = msg.get("content", "")
                    
                    if role == "user":
                        conversation_history += f"Utente: {content}\n"
                    elif role == "assistant":
                        conversation_history += f"Assistente: {content}\n"
                
                if conversation_history:
                    conversation_history = f"\n\n**CRONOLOGIA CONVERSAZIONE:**\n{conversation_history}\n"
            
            # Prompt per sintesi generativa con HEADER METODOLOGICO e SISTEMA SEMAFORO
            num_docs = len(evidences)
            prompt_messages = [
                {
                    "role": "system",
                    "content": f"""Sei un assistente esperto per la Pubblica Amministrazione italiana.

Hai a disposizione {num_docs} documenti su {total_available} totali disponibili nel database.

=== STRUTTURA OBBLIGATORIA DELLA RISPOSTA ===

La risposta DEVE seguire questa struttura ESATTA:

**SEZIONE 1 - HEADER METODOLOGICO** (OBBLIGATORIO, inseriscilo all'inizio):
{methodology_header}

**SEZIONE 2 - LEGENDA SEMAFORO** (subito dopo l'header):
---
**📊 LEGENDA AFFIDABILITÀ DATI**

🟢 **VERIFICATO** = Dato presente nei documenti ufficiali
🟠 **STIMATO** = Elaborazione/stima basata su dati parziali  
🔴 **PROPOSTA AI** = Suggerimento del sistema, NON nei documenti
---

**SEZIONE 3 - PARTE A: STATO ATTUALE (solo fatti 🟢/🟠)**
Qui descrivi SOLO ciò che emerge dai documenti. NO proposte.

**SEZIONE 4 - PARTE B: PROPOSTE E RACCOMANDAZIONI (solo 🔴)**
Qui metti le tue proposte, CHIARAMENTE SEPARATE dai fatti.

=== TONO OBBLIGATORIO PER PROPOSTE 🔴 ===

Le proposte AI devono usare SEMPRE linguaggio CONDIZIONALE e PRUDENTE:

VERBI DA USARE:
- ✅ "Si potrebbe valutare...", "Sarebbe opportuno considerare..."
- ✅ "Una possibile soluzione potrebbe essere...", "Si suggerisce di..."
- ❌ MAI: "Bisogna fare...", "È necessario...", "Occorre implementare..."

NUMERI DA USARE:
- ✅ "Budget indicativo: €150.000-250.000 (da validare con uffici tecnici)"
- ✅ "Stima preliminare soggetta a verifica: €X-Y"
- ❌ MAI numeri puntuali senza range

DISCLAIMER DA AGGIUNGERE:
- Alla fine di ogni proposta significativa, aggiungi: "(proposta da validare)"
- Per KPI: "Target indicativo, da calibrare su dati storici reali"

=== REGOLE CITAZIONI ===

Usa citazioni PRECISE con protocollo, non generiche:
- ✅ CORRETTO: "€500.000 [Prot. 00457 - Accordo Quadro Manutenzione]"  
- ❌ SBAGLIATO: "€500.000 [DOCUMENTO 1]"

=== REGOLE NUMERI 🔴 ===

MAI numeri puntuali per proposte AI. USA SEMPRE RANGE:
- ✅ CORRETTO: "🔴 €150.000-250.000 (stima indicativa, da validare)"
- ❌ SBAGLIATO: "🔴 €200.000"

CONTESTO CONVERSAZIONALE:{conversation_history}

NON includere sezione fonti - verrà aggiunta automaticamente."""
                },
                {
                    "role": "user",
                    "content": f"""Documenti disponibili dal database:

{context}
---

Domanda dell'utente:
{question}

CHECKLIST FINALE:
☐ Header metodologico inserito all'inizio
☐ Legenda semaforo presente
☐ PARTE A (fatti) separata da PARTE B (proposte)
☐ Citazioni precise con protocollo
☐ Numeri 🔴 come RANGE, non puntuali

Elabora la risposta seguendo la struttura obbligatoria."""
                }
            ]
            
            result = await adapter.generate(
                prompt_messages,
                temperature=0.7,  # Temperatura per creatività bilanciata
                max_tokens=3000  # Token per risposte complesse
            )
            
            answer = result["content"].strip()
            
            logger.info(f"Risposta generativa creata: {len(answer)} caratteri usando {len(sources)} documenti")
            
            # POST-PROCESSING: Aggiungi link cliccabili ai riferimenti documenti
            answer_with_links = self._add_clickable_links_to_answer(answer, sources)
            
            return {
                "answer": answer_with_links,
                "urs_score": None,  # No URS score per modalità generativa
                "urs_explanation": f"Modalità GENERATIVA: sintesi creativa basata su {len(sources)} documenti del tenant. Non applicate verifiche URS rigorose.",
                "claims_used": [],  # No claims extraction in modalità generativa
                "sources": sources,
                "hallucinations_found": [],
                "gaps_detected": []
            }
        
        except APIError as api_err:
            # Errore API con messaggio user-friendly
            logger.error(f"Errore API durante sintesi generativa: {api_err.error_type.value} - {api_err.message}")
            return {
                "answer": api_err.user_message,
                "urs_score": 0.0,
                "urs_explanation": f"Errore servizio AI: {api_err.error_type.value}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": [],
                "error_type": api_err.error_type.value,
                "error_provider": api_err.provider
            }
            
        except Exception as e:
            logger.error(f"Errore durante sintesi generativa con contesto: {e}", exc_info=True)
            return {
                "answer": "Si è verificato un errore durante l'elaborazione della richiesta. Si prega di riprovare.",
                "urs_score": 0.0,
                "urs_explanation": f"Errore tecnico: {str(e)}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }
    
    def _add_clickable_links_to_answer(self, answer: str, sources: List[Dict]) -> str:
        """
        Post-processing della risposta per aggiungere link cliccabili
        
        1. Sostituisce riferimenti inline [DOCUMENTO X] con link Markdown
        2. Rimuove qualsiasi sezione FONTI generata dall'AI
        3. Aggiunge automaticamente sezione FONTI corretta con link puliti
        
        Args:
            answer: Risposta generata dall'AI
            sources: Lista di sources con URL
            
        Returns:
            Answer con link cliccabili
        """
        import re
        
        # Crea mapping documento -> URL
        doc_mapping = {}
        for i, src in enumerate(sources, 1):
            doc_mapping[i] = {
                'url': src.get('url', ''),
                'title': src.get('title', f'Documento {i}'),
                'protocol': src.get('protocol_number', ''),
                'date': src.get('date', '')
            }
        
        # STEP 1: Sostituisci riferimenti inline [DOCUMENTO X] o [DOC X] con link HTML stilizzati
        # Pattern: [DOCUMENTO 13], [DOC 13], o [DOCUMENTI 1,3]
        def replace_inline_doc(match):
            full_match = match.group(0)  # es: "[DOCUMENTO 13]" o "[DOC 13]"
            doc_part = match.group(1)    # es: "DOCUMENTO 13" o "DOC 13"
            
            # Estrai numeri dal riferimento
            numbers = re.findall(r'\d+', doc_part)
            
            if not numbers:
                return full_match  # No numbers found, return as is
            
            # Crea link HTML per ogni numero (invece di Markdown)
            links = []
            for num_str in numbers:
                num = int(num_str)
                
                # Cerca per doc_number (multi-step) o per posizione (standard)
                source_info = None
                for src in sources:
                    if src.get('doc_number') == num or sources.index(src) + 1 == num:
                        source_info = src
                        break
                
                if source_info and source_info.get('url'):
                    url = source_info['url']
                    # Link HTML con stile inline: blu scuro, grassetto, sottolineato
                    # Mantieni formato originale (DOCUMENTO o DOC)
                    display_text = f"[DOC {num}]" if "DOC" in full_match and "DOCUMENTO" not in full_match else f"[DOCUMENTO {num}]"
                    links.append(f'<a href="{url}" style="color: #1B365D; text-decoration: underline; font-weight: 600;" target="_blank" rel="noopener">{display_text}</a>')
                else:
                    # Fallback senza link
                    display_text = f"DOC {num}" if "DOC" in full_match and "DOCUMENTO" not in full_match else f"DOCUMENTO {num}"
                    links.append(display_text)
            
            # Se multipli documenti: [DOCUMENTI 1,3] -> [DOCUMENTO 1], [DOCUMENTO 3]
            return ", ".join(links)
        
        # Applica sostituzione per entrambi i formati: [DOCUMENTO X] e [DOC X]
        answer = re.sub(r'\[(DOC(?:UMENT[OI])?\s+\d+(?:,\s*\d+)*)\]', replace_inline_doc, answer, flags=re.IGNORECASE)
        
        # STEP 2: Rimuovi qualsiasi sezione FONTI generata dall'AI
        fonti_section_start = answer.find("## FONTI CONSULTATE")
        if fonti_section_start != -1:
            # Taglia tutto dalla sezione FONTI in poi
            answer = answer[:fonti_section_start].rstrip()
        
        # STEP 3: Genera automaticamente sezione FONTI pulita con HTML diretto
        # Uso HTML invece di Markdown per controllo totale su stile e spaziatura
        fonti_section = "\n\n## FONTI CONSULTATE\n\n"
        fonti_section += '<div class="sources-section" style="margin-top: 1.5rem;">\n'
        fonti_section += '<ul style="list-style-type: disc; padding-left: 1.5rem; margin: 0;">\n'
        
        for i, src in enumerate(sources, 1):
            url = src.get('url', '')
            title = src.get('title', f'Documento {i}')
            protocol = src.get('protocol_number', '')
            
            # Ogni fonte ha margine bottom per spaziatura
            fonti_section += '<li style="margin-bottom: 0.75rem; line-height: 1.6;">\n'
            
            if url:  # Solo se l'URL è disponibile
                # Link blu e sottolineato, più evidente
                fonti_section += f'<a href="{url}" style="color: #1B365D; text-decoration: underline; font-weight: 600;" target="_blank" rel="noopener">{title}</a>'
                if protocol:
                    fonti_section += f' <span style="color: #6B7280; font-size: 0.875rem;">(Protocollo: {protocol})</span>'
            else:
                # Se non c'è URL, mostra solo il titolo
                fonti_section += f'<span style="font-weight: 600;">{title}</span>'
                if protocol:
                    fonti_section += f' <span style="color: #6B7280; font-size: 0.875rem;">(Protocollo: {protocol})</span>'
            
            fonti_section += '\n</li>\n'
        
        fonti_section += '</ul>\n</div>\n'
        
        answer += fonti_section
        
        return answer
    
    async def _fallback_to_ai_generation(self, question: str, tenant_id: str) -> Dict:
        """
        Fallback a generazione AI pura per query generative/creative senza documenti
        
        Args:
            question: Domanda dell'utente
            tenant_id: ID del tenant
            
        Returns:
            Dict con risposta generata dall'AI
        """
        try:
            logger.info("Fallback a generazione AI pura per query generativa")
            
            # Usa AI Router per generazione creativa
            context = {
                "tenant_id": int(tenant_id),
                "persona": "strategic",
                "task_class": "generation"
            }
            
            adapter = self.ai_router.get_chat_adapter(context)
            
            # Prompt per generazione creativa
            messages = [
                {
                    "role": "system",
                    "content": """Sei un assistente esperto per la Pubblica Amministrazione italiana.
La tua missione è aiutare a creare strumenti di analisi, matrici decisionali, strategie e documenti operativi.

Quando ti viene chiesto di creare qualcosa (matrici, tabelle, analisi, strategie), genera contenuti:
- Pratici e operativi
- Basati su best practices della PA italiana
- Formali ma comprensibili
- Strutturati e ben organizzati
- Pronti all'uso

Rispondi sempre in italiano formale."""
                },
                {
                    "role": "user",
                    "content": question
                }
            ]
            
            result = await adapter.generate(
                messages,
                temperature=0.7,  # Temperatura più alta per creatività
                max_tokens=2000  # Più token per risposte complesse
            )
            
            answer = result["content"].strip()
            
            logger.info(f"Risposta generata dall'AI: {len(answer)} caratteri")
            
            return {
                "answer": answer,
                "urs_score": 75.0,  # Score medio per generazione senza documenti
                "urs_explanation": "Risposta generata dall'AI per query creativa/generativa. Non basata su documenti verificati ma su conoscenza generale e best practices.",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }
        
        except APIError as api_err:
            # Errore API con messaggio user-friendly
            logger.error(f"Errore API durante fallback AI generation: {api_err.error_type.value} - {api_err.message}")
            return {
                "answer": api_err.user_message,
                "urs_score": 0.0,
                "urs_explanation": f"Errore servizio AI: {api_err.error_type.value}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": [],
                "error_type": api_err.error_type.value,
                "error_provider": api_err.provider
            }
            
        except Exception as e:
            logger.error(f"Errore durante fallback AI generation: {e}", exc_info=True)
            return {
                "answer": "Si è verificato un errore durante la generazione della risposta. Si prega di riprovare.",
                "urs_score": 0.0,
                "urs_explanation": f"Errore tecnico: {str(e)}",
                "claims_used": [],
                "sources": [],
                "hallucinations_found": [],
                "gaps_detected": []
            }

# Istanza globale della pipeline
_pipeline_instance = None

async def rag_fortress(
    question: str,
    tenant_id: str,
    user_id: Optional[str] = None,
    mode: str = "strict",
    messages: Optional[List] = None
) -> Dict:
    """
    Funzione principale per chiamare la pipeline RAG-Fortress
    
    Args:
        question: Domanda dell'utente (ultimo messaggio)
        tenant_id: ID del tenant
        user_id: ID dell'utente (opzionale)
        mode: Modalità pipeline ("strict" o "generative")
            - "strict": Verifica rigorosa con URS, rifiuta se URS < 90
            - "generative": Sintesi generativa con contesto tenant, no verifica URS rigorosa
        messages: Cronologia completa dei messaggi della conversazione (opzionale)
        
    Returns:
        Dict con risposta completa e metadata
    """
    global _pipeline_instance
    if _pipeline_instance is None:
        _pipeline_instance = RAGFortressPipeline()
    
    return await _pipeline_instance.rag_fortress(question, tenant_id, user_id, mode, messages)
