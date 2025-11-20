"""MongoDB service for document storage and retrieval"""
from pymongo import MongoClient
from pymongo.collection import Collection
from typing import Dict, Any, List, Optional
from app.config import (
    MONGODB_URI,
    MONGODB_DATABASE,
    MONGODB_HOST,
    MONGODB_PORT,
    MONGODB_USERNAME,
    MONGODB_PASSWORD,
)
import os
import logging

# Import certifi for MongoDB Atlas SSL support
try:
    import certifi
    CERTIFI_AVAILABLE = True
except ImportError:
    CERTIFI_AVAILABLE = False
    certifi = None

logger = logging.getLogger(__name__)

class MongoDBService:
    """MongoDB connection and operations service"""
    
    _client: Optional[MongoClient] = None
    _db = None
    
    _connected = False
    
    @classmethod
    def get_client(cls) -> Optional[MongoClient]:
        """Get or create MongoDB client (singleton)"""
        if cls._client is None:
            try:
                # Support SSL for MongoDB Atlas
                if "mongodb.net" in MONGODB_URI or "mongodb+srv" in MONGODB_URI:
                    # MongoDB Atlas connection with SSL
                    if CERTIFI_AVAILABLE:
                        cls._client = MongoClient(
                            MONGODB_URI,
                            tls=True,
                            tlsCAFile=certifi.where(),  # Use standard CA certificates
                            serverSelectionTimeoutMS=5000
                        )
                    else:
                        # Fallback: SSL without certifi (less secure)
                        logger.warning("certifi not available, using SSL without CA verification")
                        cls._client = MongoClient(
                            MONGODB_URI,
                            tls=True,
                            tlsAllowInvalidCertificates=False,  # Still validate, just without certifi
                            serverSelectionTimeoutMS=5000
                        )
                else:
                    # Standard MongoDB connection (local or DocumentDB)
                    cls._client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=5000)
                
                # Test connection
                cls._client.admin.command('ping')
                logger.info("MongoDB connection established", {
                    'host': MONGODB_HOST,
                    'database': MONGODB_DATABASE,
                    'log_category': 'MONGODB_CONNECTION_SUCCESS'
                })
                cls._connected = True
            except Exception as e:
                logger.warning("MongoDB connection failed", {
                    'host': MONGODB_HOST,
                    'error': str(e),
                    'log_category': 'MONGODB_CONNECTION_ERROR'
                })
                logger.warning(f"MongoDB connection failed: {e}. Service will run without vector search.")

                # Fallback: if running outside Docker, try localhost/127.0.0.1
                fallback_hosts = {"mongodb", "natan_mongodb", "mongo"}
                if MONGODB_HOST in fallback_hosts:
                    try:
                        logger.info("Attempting MongoDB fallback connection on 127.0.0.1")
                        if MONGODB_PASSWORD:
                            fallback_uri = (
                                f"mongodb://{MONGODB_USERNAME}:{MONGODB_PASSWORD}"
                                f"@127.0.0.1:{MONGODB_PORT}/{MONGODB_DATABASE}?authSource=admin"
                            )
                        else:
                            fallback_uri = f"mongodb://127.0.0.1:{MONGODB_PORT}/{MONGODB_DATABASE}"

                        cls._client = MongoClient(fallback_uri, serverSelectionTimeoutMS=5000)
                        cls._client.admin.command("ping")
                        cls._connected = True
                        os.environ["MONGO_DB_HOST"] = "127.0.0.1"
                        logger.info("MongoDB fallback connection established on 127.0.0.1")
                        return cls._client
                    except Exception as fallback_error:
                        logger.error(f"MongoDB fallback connection failed: {fallback_error}")

                cls._connected = False
                cls._client = None
                return None
        return cls._client
    
    @classmethod
    def is_connected(cls) -> bool:
        """Check if MongoDB is connected"""
        # Se client non esiste, prova a crearlo
        if cls._client is None:
            cls.get_client()
        if cls._client is None:
            return False
        try:
            cls._client.admin.command('ping')
            cls._connected = True
            return True
        except Exception:
            cls._connected = False
            return False
    
    @classmethod
    def get_database(cls):
        """Get database instance"""
        if cls._db is None:
            client = cls.get_client()
            if client is None:
                return None
            cls._db = client[MONGODB_DATABASE]
        return cls._db
    
    @classmethod
    def get_collection(cls, collection_name: str) -> Optional[Collection]:
        """Get collection instance"""
        db = cls.get_database()
        if db is None:
            return None
        return db[collection_name]
    
    @classmethod
    def close(cls):
        """Close MongoDB connection"""
        if cls._client:
            cls._client.close()
            cls._client = None
            cls._db = None
            logger.info("MongoDB connection closed")
    
    @classmethod
    def insert_document(cls, collection_name: str, document: Dict[str, Any]) -> Optional[str]:
        """
        Insert document into collection (returns None if MongoDB unavailable)
        Returns 'duplicate' if document already exists (E11000 error or found by document_id)
        
        CRITICAL: Checks for existing document by document_id BEFORE insert to prevent duplicates
        """
        if not cls.is_connected():
            logger.debug(f"MongoDB not available, skipping insert into {collection_name}")
            return None
        
        try:
            collection = cls.get_collection(collection_name)
            if collection is None:
                return None
            
            # CRITICAL: Check if document already exists by document_id BEFORE insert
            # This prevents duplicates even if unique index is missing
            document_id = document.get('document_id')
            tenant_id = document.get('tenant_id')
            
            if document_id:
                existing = collection.find_one({
                    "document_id": document_id,
                    "tenant_id": tenant_id
                })
                
                if existing:
                    logger.debug(f"MongoDB document already exists (pre-check): {document_id}")
                    return 'duplicate'  # Return duplicate BEFORE attempting insert
            
            result = collection.insert_one(document)
            return str(result.inserted_id)
        except Exception as e:
            error_msg = str(e)
            # E11000 = duplicate key error (document already exists)
            if 'E11000' in error_msg or 'duplicate key' in error_msg.lower():
                logger.debug(f"MongoDB document already exists in {collection_name}: {document.get('document_id', 'unknown')}")
                return 'duplicate'  # Special return value for duplicates
            logger.warning(f"MongoDB insert failed for {collection_name}: {e}")
            return None
    
    @classmethod
    def find_documents(
        cls,
        collection_name: str,
        filter: Dict[str, Any],
        limit: Optional[int] = None,
        sort: Optional[List[tuple]] = None
    ) -> List[Dict[str, Any]]:
        """Find documents in collection (returns empty list if MongoDB unavailable)"""
        if not cls.is_connected():
            logger.debug(f"MongoDB not available, returning empty list for {collection_name}")
            return []
        
        try:
            collection = cls.get_collection(collection_name)
            if collection is None:
                return []
            query = collection.find(filter)
            
            if sort:
                query = query.sort(sort)
            
            if limit:
                query = query.limit(limit)
            
            return list(query)
        except Exception as e:
            logger.warning(f"MongoDB query failed for {collection_name}: {e}")
            return []
    
    @classmethod
    def update_document(
        cls,
        collection_name: str,
        filter: Dict[str, Any],
        update: Dict[str, Any]
    ) -> int:
        """Update documents in collection"""
        collection = cls.get_collection(collection_name)
        result = collection.update_many(filter, {"$set": update})
        return result.modified_count
    
    @classmethod
    def delete_documents(cls, collection_name: str, filter: Dict[str, Any]) -> int:
        """Delete documents from collection"""
        collection = cls.get_collection(collection_name)
        result = collection.delete_many(filter)
        return result.deleted_count
    
    @classmethod
    def count_documents(cls, collection_name: str, filter: Dict[str, Any]) -> int:
        """
        Count documents in collection matching filter
        
        Args:
            collection_name: MongoDB collection name
            filter: Query filter dict
        
        Returns:
            Count of matching documents (0 if MongoDB unavailable or error)
        """
        if not cls.is_connected():
            logger.debug(f"MongoDB not available, returning 0 for count in {collection_name}")
            return 0
        
        try:
            collection = cls.get_collection(collection_name)
            if collection is None:
                return 0
            return collection.count_documents(filter)
        except Exception as e:
            logger.warning(f"MongoDB count failed for {collection_name}: {e}")
            return 0





