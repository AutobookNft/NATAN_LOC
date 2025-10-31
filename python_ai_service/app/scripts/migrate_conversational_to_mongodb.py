"""
Migration script: Migrate conversational responses from JSON to MongoDB with embeddings
Generates embeddings for all existing questions to enable semantic search
"""

import asyncio
import json
import logging
import os
from pathlib import Path
import sys

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent.parent))

from app.services.conversational_learner import ConversationalLearner
from app.services.mongodb_service import MongoDBService
from app.services.ai_router import AIRouter

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


async def migrate_responses():
    """
    Migrate all learned responses from JSON to MongoDB with embeddings
    """
    # Reset MongoDB client to ensure fresh connection
    MongoDBService._client = None
    MongoDBService._connected = False
    
    # Force connection attempt by calling get_client()
    logger.info("🔌 Tentativo connessione MongoDB...")
    client = MongoDBService.get_client()
    
    # Check MongoDB connection
    if not client or not MongoDBService.is_connected():
        logger.error("❌ MongoDB non è connesso! Impossibile migrare.")
        logger.info("💡 Avvia MongoDB o verifica la connessione.")
        logger.info(f"💡 URI configurata: mongodb://{os.getenv('MONGO_DB_USERNAME', 'natan_user')}:***@{os.getenv('MONGO_DB_HOST', 'localhost')}:{os.getenv('MONGO_DB_PORT', '27017')}")
        return False
    
    logger.info("✅ MongoDB connesso con successo!")
    
    # Path to JSON file
    json_file = Path(__file__).parent.parent.parent / "data" / "learned_responses.json"
    
    if not json_file.exists():
        logger.warning(f"⚠️ File JSON non trovato: {json_file}")
        return False
    
    # Load JSON responses
    logger.info(f"📖 Caricamento risposte da {json_file}...")
    try:
        with open(json_file, 'r', encoding='utf-8') as f:
            json_responses = json.load(f)
    except Exception as e:
        logger.error(f"❌ Errore caricamento JSON: {e}")
        return False
    
    if not json_responses:
        logger.info("ℹ️ Nessuna risposta da migrare")
        return True
    
    logger.info(f"✅ Trovate {len(json_responses)} risposte da migrare")
    
    # Initialize services
    learner = ConversationalLearner()
    ai_router = AIRouter()
    
    # Statistics
    migrated = 0
    skipped = 0
    errors = 0
    
    # Check which responses already exist in MongoDB
    logger.info("🔍 Verifica risposte già esistenti in MongoDB...")
    existing_questions = set()
    try:
        existing = MongoDBService.find_documents("conversational_responses", {})
        existing_questions = {item.get("question_lower", "").lower() for item in existing}
        logger.info(f"📊 Trovate {len(existing_questions)} risposte già presenti in MongoDB")
    except Exception as e:
        logger.warning(f"⚠️ Errore verifica risposte esistenti: {e}")
    
    # Migrate each response
    for idx, response_data in enumerate(json_responses, 1):
        question = response_data.get("question", "")
        question_lower = response_data.get("question_lower", "").lower()
        variants = response_data.get("variants", [])
        
        if not question or not variants:
            logger.warning(f"⚠️ Risposta {idx}: dati incompleti, skip")
            skipped += 1
            continue
        
        # Check if already exists
        if question_lower in existing_questions:
            logger.info(f"⏭️  [{idx}/{len(json_responses)}] '{question[:50]}...' già presente, skip")
            skipped += 1
            continue
        
        logger.info(f"🔄 [{idx}/{len(json_responses)}] Migrazione: '{question[:50]}...'")
        
        try:
            # Generate embedding for question
            logger.info(f"  🤖 Generazione embedding...")
            ai_context = {
                "tenant_id": 0,
                "task_class": "conversational"
            }
            embedding_adapter = ai_router.get_embedding_adapter(ai_context)
            embed_result = await embedding_adapter.embed(question)
            embedding = embed_result.get("embedding", [])
            
            if not embedding:
                logger.warning(f"  ⚠️ Impossibile generare embedding, skip")
                skipped += 1
                continue
            
            logger.info(f"  ✅ Embedding generato ({len(embedding)} dimensioni)")
            
            # Save to MongoDB with embedding
            learned_data = {
                "question": question,
                "question_lower": question_lower,
                "pattern": response_data.get("pattern"),  # Keep for backwards compat
                "variants": variants,
                "embedding": embedding,
                "learned_at": response_data.get("learned_at", str(Path(__file__).parent.parent.parent / "data")),
                "usage_count": response_data.get("usage_count", 0),
                "migrated_from_json": True  # Flag per tracciare migrazione
            }
            
            MongoDBService.insert_document("conversational_responses", learned_data)
            logger.info(f"  ✅ Salvato in MongoDB con embedding")
            migrated += 1
            
            # Small delay to avoid rate limiting
            await asyncio.sleep(0.1)
            
        except Exception as e:
            logger.error(f"  ❌ Errore migrazione: {e}")
            errors += 1
            continue
    
    # Summary
    logger.info("\n" + "="*60)
    logger.info("📊 RIEPILOGO MIGRAZIONE")
    logger.info("="*60)
    logger.info(f"✅ Migrate:        {migrated}")
    logger.info(f"⏭️  Saltate:        {skipped}")
    logger.info(f"❌ Errori:         {errors}")
    logger.info(f"📝 Totale:         {len(json_responses)}")
    logger.info("="*60)
    
    if migrated > 0:
        logger.info(f"\n🎉 Migrazione completata! {migrated} risposte ora hanno embeddings in MongoDB")
        logger.info("💡 Le ricerche semantiche funzioneranno meglio ora!")
    
    return True


async def verify_migration():
    """
    Verify migrated data in MongoDB
    """
    logger.info("\n🔍 Verifica dati migrati...")
    
    if not MongoDBService.is_connected():
        logger.error("❌ MongoDB non connesso")
        return
    
    try:
        # Count total responses
        all_responses = MongoDBService.find_documents("conversational_responses", {})
        total = len(all_responses)
        
        # Count with embeddings
        with_embeddings = MongoDBService.find_documents(
            "conversational_responses",
            {"embedding": {"$exists": True, "$ne": None}}
        )
        with_emb = len(with_embeddings)
        
        logger.info(f"📊 Risposte totali:           {total}")
        logger.info(f"📊 Con embeddings:            {with_emb}")
        logger.info(f"📊 Senza embeddings:          {total - with_emb}")
        
        if with_emb > 0:
            # Show sample
            logger.info(f"\n📝 Esempio risposta con embedding:")
            sample = with_embeddings[0]
            logger.info(f"   Domanda: {sample.get('question', 'N/A')[:60]}...")
            logger.info(f"   Embedding: {len(sample.get('embedding', []))} dimensioni")
            logger.info(f"   Varianti: {len(sample.get('variants', []))}")
        
    except Exception as e:
        logger.error(f"❌ Errore verifica: {e}")


async def main():
    """
    Main migration function
    """
    logger.info("="*60)
    logger.info("🚀 MIGRAZIONE RISPOSTE CONVERSAZIONALI")
    logger.info("   Da JSON → MongoDB con Embeddings")
    logger.info("="*60)
    logger.info("")
    
    # Run migration
    success = await migrate_responses()
    
    if success:
        # Verify
        await verify_migration()
    
    logger.info("\n✅ Migrazione completata!")


if __name__ == "__main__":
    asyncio.run(main())

