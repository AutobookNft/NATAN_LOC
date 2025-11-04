"""
Question Classifier - Classifica le domande per intent detection
Usa AI leggero per determinare il tipo di query
"""

from typing import Dict, Any, Optional
from enum import Enum
import json
from app.services.ai_router import AIRouter


class QueryIntent(str, Enum):
    """Query intent types"""
    FACT_CHECK = "fact_check"           # Verifica fatti numerici/data
    NUMERICAL = "numerical"             # Query numerica specifica
    INTERPRETATION = "interpretation"   # Interpretazione/analisi
    COMPARISON = "comparison"           # Confronto tra entità
    DEFINITION = "definition"           # Definizione/concetto
    PROCEDURE = "procedure"             # Procedura/processo
    TEMPORAL = "temporal"               # Query temporale
    SPATIAL = "spatial"                 # Query geografica/spaziale
    CONVERSATIONAL = "conversational"   # Domande conversazionali/saluti
    BLOCKED = "blocked"                 # Query non verificabile/bloccata


class QuestionClassifier:
    """
    Classifica domande per determinare intent e confidenza
    
    Usa keyword-based classification come metodo principale (veloce, economico).
    Per domande complesse, usa classify_with_llm() con LLM leggero (gpt-4o-mini).
    """
    
    # Keywords patterns per classification rapida (fallback)
    KEYWORD_PATTERNS = {
        QueryIntent.CONVERSATIONAL: [
            # Saluti e cortesie
            "ci sei", "ciao", "salve", "buongiorno", "buonasera", "buonanotte",
            "grazie", "prego", "ok", "va bene", "perfetto", "okay",
            "come stai", "come va", "tutto bene", "aiuto", "help",
            # Domande personali/relazionali (non documentali)
            "sai", "puoi", "vuoi", "fai", "conosci", "ti piace", "ti piacciono",
            "sai fare", "sai ballare", "sai cantare", "sai suonare", "sai guidare",
            "puoi fare", "puoi ballare", "puoi cantare", "puoi suonare",
            "ti va", "vorresti", "potresti", "saresti",
            # Domande su capacità/conoscenze personali
            "che fai", "cosa fai", "dove sei", "chi sei", "cosa sei",
            "cosa ti piace", "cosa non ti piace", "preferisci",
            # Domande generiche non documentali
            "che ne pensi", "cosa ne pensi", "qual è la tua opinione",
            "scusa", "scusami", "perdono", "mi dispiace",
            # Frasi comuni conversazionali
            "dimmi", "raccontami", "parlami", "dimmi tu",
            "non so", "non capisco", "spiegami", "cosa significa per te",
            # Metafore e frasi idiomatiche
            "ago in un pagliaio", "ago nel pagliaio", "ago in pagliaio",
            "cercare un ago", "troppo difficile", "impossibile trovare",
            "come cercare", "è come", "sembra impossibile"
        ],
        QueryIntent.FACT_CHECK: [
            "quando", "dove", "chi", "cosa", "quale", "quanti", "quante",
            "importo", "costo", "prezzo", "valore", "numero"
        ],
        QueryIntent.NUMERICAL: [
            "quanto", "quantità", "somma", "totale", "percentuale",
            "€", "euro", "dollari"
        ],
        QueryIntent.COMPARISON: [
            "confronta", "differenza", "vs", "rispetto", "meglio", "peggio"
        ],
        QueryIntent.DEFINITION: [
            "cos'è", "cosa significa", "definizione", "significato"
        ],
        QueryIntent.PROCEDURE: [
            "come", "procedura", "processo", "step", "fasi"
        ],
        QueryIntent.TEMPORAL: [
            "quando", "data", "periodo", "dal", "al", "anno", "mese"
        ],
        QueryIntent.INTERPRETATION: [
            "perché", "motivo", "ragione", "opinione", "ritieni", "pensi"
        ]
    }
    
    @staticmethod
    def classify(question: str, tenant_id: int, model: str = "light") -> Dict[str, Any]:
        """
        Classifica una domanda
        
        Args:
            question: Testo della domanda
            tenant_id: ID tenant per context
            model: Model da usare ("light" o "llm")
        
        Returns:
            Dict con:
                - intent: QueryIntent
                - confidence: float (0.0 - 1.0)
                - constraints: Dict[str, Any] (filters, date ranges, etc.)
        """
        question_lower = question.lower().strip()
        
        # TODO: Implementare con AI leggero (es. fine-tuned model)
        # Per ora: keyword-based classification
        
        intent = QueryIntent.FACT_CHECK
        confidence = 0.6  # Default confidence per keyword-based
        constraints = {}
        
        # Check conversational first (higher priority)
        # Use more sophisticated matching for conversational queries
        
        # IMPORTANT: Check if this is a document request FIRST (even if contains conversational verbs)
        # Document request keywords that override conversational classification
        document_request_keywords = [
            "tokenomics", "documento", "documenti", "dati", "dato", "informazioni",
            "estrai", "estrae", "estraggo", "estragga",
            "cerca", "cercare", "trova", "trovare", "mostra", "mostrami", "mostrare",
            "forniscimi", "fornisci", "fornire",
            "analizza", "analizzare", "analisi",
            "delibera", "delibere", "atto", "atti", "procedura", "procedure",
            "legge", "leggi", "regolamento", "regolamenti", "circolare", "circolari",
            "bando", "bandi", "gara", "gare", "appalto", "appalti",
            "protocollo", "numero", "data", "importo", "costo", "prezzo"
        ]
        
        has_document_request = any(doc_kw in question_lower for doc_kw in document_request_keywords)
        
        # Conversational keywords
        conv_keywords = QuestionClassifier.KEYWORD_PATTERNS.get(QueryIntent.CONVERSATIONAL, [])
        
        # Check for exact keyword matches, BUT skip if it's a document request
        if not has_document_request and any(keyword in question_lower for keyword in conv_keywords):
            intent = QueryIntent.CONVERSATIONAL
            confidence = 0.9
        # Check for metafore/frasi idiomatiche comuni
        elif any(idiom in question_lower for idiom in [
            "ago in un pagliaio", "ago nel pagliaio", "ago in pagliaio",
            "è come cercare", "sembra impossibile", "troppo difficile",
            "missione impossibile", "introvabile", "cercare l'introvabile",
            "rintracciare l'impossibile", "metaforfosi", "clorofilliana"  # Termini inventati/nonsensici
        ]):
            # Metafore sono sempre conversazionali
            intent = QueryIntent.CONVERSATIONAL
            confidence = 0.95
        # Check for conversational patterns (sai + verb, puoi + verb, etc.)
        elif not has_document_request and any(pattern in question_lower for pattern in [
            "sai ", "puoi ", "vuoi ", "fai ", "conosci ", "ti piace",
            "che fai", "cosa fai", "dimmi", "raccontami", "parlami",
            "cosa potresti", "cosa faresti", "cosa faresti se"
        ]):
            # Additional check: if it's a simple personal question (not document-related)
            # These are likely conversational ONLY if NOT a document request
            # Special case: if contains "documento" BUT also impossible/invented terms, it's conversational
            impossible_terms = ["metaforfosi", "clorofilliana", "introvabile", "impossibile", 
                               "fantastico", "magico", "impossibile", "nonsensico"]
            has_impossible = any(term in question_lower for term in impossible_terms)
            
            if has_impossible:
                # Contains impossible terms → conversational (probabilmente domanda inventata/nonsensica)
                intent = QueryIntent.CONVERSATIONAL
                confidence = 0.90
            else:
                # Simple conversational pattern without document keywords
                intent = QueryIntent.CONVERSATIONAL
                confidence = 0.85
        else:
            # If it's a document request, prioritize fact_check or interpretation
            if has_document_request:
                # Document requests should use RAG, not conversational
                if any(kw in question_lower for kw in ["quando", "dove", "chi", "cosa", "quale", "quanti", "quante"]):
                    intent = QueryIntent.FACT_CHECK
                    confidence = 0.85
                elif any(kw in question_lower for kw in ["perché", "motivo", "ragione", "come", "procedura"]):
                    intent = QueryIntent.INTERPRETATION
                    confidence = 0.80
                else:
                    # Generic document request -> fact_check (will use RAG)
                    intent = QueryIntent.FACT_CHECK
                    confidence = 0.80
            else:
                # Keyword matching for other intents
                for intent_type, keywords in QuestionClassifier.KEYWORD_PATTERNS.items():
                    if intent_type == QueryIntent.CONVERSATIONAL:
                        continue  # Already checked
                    if any(keyword in question_lower for keyword in keywords):
                        intent = intent_type
                        confidence = 0.75
                        break
        
        # Block interpretation/intent troppo aperto
        # BUT allow interpretation if it's a document request
        if intent == QueryIntent.INTERPRETATION:
            # If it's a document request, interpretation is OK (will use RAG)
            if has_document_request:
                # Keep as interpretation, will be routed to RAG
                confidence = max(confidence, 0.75)
            elif any(word in question_lower for word in ["quale", "quali", "seleziona"]):
                # Convert to fact_check se ha constraint
                intent = QueryIntent.FACT_CHECK
                confidence = 0.7
            else:
                # Block open interpretation (only if NOT a document request)
                intent = QueryIntent.BLOCKED
                confidence = 0.9
        
        return {
            "intent": intent.value,
            "confidence": confidence,
            "constraints": constraints,
            "model": model,
            "question": question
        }
    
    @staticmethod
    async def classify_with_llm(question: str, tenant_id: int) -> Dict[str, Any]:
        """
        Classifica con LLM (per queries complesse)
        Usa un LLM leggero per classificare domande che non possono essere classificate
        con keyword matching semplice
        
        Args:
            question: Testo della domanda
            tenant_id: ID tenant
        
        Returns:
            Dict con intent, confidence, constraints, model="llm"
        """
        try:
            # Initialize AI router
            ai_router = AIRouter()
            
            # Build context for policy engine (use lightweight model for classification)
            # Use task_class "chat" with priority "speed" to get lightweight model
            context = {
                "tenant_id": tenant_id,
                "task_class": "chat",
                "priority": "speed"  # Prefer lightweight/fast model
            }
            
            # Get chat adapter (will select lightweight model via policy engine)
            adapter = ai_router.get_chat_adapter(context)
            
            # Fallback to OpenAI gpt-4o-mini if adapter doesn't support lightweight model
            # This ensures we always use a cheap model for classification
            try:
                # Try to get a lightweight model - check if it's OpenAI
                from app.services.providers import OpenAIChatAdapter
                if not isinstance(adapter, OpenAIChatAdapter):
                    # Use lightweight OpenAI model instead
                    adapter = OpenAIChatAdapter(model="gpt-4o-mini")
            except ImportError:
                pass  # Use whatever adapter we got
            
            # Build classification prompt (brief, specific)
            prompt = f"""You are a query classifier for a Public Administration document analysis system.

Classify this question into ONE of these intent types:
- fact_check: Questions about specific facts, numbers, dates, who/what/where/when
- numerical: Questions about quantities, amounts, percentages, sums
- comparison: Questions comparing entities or concepts
- definition: Questions asking for definitions or meanings
- procedure: Questions about how to do something, processes, steps
- temporal: Questions about time periods, dates, schedules
- spatial: Questions about locations, geography, places
- interpretation: Questions asking why, reasons, analysis, opinions (ONLY if asking about documents/data)
- conversational: Greetings, casual chat, personal questions (NOT about documents)
- blocked: Queries that cannot be verified or are inappropriate

Question: "{question}"

IMPORTANT RULES:
1. If the question asks about documents, data, or information retrieval → use fact_check, numerical, or interpretation
2. If it's a greeting or casual chat without document context → conversational
3. If it asks for general knowledge not in documents → blocked
4. If it's asking about "why" or "reason" regarding documents/data → interpretation
5. If it's a simple factual question (who/what/when/where) → fact_check

Return ONLY a JSON object with this structure:
{{
    "intent": "one_of_the_intents_above",
    "confidence": 0.0-1.0,
    "constraints": {{}}
}}

Example responses:
- "Quanti documenti ci sono?" → {{"intent": "numerical", "confidence": 0.9, "constraints": {{}}}}
- "Ciao, come stai?" → {{"intent": "conversational", "confidence": 0.95, "constraints": {{}}}}
- "Quando è stata approvata la delibera?" → {{"intent": "fact_check", "confidence": 0.9, "constraints": {{}}}}
- "Perché è stata presa questa decisione?" → {{"intent": "interpretation", "confidence": 0.85, "constraints": {{}}}}

Classify this question:"""

            # Prepare messages
            messages = [
                {
                    "role": "system",
                    "content": "You are a query classifier. Return ONLY valid JSON, no other text."
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            # Generate classification with low temperature (deterministic) and low max_tokens (brief response)
            result = await adapter.generate(
                messages,
                temperature=0.1,  # Low temperature for consistent classification
                max_tokens=200  # Brief response (just JSON)
            )
            
            response_text = result["content"].strip()
            
            # Parse JSON from response (handle markdown code blocks)
            try:
                # Try to extract JSON from markdown code block if present
                if "```json" in response_text:
                    json_start = response_text.find("```json") + 7
                    json_end = response_text.find("```", json_start)
                    json_text = response_text[json_start:json_end].strip()
                elif "```" in response_text:
                    json_start = response_text.find("```") + 3
                    json_end = response_text.find("```", json_start)
                    json_text = response_text[json_start:json_end].strip()
                else:
                    json_text = response_text.strip()
                
                # Try to find JSON object in text
                obj_start = json_text.find("{")
                obj_end = json_text.rfind("}") + 1
                if obj_start >= 0 and obj_end > obj_start:
                    json_text = json_text[obj_start:obj_end]
                
                # Parse JSON
                classification = json.loads(json_text)
                
                # Validate intent
                intent_value = classification.get("intent", "fact_check")
                try:
                    # Validate intent exists in enum
                    QueryIntent(intent_value)
                except ValueError:
                    # Invalid intent, fallback to fact_check
                    intent_value = "fact_check"
                
                # Ensure confidence is valid
                confidence = float(classification.get("confidence", 0.7))
                confidence = max(0.0, min(1.0, confidence))  # Clamp between 0 and 1
                
                # Get constraints
                constraints = classification.get("constraints", {})
                
                return {
                    "intent": intent_value,
                    "confidence": confidence,
                    "constraints": constraints,
                    "model": "llm",
                    "question": question
                }
            
            except (json.JSONDecodeError, KeyError, ValueError) as e:
                # If JSON parsing fails, fallback to keyword-based classification
                return QuestionClassifier.classify(question, tenant_id, model="llm_fallback")
        
        except Exception as e:
            # If LLM call fails, fallback to keyword-based classification
            return QuestionClassifier.classify(question, tenant_id, model="llm_error")





