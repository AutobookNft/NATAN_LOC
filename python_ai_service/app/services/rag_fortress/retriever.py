"""
Retriever avanzato con hybrid search MongoDB Atlas
Multi-tenant con reranking per massima accuratezza
"""

import logging
from typing import List, Dict, Optional
import numpy as np
from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter
from app.services.providers import OpenAIEmbeddingAdapter
import os

def cosine_similarity(vec1: List[float], vec2: List[float]) -> float:
    """Calcola cosine similarity tra due vettori"""
    try:
        vec1_array = np.array(vec1)
        vec2_array = np.array(vec2)
        dot_product = np.dot(vec1_array, vec2_array)
        norm1 = np.linalg.norm(vec1_array)
        norm2 = np.linalg.norm(vec2_array)
        if norm1 == 0 or norm2 == 0:
            return 0.0
        return float(dot_product / (norm1 * norm2))
    except Exception:
        return 0.0

logger = logging.getLogger(__name__)

class HybridRetriever:
    """
    Retriever ibrido avanzato per MongoDB Atlas
    Combina vector search e text search con reranking
    """
    
    def __init__(self):
        self.ai_router = AIRouter()
        self.embedding_model = "openai.text-embedding-3-small"  # Configurabile: voyage-3 o openai
        self.reranker_model = "bge-reranker"  # Configurabile: bge-reranker o cohere-rerank-3.5
        # Atlas Vector Search restituisce score 0-1, non 0-10!
        # Soglia 0.5 = buona rilevanza per similarity cosine
        self.relevance_threshold = 0.5  # Soglia minima per includere chunk (Atlas: 0-1)
        
    async def _generate_embedding(self, text: str) -> List[float]:
        """Genera embedding per il testo"""
        try:
            context = {"task_class": "embedding"}
            adapter = self.ai_router.get_embedding_adapter(context)
            result = await adapter.embed(text)
            return result["embedding"]
        except Exception as e:
            logger.error(f"Errore generazione embedding: {e}")
            raise
    
    async def _detect_keywords(self, question: str) -> bool:
        """
        Rileva se la domanda contiene keyword per text search
        Semplice implementazione: controlla presenza di termini specifici PA
        """
        pa_keywords = [
            "delibera", "determina", "ordinanza", "regolamento",
            "atto", "documento", "numero", "data", "anno",
            "comune", "provincia", "regione", "ente"
        ]
        question_lower = question.lower()
        return any(keyword in question_lower for keyword in pa_keywords)
    
    async def _rerank_chunks(
        self, 
        question: str, 
        chunks: List[Dict], 
        top_k: int = 20
    ) -> List[Dict]:
        """
        Rerank chunks usando bge-reranker o Cohere Rerank 3.5
        Per ora implementazione semplificata basata su score MongoDB
        TODO: Integrare servizio reranking esterno se disponibile
        """
        # Per ora usiamo lo score MongoDB come base
        # In produzione integrare servizio reranking dedicato
        sorted_chunks = sorted(
            chunks, 
            key=lambda x: x.get("score", 0), 
            reverse=True
        )
        return sorted_chunks[:top_k]
    
    async def retrieve_evidence(
        self, 
        question: str, 
        tenant_id: str,
        user_id: Optional[int] = None,
        top_k: int = 100
    ) -> List[Dict]:
        """
        Recupera evidenze usando hybrid search MongoDB Atlas + user memories
        
        Args:
            question: Domanda dell'utente
            tenant_id: ID del tenant per isolamento dati (può essere stringa o intero)
            user_id: ID utente per memorie personalizzate (opzionale)
            top_k: Numero massimo di chunk da recuperare inizialmente
            
        Returns:
            Lista di dict con evidenze verificate (documenti PA + memorie utente)
            Ogni dict contiene: evidence_id, content, source, metadata, score, exact_quote
        """
        try:
            if not MongoDBService.is_connected():
                logger.warning("MongoDB non connesso, ritorno lista vuota")
                return []
            
            collection = MongoDBService.get_collection("documents")
            if collection is None:
                logger.warning("Collection 'documents' non trovata")
                return []
            
            # Normalizza tenant_id: prova sia come intero che come stringa
            # I documenti possono avere tenant_id come int o str
            try:
                tenant_id_int = int(tenant_id)
            except (ValueError, TypeError):
                tenant_id_int = None
            
            # Step 1: Genera embedding per la domanda
            question_embedding = await self._generate_embedding(question)
            
            # Step 2: Over-retrieve chunk con vector search
            # MongoDB Atlas $vectorSearch non supporta $in, quindi facciamo 2 ricerche separate
            vector_results = []
            
            # Ricerca 1: tenant_id come stringa
            vector_search_pipeline_str = [
                {
                    "$vectorSearch": {
                        "index": "vector_index",
                        "path": "embedding",
                        "queryVector": question_embedding,
                        "numCandidates": top_k * 2,
                        "limit": top_k,
                        "filter": {
                            "tenant_id": tenant_id  # Stringa
                        }
                    }
                },
                {
                    "$project": {
                        "_id": 1,
                        "document_id": 1,
                        "title": 1,
                        "protocol_number": 1,
                        "protocol_date": 1,
                        "content": 1,
                        "source": 1,
                        "metadata": 1,
                        "score": {"$meta": "vectorSearchScore"}
                    }
                }
            ]
            
            try:
                vector_results_str = list(collection.aggregate(vector_search_pipeline_str))
                vector_results.extend(vector_results_str)
                logger.debug(f"Vector search con tenant_id stringa: {len(vector_results_str)} risultati")
            except Exception as e:
                logger.debug(f"Vector search con tenant_id stringa fallita: {e}")
            
            # Ricerca 2: tenant_id come intero (SEMPRE se convertibile)
            # Fix: esegui sempre entrambe le ricerche per gestire documenti con int o str
            if tenant_id_int is not None:
                vector_search_pipeline_int = [
                    {
                        "$vectorSearch": {
                            "index": "vector_index",
                            "path": "embedding",
                            "queryVector": question_embedding,
                            "numCandidates": top_k * 2,
                            "limit": top_k,
                            "filter": {
                                "tenant_id": tenant_id_int  # Intero
                            }
                        }
                    },
                    {
                        "$project": {
                            "_id": 1,
                            "document_id": 1,
                            "title": 1,
                            "protocol_number": 1,
                            "protocol_date": 1,
                            "content": 1,
                            "source": 1,
                            "metadata": 1,
                            "score": {"$meta": "vectorSearchScore"}
                        }
                    }
                ]
                
                try:
                    vector_results_int = list(collection.aggregate(vector_search_pipeline_int))
                    vector_results.extend(vector_results_int)
                    logger.debug(f"Vector search con tenant_id intero: {len(vector_results_int)} risultati")
                except Exception as e:
                    logger.debug(f"Vector search con tenant_id intero fallita: {e}")
            
            logger.info(f"Vector search totale: {len(vector_results)} risultati")
            
            # FALLBACK: Se vector search non trova risultati (indice non esiste), usa ricerca manuale
            used_fallback = False
            if len(vector_results) == 0:
                logger.warning("Vector search restituisce 0 risultati - probabile indice vettoriale mancante. Uso fallback ricerca manuale.")
                # Per query generative, aumenta top_k per recuperare più documenti
                # Una query generica deve trovare molti documenti, non solo 5-10
                fallback_top_k = top_k * 2 if top_k < 50 else top_k  # Almeno il doppio per query generative
                vector_results = await self._fallback_manual_vector_search(
                    question_embedding, tenant_id, tenant_id_int, fallback_top_k
                )
                used_fallback = True
                logger.info(f"Fallback ricerca manuale: {len(vector_results)} risultati")
            
            # Step 3: Se keywords rilevate, aggiungi text search
            text_results = []
            if await self._detect_keywords(question):
                # Text search: prova sia con stringa che intero
                text_filters = [{"tenant_id": tenant_id}]
                if tenant_id_int is not None and str(tenant_id_int) != tenant_id:
                    text_filters.append({"tenant_id": tenant_id_int})
                
                for text_filter in text_filters:
                    try:
                        text_search_pipeline = [
                            {
                                "$match": {
                                    **text_filter,
                                    "$text": {"$search": question}
                                }
                            },
                            {
                                "$project": {
                                    "_id": 1,
                                    "document_id": 1,
                                    "title": 1,
                                    "protocol_number": 1,
                                    "protocol_date": 1,
                                    "content": 1,
                                    "source": 1,
                                    "metadata": 1,
                                    "score": {"$meta": "textScore"}
                                }
                            },
                            {
                                "$limit": top_k // 2  # Metà chunk da text search
                            }
                        ]
                        text_results_batch = list(collection.aggregate(text_search_pipeline))
                        text_results.extend(text_results_batch)
                    except Exception as e:
                        # Text index non disponibile - skip text search
                        logger.debug(f"Text search non disponibile (text index mancante): {e}")
            
            # Step 3.5: Recupera memorie utente se user_id fornito
            user_memories = []
            if user_id is not None:
                user_memories = await self._retrieve_user_memories(
                    user_id=user_id,
                    question_embedding=question_embedding,
                    max_memories=5
                )
                if user_memories:
                    logger.info(f"💭 Recuperate {len(user_memories)} memorie utente per user_id={user_id}")
            
            # Step 4: Combina risultati (documenti PA + memorie utente)
            all_chunks = {}
            for chunk in vector_results + text_results + user_memories:
                chunk_id = str(chunk.get("_id"))
                if chunk_id not in all_chunks:
                    # Estrai document_id: prova dal campo document_id, poi da metadata, poi usa chunk_id
                    document_id = chunk.get("document_id")
                    if not document_id:
                        metadata = chunk.get("metadata", {})
                        document_id = metadata.get("document_id") if isinstance(metadata, dict) else None
                    if not document_id:
                        # Fallback: usa chunk_id come document_id
                        document_id = chunk_id
                    
                    all_chunks[chunk_id] = {
                        "evidence_id": chunk_id,
                        "document_id": document_id,
                        "title": chunk.get("title", "Documento senza titolo"),
                        "protocol_number": chunk.get("protocol_number", ""),
                        "protocol_date": chunk.get("protocol_date", ""),
                        "content": chunk.get("content", ""),
                        "source": chunk.get("source", "unknown"),
                        "metadata": chunk.get("metadata", {}),
                        "score": chunk.get("score", 0.0),
                        "exact_quote": None  # Sarà popolato da evidence_verifier
                    }
                else:
                    # Se duplicato, mantieni score più alto
                    all_chunks[chunk_id]["score"] = max(
                        all_chunks[chunk_id]["score"],
                        chunk.get("score", 0.0)
                    )
            
            # Step 5: Rerank con bge-reranker o Cohere
            chunks_list = list(all_chunks.values())
            
            # Se abbiamo usato fallback (cosine similarity), salta il reranking e usa direttamente i risultati
            # perché il reranker potrebbe modificare gli score in modo incompatibile
            if used_fallback and len(chunks_list) > 0:
                logger.info(f"Usando risultati fallback direttamente senza reranking (cosine similarity già calcolata)")
                # Prendi i top_k risultati già ordinati per score
                filtered_chunks = sorted(chunks_list, key=lambda x: x.get("score", 0), reverse=True)[:top_k]
                # Rimuovi filtro soglia per fallback (cosine similarity va da -1 a 1, non da 0 a 10)
                filtered_chunks = [chunk for chunk in filtered_chunks if chunk.get("score", 0) >= 0.3]
            else:
                reranked_chunks = await self._rerank_chunks(question, chunks_list, top_k)
                # Step 6: Filtra per relevance_score > 8.8 (per risultati da vector search)
                filtered_chunks = [
                    chunk for chunk in reranked_chunks
                    if chunk.get("score", 0) >= self.relevance_threshold
                ]
            
            logger.info(f"Retrieved {len(filtered_chunks)} evidenze per tenant {tenant_id}")
            
            return filtered_chunks
            
        except Exception as e:
            logger.error(f"Errore durante retrieval evidenze: {e}", exc_info=True)
            return []
    
    async def _fallback_manual_vector_search(
        self,
        question_embedding: List[float],
        tenant_id: str,
        tenant_id_int: Optional[int],
        top_k: int
    ) -> List[Dict]:
        """
        Fallback: ricerca manuale con cosine similarity quando $vectorSearch non disponibile
        
        Args:
            question_embedding: Embedding della query
            tenant_id: Tenant ID come stringa
            tenant_id_int: Tenant ID come intero (se diverso)
            top_k: Numero massimo di risultati
            
        Returns:
            Lista di documenti con score di similarità
        """
        try:
            collection = MongoDBService.get_collection("documents")
            if collection is None:
                return []
            
            # Filtro tenant: cerca sia intero che stringa
            # IMPORTANTE: I documenti nel DB hanno tenant_id come intero, quindi priorità all'intero
            tenant_filters = []
            if tenant_id_int is not None:
                tenant_filters.append({"tenant_id": tenant_id_int})  # Priorità: intero
            if str(tenant_id_int) != tenant_id:  # Aggiungi stringa solo se diversa
                tenant_filters.append({"tenant_id": tenant_id})
            
            all_results = []
            
            for tenant_filter_base in tenant_filters:
                # Query documenti con embedding (crea copia per non modificare l'originale)
                tenant_filter = tenant_filter_base.copy()
                tenant_filter["embedding"] = {"$exists": True, "$ne": None}
                
                logger.debug(f"Fallback: cercando documenti con filtro {tenant_filter}")
                
                # Recupera tutti i documenti con embedding
                # OTTIMIZZAZIONE: Ridotto limite per evitare timeout con fallback manuale
                # Limite ridotto a 800 per bilanciare recall e performance
                limit_docs = 800 if top_k > 50 else 500
                
                documents = list(collection.find(tenant_filter, {
                    "_id": 1,
                    "document_id": 1,
                    "title": 1,
                    "protocol_number": 1,
                    "protocol_date": 1,
                    "content": 1,
                    "metadata": 1,
                    "embedding": 1
                }).limit(limit_docs))
                
                logger.debug(f"Fallback: recuperati {len(documents)} documenti dal database (limite: {limit_docs})")
                
                logger.debug(f"Fallback: trovati {len(documents)} documenti con filtro {tenant_filter}")
                
                # Calcola cosine similarity per ogni documento
                logger.debug(f"Fallback: elaborando {len(documents)} documenti per calcolo similarity")
                processed = 0
                skipped_no_embedding = 0
                skipped_no_content = 0
                
                for doc in documents:
                    doc_embedding = doc.get("embedding")
                    if not doc_embedding or not isinstance(doc_embedding, list):
                        skipped_no_embedding += 1
                        continue
                    
                    # Calcola similarity
                    similarity = cosine_similarity(question_embedding, doc_embedding)
                    
                    # Estrai contenuto: prova content.full_text o content.raw_text
                    content = ""
                    if "content" in doc:
                        content_obj = doc["content"]
                        if isinstance(content_obj, dict):
                            content = content_obj.get("full_text") or content_obj.get("raw_text") or ""
                        elif isinstance(content_obj, str):
                            content = content_obj
                    
                    # Se non c'è contenuto, prova a prendere dai chunks
                    if not content and "content" in doc and isinstance(doc["content"], dict):
                        chunks = doc["content"].get("chunks", [])
                        if chunks and isinstance(chunks, list) and len(chunks) > 0:
                            # Prendi il primo chunk
                            first_chunk = chunks[0]
                            if isinstance(first_chunk, dict):
                                content = first_chunk.get("chunk_text", "")
                    
                    if not content:
                        skipped_no_content += 1
                        continue
                    
                    processed += 1
                    
                    # Estrai document_id: prova dal campo document_id o usa _id come fallback
                    document_id = doc.get("document_id")
                    if not document_id:
                        # Fallback: usa _id come document_id
                        document_id = str(doc.get("_id"))
                    
                    all_results.append({
                        "_id": doc.get("_id"),
                        "evidence_id": str(doc.get("_id")),
                        "document_id": document_id,
                        "title": doc.get("title", "Documento senza titolo"),
                        "protocol_number": doc.get("protocol_number", ""),
                        "protocol_date": doc.get("protocol_date", ""),
                        "content": content[:2000],  # Limita a 2000 caratteri
                        "source": doc.get("title", "Documento"),
                        "metadata": doc.get("metadata", {}),
                        "score": similarity,
                        "exact_quote": None
                    })
            
            # Ordina per score decrescente e prendi top_k
            all_results.sort(key=lambda x: x.get("score", 0), reverse=True)
            
            logger.info(f"Fallback ricerca manuale: {len(all_results)} risultati totali (processati: {processed}, saltati no-embedding: {skipped_no_embedding}, saltati no-content: {skipped_no_content}), restituisco top {top_k}")
            
            return all_results[:top_k]
            
        except Exception as e:
            logger.error(f"Errore durante fallback ricerca manuale: {e}", exc_info=True)
            return []
    
    async def _retrieve_user_memories(
        self,
        user_id: int,
        question_embedding: List[float],
        max_memories: int = 5,
        min_score: float = 0.3
    ) -> List[Dict]:
        """
        Recupera memorie utente rilevanti alla query
        
        Args:
            user_id: ID utente
            question_embedding: Embedding della domanda
            max_memories: Numero massimo di memorie da recuperare
            min_score: Soglia minima di similarità
            
        Returns:
            Lista di memorie formattate come evidenze per RAG-Fortress
        """
        try:
            memories_collection = MongoDBService.get_collection("user_memories")
            if memories_collection is None:
                logger.debug("Collection 'user_memories' non trovata")
                return []
            
            # Query memorie attive per questo utente con embedding
            memories = list(memories_collection.find({
                "user_id": user_id,
                "is_active": True,
                "embedding": {"$exists": True, "$ne": None}
            }).limit(50))  # Recupera più memorie per scoring
            
            if not memories:
                return []
            
            logger.debug(f"💭 Trovate {len(memories)} memorie per user_id={user_id}")
            
            # Calcola similarità per ogni memoria
            scored_memories = []
            for memory in memories:
                memory_embedding = memory.get("embedding")
                if not memory_embedding or not isinstance(memory_embedding, list):
                    continue
                
                similarity = cosine_similarity(question_embedding, memory_embedding)
                
                if similarity >= min_score:
                    # Formatta memoria come evidenza per RAG-Fortress
                    memory_id = str(memory.get("_id", ""))
                    content = memory.get("content", "")
                    
                    if not content:
                        continue
                    
                    scored_memories.append({
                        "_id": memory.get("_id"),
                        "evidence_id": f"memory_{memory_id}",
                        "document_id": f"user_memory_{memory_id}",
                        "title": f"💭 Memoria Personale: {memory.get('memory_type', 'generale')}",
                        "protocol_number": "",
                        "protocol_date": "",
                        "content": content,
                        "source": "Memoria Utente",
                        "metadata": {
                            "type": "user_memory",
                            "memory_type": memory.get("memory_type", "general"),
                            "memory_id": memory_id,
                            "created_at": memory.get("created_at"),
                            "is_memory": True
                        },
                        "score": float(similarity),
                        "exact_quote": None
                    })
            
            # Ordina per similarità e limita
            scored_memories.sort(key=lambda x: x.get("score", 0), reverse=True)
            result = scored_memories[:max_memories]
            
            logger.info(f"💭 Restituite {len(result)} memorie con score >= {min_score}")
            return result
            
        except Exception as e:
            logger.error(f"❌ Errore recupero memorie utente: {e}", exc_info=True)
            return []
