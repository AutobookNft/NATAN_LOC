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
        self.relevance_threshold = 8.8  # Soglia minima per includere chunk
        
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
        top_k: int = 100
    ) -> List[Dict]:
        """
        Recupera evidenze usando hybrid search MongoDB Atlas
        
        Args:
            question: Domanda dell'utente
            tenant_id: ID del tenant per isolamento dati
            top_k: Numero massimo di chunk da recuperare inizialmente
            
        Returns:
            Lista di dict con evidenze verificate
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
            
            # Step 1: Genera embedding per la domanda
            question_embedding = await self._generate_embedding(question)
            
            # Step 2: Over-retrieve 100 chunk con vector search
            # MongoDB Atlas vector search pipeline
            vector_search_pipeline = [
                {
                    "$vectorSearch": {
                        "index": "vector_index",  # Nome index da configurare
                        "path": "embedding",
                        "queryVector": question_embedding,
                        "numCandidates": top_k * 2,  # Over-retrieve
                        "limit": top_k,
                        "filter": {
                            "tenant_id": tenant_id  # Isolamento tenant
                        }
                    }
                },
                {
                    "$project": {
                        "_id": 1,
                        "content": 1,
                        "source": 1,
                        "metadata": 1,
                        "score": {"$meta": "vectorSearchScore"}
                    }
                }
            ]
            
            # Esegui vector search
            vector_results = list(collection.aggregate(vector_search_pipeline))
            
            # Step 3: Se keywords rilevate, aggiungi text search
            text_results = []
            if await self._detect_keywords(question):
                try:
                    text_search_pipeline = [
                        {
                            "$match": {
                                "tenant_id": tenant_id,
                                "$text": {"$search": question}
                            }
                        },
                        {
                            "$project": {
                                "_id": 1,
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
                    text_results = list(collection.aggregate(text_search_pipeline))
                except Exception as e:
                    # Text index non disponibile - skip text search
                    logger.debug(f"Text search non disponibile (text index mancante): {e}")
                    text_results = []
            
            # Step 4: Combina risultati
            all_chunks = {}
            for chunk in vector_results + text_results:
                chunk_id = str(chunk.get("_id"))
                if chunk_id not in all_chunks:
                    all_chunks[chunk_id] = {
                        "evidence_id": chunk_id,
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
            reranked_chunks = await self._rerank_chunks(question, chunks_list, top_k)
            
            # Step 6: Filtra per relevance_score > 8.8
            filtered_chunks = [
                chunk for chunk in reranked_chunks
                if chunk.get("score", 0) >= self.relevance_threshold
            ]
            
            logger.info(f"Retrieved {len(filtered_chunks)} evidenze per tenant {tenant_id}")
            
            return filtered_chunks
            
        except Exception as e:
            logger.error(f"Errore durante retrieval evidenze: {e}", exc_info=True)
            return []
