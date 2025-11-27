"""Chat router - LLM inference con RAG-Fortress Zero-Hallucination"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from app.services.ai_router import AIRouter
from app.services.rag_fortress.pipeline import rag_fortress
from app.services.question_classifier import QuestionClassifier
from app.services.execution_router import ExecutionRouter, RouterAction
import time
import logging

logger = logging.getLogger(__name__)

router = APIRouter()
ai_router = AIRouter()
question_classifier = QuestionClassifier()
execution_router = ExecutionRouter()

class Message(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: List[Message]
    tenant_id: int
    user_id: Optional[int] = None  # ID utente per memorie personalizzate
    persona: str = "strategic"
    model: Optional[str] = None  # Optional, will use policy engine if None
    task_class: str = "chat"  # For policy selection
    temperature: Optional[float] = None  # Optional override
    max_tokens: Optional[int] = None  # Optional override
    use_rag_fortress: bool = True  # Usa RAG-Fortress per default

class ChatResponse(BaseModel):
    message: str
    model: str
    usage: dict
    citations: List[str] = []
    urs_score: Optional[float] = None
    urs_explanation: Optional[str] = None
    claims: List[str] = []
    sources: List[str] = []
    hallucinations_found: List[str] = []
    gaps_detected: List[str] = []
    processing_notice: Optional[str] = None  # Messaggio di progresso per operazioni lunghe

class ProcessingEstimate(BaseModel):
    """Stima della complessità del processamento per mostrare notice anticipato"""
    will_take_time: bool
    estimated_seconds: int
    processing_notice: Optional[str] = None
    query_type: str
    num_documents_estimated: int

@router.post("/chat/estimate", response_model=ProcessingEstimate)
async def estimate_processing(request: ChatRequest):
    """
    Endpoint veloce per stimare complessità query e restituire processing notice
    Il frontend può chiamare questo PRIMA di /chat per mostrare subito il messaggio
    
    Returns:
        ProcessingEstimate con flag will_take_time e messaggio
    """
    try:
        # Estrai ultima domanda
        user_messages = [msg for msg in request.messages if msg.role == "user"]
        if not user_messages:
            return ProcessingEstimate(
                will_take_time=False,
                estimated_seconds=5,
                processing_notice=None,
                query_type="simple",
                num_documents_estimated=0
            )
        
        question = user_messages[-1].content
        
        # Classifica query
        classification = QuestionClassifier.classify(question, request.tenant_id)
        intent = classification.get("intent")
        
        # Routing decision
        routing = execution_router.route(
            intent=intent,
            confidence=classification.get("confidence", 0.0),
            question=question,
            tenant_id=request.tenant_id,
            constraints=classification.get("constraints", {})
        )
        
        action = routing["action"]
        
        # Stima documenti recuperati (query veloce al database)
        from app.services.rag_fortress.pipeline import RAGFortressPipeline
        from app.services.mongodb_service import MongoDBService
        
        pipeline = RAGFortressPipeline()
        
        is_generative = pipeline._is_generative_query(question)
        
        # Stima documenti: conta reale dal database per il tenant
        if is_generative or action == RouterAction.RAG_GENERATIVE.value:
            # Query veloce per contare documenti disponibili per questo tenant
            try:
                total_available = MongoDBService.count_documents(
                    "documents", 
                    {"tenant_id": request.tenant_id, "embedding": {"$exists": True}}
                )
            except Exception:
                total_available = 100  # Fallback se conteggio fallisce
            
            # Flusso REALE del retrieval:
            # 1. Vector Search estrae top_k=100 più simili alla query
            # 2. Filtro rilevanza (score >= 0.5)
            # 3. Limit a 50 documenti (evidences[:50])
            # Quindi: da total_available -> estrai 100 -> processa max 50
            retrieval_top_k = 100  # Documenti estratti da vector search
            max_processing = 50    # Limite massimo per processing
            
            # Se abbiamo meno documenti del top_k, estraiamo tutti quelli disponibili
            docs_retrieved = min(total_available, retrieval_top_k)
            docs_to_process = min(docs_retrieved, max_processing)
            
            # Calcola tempo in base ai documenti da processare
            num_batches = (docs_to_process + 9) // 10
            estimated_seconds = (num_batches * 15) + 20  # ~15 sec per batch + 20 sec aggregazione
            query_type = "generative_deep"
            
            # Genera processing notice con numeri REALI del flusso
            processing_notice = pipeline._generate_processing_notice(
                total_available=total_available,
                docs_retrieved=docs_retrieved,
                docs_to_process=docs_to_process, 
                num_batches=num_batches, 
                question=question
            )
            
            return ProcessingEstimate(
                will_take_time=True,
                estimated_seconds=estimated_seconds,
                processing_notice=processing_notice,
                query_type=query_type,
                num_documents_estimated=docs_to_process
            )
        elif action == RouterAction.RAG_STRICT.value:
            estimated_docs = 20
            estimated_seconds = 15
            query_type = "rag_strict"
            
            return ProcessingEstimate(
                will_take_time=estimated_seconds > 10,
                estimated_seconds=estimated_seconds,
                processing_notice="🔍 Ricerca in corso tra i documenti verificati..." if estimated_seconds > 10 else None,
                query_type=query_type,
                num_documents_estimated=estimated_docs
            )
        else:
            # Direct query - veloce
            return ProcessingEstimate(
                will_take_time=False,
                estimated_seconds=3,
                processing_notice=None,
                query_type="conversational",
                num_documents_estimated=0
            )
            
    except Exception as e:
        logger.error(f"Errore durante stima processing: {e}", exc_info=True)
        return ProcessingEstimate(
            will_take_time=False,
            estimated_seconds=5,
            processing_notice=None,
            query_type="unknown",
            num_documents_estimated=0
        )

@router.post("/chat", response_model=ChatResponse)
async def chat_inference(request: ChatRequest):
    """
    LLM inference con Question Classifier + Routing intelligente
    
    Flow:
    1. Classifica la query (fattuale PA vs generativa vs conversazionale)
    2. Route appropriato:
       - Query fattuali PA → RAG-Fortress (retrieval + verifica)
       - Query generative/creative → Direct AI (no RAG)
       - Query conversazionali → Direct AI (semplice)
    """
    try:
        # Estrai ultimo messaggio utente (domanda)
        user_messages = [msg for msg in request.messages if msg.role == "user"]
        if not user_messages:
            raise HTTPException(status_code=400, detail="Nessun messaggio utente trovato")
        
        question = user_messages[-1].content
        
        # STEP 1: Classifica la query
        classification = question_classifier.classify(
            question=question,
            tenant_id=request.tenant_id,
            model="light"
        )
        
        intent = classification["intent"]
        confidence = classification["confidence"]
        
        logger.info(f"🔍 CLASSIFICATION - Query: '{question[:50]}...' → Intent: {intent}, Confidence: {confidence}")
        
        # STEP 2: Route la query
        routing = execution_router.route(
            intent=intent,
            confidence=confidence,
            question=question,
            tenant_id=request.tenant_id,
            constraints=classification.get("constraints", {})
        )
        
        action = routing["action"]
        logger.info(f"🔍 ROUTING - Intent: {intent} → Action: {action}")
        
        # STEP 3: Esegui azione appropriata
        start_time = time.time()
        
        if action == RouterAction.RAG_STRICT.value:
            # Query fattuale PA → RAG-Fortress (retrieval + verifica rigorosa)
            # Converti Message objects in dict
            messages_dict = [{"role": msg.role, "content": msg.content} for msg in request.messages]
            result = await rag_fortress(
                question=question,
                messages=messages_dict,  # Passa cronologia conversazione come dict
                tenant_id=str(request.tenant_id),
                user_id=request.user_id  # Passa user_id per memorie personalizzate
            )
            elapsed_ms = int((time.time() - start_time) * 1000)
            
            return ChatResponse(
                message=result.get("answer", ""),
                model="rag-fortress-pipeline",
                usage={
                    "prompt_tokens": 0,  # TODO: calcola da pipeline
                    "completion_tokens": 0,
                    "total_tokens": 0,
                    "elapsed_ms": elapsed_ms
                },
                citations=result.get("claims_used", []),
                urs_score=result.get("urs_score", 0.0),
                urs_explanation=result.get("urs_explanation", ""),
                claims=result.get("claims_used", []),
                sources=result.get("sources", []),
                hallucinations_found=result.get("hallucinations_found", []),
                gaps_detected=result.get("gaps_detected", [])
            )
        
        elif action == RouterAction.RAG_GENERATIVE.value:
            # Query generativa/creative → RAG-Fortress con modalità GENERATIVA
            # Usa retrieval per contesto tenant + AI generativo (NO verifica URS rigorosa)
            # Converti Message objects in dict
            messages_dict = [{"role": msg.role, "content": msg.content} for msg in request.messages]
            result = await rag_fortress(
                question=question,
                messages=messages_dict,  # Passa cronologia conversazione come dict
                tenant_id=str(request.tenant_id),
                user_id=request.user_id,  # Passa user_id per memorie personalizzate
                mode="generative"  # Modalità generativa: retrieval SI, verifica URS rigorosa NO
            )
            elapsed_ms = int((time.time() - start_time) * 1000)
            
            # Convert sources dict to string format with URL for Pydantic validation
            # Format: "Title | URL | document_id | protocol"
            sources_data = result.get("sources", [])
            sources_str = []
            for src in sources_data:
                if isinstance(src, dict):
                    title = src.get("title", "Documento")
                    doc_id = src.get("document_id", "unknown")
                    url = src.get("url", "")
                    protocol = src.get("protocol_number", "")
                    
                    # Format with URL for frontend parsing: "Title|URL|doc_id|protocol"
                    # Frontend can split by "|" and create clickable links
                    source_entry = f"{title}|{url}|{doc_id}|{protocol}"
                    sources_str.append(source_entry)
                elif isinstance(src, str):
                    sources_str.append(src)
            
            return ChatResponse(
                message=result.get("answer", ""),
                model="rag-generative-pipeline",
                usage={
                    "prompt_tokens": 0,  # TODO: calcola da pipeline
                    "completion_tokens": 0,
                    "total_tokens": 0,
                    "elapsed_ms": elapsed_ms
                },
                citations=sources_str,  # Sources del tenant usate (convertite a stringhe)
                urs_score=None,  # No URS rigoroso per query generative
                urs_explanation=result.get("urs_explanation", "Query generativa - usa contesto tenant senza verifica URS rigorosa"),
                claims=[],
                sources=sources_str,  # Same as citations
                hallucinations_found=[],
                gaps_detected=[],
                processing_notice=result.get("processing_notice")  # Messaggio di progresso se presente
            )
        
        elif action == RouterAction.DIRECT_QUERY.value:
            # Query conversazionale → Recupera memorie utente e genera risposta
            elapsed_ms = 0
            
            # Se user_id presente, recupera memorie per contesto personalizzato
            if request.user_id:
                from app.services.rag_fortress.retriever import HybridRetriever
                retriever = HybridRetriever()
                
                # Genera embedding per la query
                question_embedding = await retriever._generate_embedding(question)
                
                # Recupera memorie rilevanti
                memories = await retriever._retrieve_user_memories(
                    user_id=request.user_id,
                    question_embedding=question_embedding,
                    max_memories=3,
                    min_score=0.3
                )
                
                # Costruisci contesto da memorie
                memory_context = ""
                if memories:
                    logger.info(f"🔍 DEBUG - Memorie recuperate: {len(memories)}")
                    for idx, m in enumerate(memories):
                        logger.info(f"🔍 DEBUG - Memoria {idx}: {m}")
                    
                    memory_texts = [m.get("content", "") for m in memories]
                    logger.info(f"🔍 DEBUG - Testi estratti: {memory_texts}")
                    
                    memory_context = "\n\nInformazioni personali dell'utente:\n" + "\n".join(f"- {text}" for text in memory_texts if text)
                    logger.info(f"🔍 DEBUG - Contesto finale: {memory_context}")
                
                # Aggiungi contesto memorie al messaggio di sistema
                system_message = {
                    "role": "system",
                    "content": f"Sei un assistente utile e conversazionale. Rispondi in modo naturale e amichevole.{memory_context}"
                }
                messages = [system_message] + [{"role": msg.role, "content": msg.content} for msg in request.messages]
            else:
                # Nessun user_id, usa solo i messaggi
                messages = [{"role": msg.role, "content": msg.content} for msg in request.messages]
            
            context = {
                "tenant_id": request.tenant_id,
                "persona": request.persona,
                "task_class": "conversational"
            }
            
            adapter = ai_router.get_chat_adapter(context)
            
            result = await adapter.generate(messages, temperature=0.7, max_tokens=150)
            elapsed_ms = int((time.time() - start_time) * 1000)
            
            return ChatResponse(
                message=result["content"],
                model=f"{result['model']}-conversational",
                usage={
                    "prompt_tokens": result["usage"].get("prompt_tokens", 0),
                    "completion_tokens": result["usage"].get("completion_tokens", 0),
                    "total_tokens": result["usage"].get("total_tokens", 0),
                    "elapsed_ms": elapsed_ms
                },
                citations=[],
                urs_score=None,
                urs_explanation="Query conversazionale con memorie personalizzate" if request.user_id else "Query conversazionale",
                claims=[],
                sources=[],
                hallucinations_found=[],
                gaps_detected=[]
            )
        
        else:  # BLOCK
            # Query bloccata (non verificabile o inappropriata)
            raise HTTPException(
                status_code=400,
                detail=f"Query non processabile: {routing.get('reason', 'Query bloccata')}"
            )
    
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Chat inference failed: {str(e)}")

