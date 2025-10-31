"""
Conversational Learner - Auto-learning system for conversational responses
When a question doesn't match existing patterns, asks AI to generate 5+ response variants
and stores them for future use
"""

import json
import asyncio
import re
from typing import Dict, List, Optional
from pathlib import Path
from app.services.ai_router import AIRouter
from app.services.mongodb_service import MongoDBService
import logging

logger = logging.getLogger(__name__)


class ConversationalLearner:
    """
    Sistema di apprendimento automatico per risposte conversazionali
    """
    
    # Storage paths
    LEARNED_RESPONSES_FILE = Path(__file__).parent.parent.parent / "data" / "learned_responses.json"
    
    def __init__(self):
        """Initialize learner"""
        self.ai_router = AIRouter()
        self._ensure_data_dir()
    
    def _ensure_data_dir(self):
        """Ensure data directory exists"""
        self.LEARNED_RESPONSES_FILE.parent.mkdir(parents=True, exist_ok=True)
    
    async def learn_from_ai(
        self,
        question: str,
        context: Optional[Dict] = None
    ) -> List[str]:
        """
        Ask AI to generate 5+ response variants for a question
        
        Args:
            question: User question that didn't match existing patterns
            context: Optional context (tenant_id, persona, etc.)
        
        Returns:
            List of 5+ response variants from AI
        """
        try:
            # Build prompt for AI
            prompt = self._build_learning_prompt(question, context)
            
            # Get AI adapter
            ai_context = {
                "tenant_id": context.get("tenant_id", 0) if context else 0,
                "task_class": "conversational",
                "persona": context.get("persona", "strategic") if context else "strategic"
            }
            
            chat_adapter = None
            
            # Try primary adapter first
            try:
                chat_adapter = self.ai_router.get_chat_adapter(ai_context)
            except Exception as e:
                logger.warning(f"Failed to get primary chat adapter: {e}")
            
            # Fallback to OpenAI if primary fails or if generation fails
            if chat_adapter is None:
                logger.info("Using OpenAI fallback for conversational learning")
                from app.services.providers import OpenAIChatAdapter
                chat_adapter = OpenAIChatAdapter(model="gpt-4o-mini")
            
            # Generate responses
            messages = [
                {
                    "role": "system",
                    "content": "Sei un assistente per domande sui documenti della PA. Genera risposte naturali, amichevoli e utili."
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ]
            
            # Try generation with primary adapter, fallback to OpenAI on error
            result = None
            try:
                result = await chat_adapter.generate(
                    messages,
                    temperature=0.8,
                    max_tokens=1000
                )
            except Exception as e:
                logger.warning(f"Primary adapter failed: {e}, trying OpenAI fallback...")
                # Use OpenAI as fallback
                from app.services.providers import OpenAIChatAdapter
                openai_adapter = OpenAIChatAdapter(model="gpt-4o-mini")
                result = await openai_adapter.generate(
                    messages,
                    temperature=0.8,
                    max_tokens=1000
                )
            response_text = result["content"]
            logger.info(f"Raw AI response: {response_text[:200]}...")
            
            # Parse AI response to extract variants
            variants = self._parse_ai_response(response_text)
            logger.info(f"Parsed {len(variants)} variants: {variants[:2] if variants else 'None'}...")
            
            if len(variants) >= 5:
                logger.info(f"✅ Generated {len(variants)} response variants for question: {question[:50]}")
                return variants
            elif len(variants) > 0:
                logger.warning(f"⚠️ AI generated only {len(variants)} variants (expected at least 5), but using them anyway")
                return variants
            else:
                logger.error(f"❌ Failed to parse any variants from AI response")
                return []
        
        except Exception as e:
            logger.error(f"Error learning from AI: {e}")
            return []
    
    def _build_learning_prompt(self, question: str, context: Optional[Dict] = None) -> str:
        """Build prompt for AI to generate response variants"""
        return f"""Devi generare ALMENO 5 risposte diverse (ideale 7-10) per questa domanda conversazionale dell'utente:

Domanda utente: "{question}"

REQUISITI:
1. Genera ALMENO 5 varianti di risposta diverse
2. Ogni risposta deve essere naturale, amichevole e utile
3. Le risposte devono essere appropriate per un assistente AI che aiuta con domande sui documenti della PA
4. Varia lo stile, il tono, la lunghezza tra le risposte
5. Mantieni il contesto: sei NATAN, assistente per documenti della PA

FORMATO OUTPUT (JSON array):
[
  "Prima risposta variante",
  "Seconda risposta variante",
  "Terza risposta variante",
  "Quarta risposta variante",
  "Quinta risposta variante"
]

Genera solo il JSON array, niente altro testo prima o dopo."""
    
    def _parse_ai_response(self, response_text: str) -> List[str]:
        """
        Parse AI response to extract response variants
        
        Args:
            response_text: Raw AI response
            
        Returns:
            List of response variants
        """
        variants = []
        
        try:
            # Try to parse as JSON first
            # Remove markdown code blocks if present
            cleaned = response_text.strip()
            logger.debug(f"Cleaning AI response: first 100 chars = {cleaned[:100]}")
            
            if cleaned.startswith("```"):
                # Extract JSON from code block
                lines = cleaned.split("\n")
                json_lines = []
                in_code = False
                for line in lines:
                    if line.strip().startswith("```"):
                        in_code = not in_code
                        continue
                    if in_code:
                        json_lines.append(line)
                cleaned = "\n".join(json_lines).strip()
                logger.debug(f"Extracted from code block: first 100 chars = {cleaned[:100]}")
            elif "```json" in cleaned:
                # Remove ```json and ``` markers
                cleaned = re.sub(r'```json\s*', '', cleaned)
                cleaned = re.sub(r'```\s*$', '', cleaned).strip()
                logger.debug(f"Cleaned JSON markers: first 100 chars = {cleaned[:100]}")
            
            # Try to parse JSON
            try:
                parsed = json.loads(cleaned)
                if isinstance(parsed, list):
                    variants = [str(v).strip() for v in parsed if v and str(v).strip()]
                    logger.info(f"✅ Successfully parsed {len(variants)} variants from JSON")
            except json.JSONDecodeError as e:
                logger.warning(f"JSON parsing failed: {e}, trying fallback methods...")
                
                # Fallback 1: Look for JSON array pattern in text
                json_array_match = re.search(r'\[([^\]]+)\]', cleaned, re.DOTALL)
                if json_array_match:
                    array_content = json_array_match.group(1)
                    # Try to extract quoted strings
                    string_matches = re.findall(r'"([^"]+)"', array_content)
                    if string_matches:
                        variants = [s.strip() for s in string_matches if s.strip()]
                        logger.info(f"✅ Extracted {len(variants)} variants using regex fallback")
        
        except Exception as e:
            logger.error(f"Error in JSON parsing: {e}")
        
        # Fallback 2: Parse as lines/paragraphs if no JSON found
        if not variants:
            logger.info("Trying line-based parsing fallback...")
            lines = response_text.strip().split("\n")
            for line in lines:
                line = line.strip()
                # Remove numbering (1. 2. etc.) and bullet points
                line = re.sub(r'^[\d\s\.\)\-•]+\s*', '', line)
                # Remove quotes if entire line is quoted
                if line.startswith('"') and line.endswith('"'):
                    line = line[1:-1]
                if line and len(line) > 10:  # Minimum length
                    variants.append(line)
            
            if variants:
                logger.info(f"✅ Extracted {len(variants)} variants using line-based parsing")
        
        # Fallback 3: Split by sentences if still nothing
        if not variants:
            logger.info("Trying sentence-based parsing fallback...")
            sentences = re.split(r'[.!?]\s+', response_text)
            variants = [s.strip() + "." for s in sentences if len(s.strip()) > 20][:10]
            
            if variants:
                logger.info(f"✅ Extracted {len(variants)} variants using sentence-based parsing")
        
        # Clean and validate variants
        cleaned_variants = []
        for v in variants[:10]:  # Max 10 variants
            v = v.strip()
            if v and len(v) > 5:  # Minimum reasonable length
                cleaned_variants.append(v)
        
        logger.info(f"Final parsed variants count: {len(cleaned_variants)}")
        return cleaned_variants
    
    async def save_learned_responses(
        self,
        question: str,
        question_lower: str,
        variants: List[str],
        pattern: Optional[str] = None,
        embedding: Optional[List[float]] = None
    ) -> bool:
        """
        Save learned responses to storage (MongoDB + JSON file backup)
        Now includes embedding for semantic search
        
        Args:
            question: Original question
            question_lower: Lowercase version for matching
            variants: List of response variants
            pattern: Optional regex pattern to match similar questions (kept for backwards compatibility)
            embedding: Optional pre-computed embedding, if None will generate it
        
        Returns:
            True if saved successfully
        """
        try:
            # Generate embedding if not provided
            if embedding is None:
                logger.info(f"Generating embedding for question: {question[:50]}")
                embedding = await self._generate_question_embedding(question)
            
            # Prepare data
            learned_data = {
                "question": question,
                "question_lower": question_lower,
                "pattern": pattern or self._extract_pattern(question_lower),  # Keep for backwards compat
                "variants": variants,
                "embedding": embedding,  # New: semantic search
                "learned_at": str(Path(__file__).parent.parent.parent / "data"),
                "usage_count": 0
            }
            
            # Save to MongoDB if available
            if MongoDBService.is_connected():
                try:
                    MongoDBService.insert_document("conversational_responses", learned_data)
                    logger.info(f"✅ Saved learned responses with embedding to MongoDB for: {question[:50]}")
                except Exception as e:
                    logger.warning(f"Failed to save to MongoDB: {e}")
            
            # Save to JSON file as backup (without embedding to keep file size reasonable)
            json_data = learned_data.copy()
            json_data.pop("embedding", None)  # Don't save large embeddings in JSON
            self._save_to_json_file(json_data)
            
            return True
        
        except Exception as e:
            logger.error(f"Error saving learned responses: {e}")
            return False
    
    async def _generate_question_embedding(self, question: str) -> List[float]:
        """
        Generate embedding for a question using AI router
        
        Args:
            question: Question text
            
        Returns:
            Embedding vector
        """
        try:
            from app.services.ai_router import AIRouter
            
            ai_context = {
                "tenant_id": 0,  # Conversational responses are tenant-agnostic
                "task_class": "conversational"
            }
            
            embedding_adapter = self.ai_router.get_embedding_adapter(ai_context)
            result = await embedding_adapter.embed(question)
            
            return result.get("embedding", [])
        
        except Exception as e:
            logger.error(f"Error generating embedding: {e}")
            return []  # Return empty if generation fails
    
    def _extract_pattern(self, question_lower: str) -> str:
        """
        Extract a simple regex pattern from question
        For now, use word-based pattern
        """
        import re
        # Extract key words (3-15 chars, alphanumeric)
        words = re.findall(r'\b[a-z]{3,15}\b', question_lower)
        if words:
            # Use first 3-5 key words as pattern
            key_words = words[:5]
            pattern = ".*".join(key_words)  # Match questions with these words in order
            return pattern
        return question_lower.replace("?", "\\?").replace(".", "\\.")
    
    def _save_to_json_file(self, data: Dict):
        """Save learned response to JSON file"""
        try:
            # Load existing data
            if self.LEARNED_RESPONSES_FILE.exists():
                with open(self.LEARNED_RESPONSES_FILE, 'r', encoding='utf-8') as f:
                    existing = json.load(f)
            else:
                existing = []
            
            # Add new entry
            existing.append(data)
            
            # Save back
            with open(self.LEARNED_RESPONSES_FILE, 'w', encoding='utf-8') as f:
                json.dump(existing, f, indent=2, ensure_ascii=False)
            
            logger.info(f"Saved learned response to JSON file")
        
        except Exception as e:
            logger.error(f"Error saving to JSON file: {e}")
    
    def load_learned_responses(self) -> List[Dict]:
        """
        Load all learned responses from storage
        
        Returns:
            List of learned response dictionaries
        """
        learned = []
        
        # Load from MongoDB if available
        if MongoDBService.is_connected():
            try:
                mongo_responses = MongoDBService.find_documents("conversational_responses", {})
                learned.extend(mongo_responses)
            except Exception as e:
                logger.warning(f"Failed to load from MongoDB: {e}")
        
        # Load from JSON file
        if self.LEARNED_RESPONSES_FILE.exists():
            try:
                with open(self.LEARNED_RESPONSES_FILE, 'r', encoding='utf-8') as f:
                    json_responses = json.load(f)
                    learned.extend(json_responses)
            except Exception as e:
                logger.warning(f"Failed to load from JSON file: {e}")
        
        return learned
    
    async def find_matching_learned_response(
        self, 
        question: str,
        question_lower: str,
        similarity_threshold: float = 0.75  # Abbassato da 0.85 a 0.75 per match più flessibili
    ) -> Optional[Dict]:
        """
        Find a learned response using semantic similarity (embeddings)
        Falls back to pattern matching if embeddings not available
        
        Args:
            question: Original question (for embedding generation)
            question_lower: Lowercase question (for pattern matching fallback)
            similarity_threshold: Minimum cosine similarity score (0-1)
            
        Returns:
            Matching learned response dict or None
        """
        import re
        
        # Step 1: Try semantic search with embeddings (best method)
        try:
            # Generate embedding for query question
            query_embedding = await self._generate_question_embedding(question)
            
            if query_embedding and len(query_embedding) > 0:
                # Search using vector similarity
                learned_responses = self._load_learned_responses_with_embeddings()
                
                if learned_responses:
                    best_match = self._semantic_search(
                        query_embedding=query_embedding,
                        learned_responses=learned_responses,
                        threshold=similarity_threshold
                    )
                    
                    if best_match:
                        logger.info(f"✅ Found semantic match (similarity: {best_match.get('similarity', 0):.3f})")
                        # Increment usage count
                        learned_data = best_match.get("data", {})
                        learned_data["usage_count"] = learned_data.get("usage_count", 0) + 1
                        self._update_usage_count(learned_data)
                        return learned_data
        except Exception as e:
            logger.warning(f"Semantic search failed: {e}, falling back to pattern matching")
        
        # Step 2: Fallback to pattern matching (if embeddings not available or search failed)
        learned_responses = self.load_learned_responses()
        
        for learned in learned_responses:
            pattern = learned.get("pattern")
            question_match = learned.get("question_lower")
            
            # Try pattern matching
            if pattern:
                try:
                    if re.search(pattern, question_lower, re.IGNORECASE):
                        learned["usage_count"] = learned.get("usage_count", 0) + 1
                        self._update_usage_count(learned)
                        return learned
                except re.error:
                    pass
            
            # Try exact match
            if question_match and question_match == question_lower:
                learned["usage_count"] = learned.get("usage_count", 0) + 1
                self._update_usage_count(learned)
                return learned
        
        return None
    
    def _load_learned_responses_with_embeddings(self) -> List[Dict]:
        """
        Load learned responses that have embeddings (from MongoDB or JSON)
        
        Returns:
            List of learned responses with embeddings
        """
        learned = []
        
        # Load from MongoDB if available (embeddings are stored there)
        if MongoDBService.is_connected():
            try:
                mongo_responses = MongoDBService.find_documents(
                    "conversational_responses",
                    {"embedding": {"$exists": True, "$ne": None}}
                )
                learned.extend(mongo_responses)
                logger.info(f"📊 Caricate {len(mongo_responses)} risposte con embeddings da MongoDB")
            except Exception as e:
                logger.warning(f"Failed to load from MongoDB: {e}")
        
        # Also try to load from JSON and generate embeddings on-the-fly (slower but works without MongoDB)
        # Only if we don't have MongoDB responses (to avoid duplicates)
        if not learned:
            logger.info("🔄 MongoDB non disponibile, tentativo caricamento da JSON...")
            json_responses = self.load_learned_responses()
            # Filter out responses without embeddings in JSON (they won't have them)
            # For now, return empty - embeddings must be generated and saved first
            logger.warning("⚠️ Embeddings disponibili solo se salvati in MongoDB. Usando pattern matching come fallback.")
        
        return learned
    
    def _semantic_search(
        self,
        query_embedding: List[float],
        learned_responses: List[Dict],
        threshold: float = 0.85
    ) -> Optional[Dict]:
        """
        Find best matching learned response using cosine similarity
        
        Args:
            query_embedding: Query question embedding
            learned_responses: List of learned responses with embeddings
            threshold: Minimum similarity threshold
            
        Returns:
            Best match dict with similarity score or None
        """
        from app.services.vector_search import cosine_similarity
        
        best_match = None
        best_score = 0.0
        
        for learned in learned_responses:
            embedding = learned.get("embedding")
            if not embedding or not isinstance(embedding, list) or len(embedding) == 0:
                continue
            
            # Calculate cosine similarity
            similarity = cosine_similarity(query_embedding, embedding)
            
            if similarity > best_score and similarity >= threshold:
                best_score = similarity
                best_match = {
                    "data": learned,
                    "similarity": similarity
                }
        
        return best_match
    
    def _update_usage_count(self, learned: Dict):
        """Update usage count in storage"""
        # TODO: Implement update in MongoDB and JSON
        pass

