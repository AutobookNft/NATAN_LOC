// MongoDB initialization script
// Creates collections and indexes for NATAN_LOC

db = db.getSiblingDB("natan_ai_core");

// Create collections (MongoDB creates automatically, but we define structure)
// Collection: documents (main document storage)
db.documents.createIndex({ tenant_id: 1, document_id: 1 }, { unique: true });
db.documents.createIndex({ tenant_id: 1, document_type: 1 });
db.documents.createIndex({ tenant_id: 1, created_at: -1 });
db.documents.createIndex({ "content.raw_text": "text" });

// Collection: sources (USE pipeline - atomic sources)
db.sources.createIndex({ tenant_id: 1, entity_id: 1 });
db.sources.createIndex({ tenant_id: 1, created_at: -1 });

// Collection: claims (USE pipeline - verified claims)
db.claims.createIndex({ tenant_id: 1, answer_id: 1 });
db.claims.createIndex({ tenant_id: 1, source_ids: 1 });
db.claims.createIndex({ tenant_id: 1, urs: -1 });

// Collection: query_audit (USE pipeline - audit trail)
db.query_audit.createIndex({ tenant_id: 1, created_at: -1 });
db.query_audit.createIndex({ tenant_id: 1, intent: 1 });

// Collection: natan_chat_messages (chat history)
db.natan_chat_messages.createIndex({ tenant_id: 1, user_id: 1 });
db.natan_chat_messages.createIndex({ tenant_id: 1, conversation_id: 1 });
db.natan_chat_messages.createIndex({ tenant_id: 1, created_at: -1 });

// Collection: ai_logs (AI operation logs)
db.ai_logs.createIndex({ tenant_id: 1, created_at: -1 });
db.ai_logs.createIndex({ tenant_id: 1, event: 1 });

// Collection: analytics (aggregated metrics)
db.analytics.createIndex({ tenant_id: 1, date: -1 }, { unique: true });

print("MongoDB initialization completed: collections and indexes created");

