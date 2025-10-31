"""MongoDB service for document storage and retrieval"""
from pymongo import MongoClient
from pymongo.collection import Collection
from typing import Dict, Any, List, Optional
from app.config import MONGODB_URI, MONGODB_DATABASE
import logging

logger = logging.getLogger(__name__)

class MongoDBService:
    """MongoDB connection and operations service"""
    
    _client: Optional[MongoClient] = None
    _db = None
    
    @classmethod
    def get_client(cls) -> MongoClient:
        """Get or create MongoDB client (singleton)"""
        if cls._client is None:
            cls._client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=5000)
            # Test connection
            try:
                cls._client.admin.command('ping')
                logger.info("MongoDB connection established")
            except Exception as e:
                logger.error(f"MongoDB connection failed: {e}")
                raise
        return cls._client
    
    @classmethod
    def get_database(cls):
        """Get database instance"""
        if cls._db is None:
            client = cls.get_client()
            cls._db = client[MONGODB_DATABASE]
        return cls._db
    
    @classmethod
    def get_collection(cls, collection_name: str) -> Collection:
        """Get collection instance"""
        db = cls.get_database()
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
    def insert_document(cls, collection_name: str, document: Dict[str, Any]) -> str:
        """Insert document into collection"""
        collection = cls.get_collection(collection_name)
        result = collection.insert_one(document)
        return str(result.inserted_id)
    
    @classmethod
    def find_documents(
        cls,
        collection_name: str,
        filter: Dict[str, Any],
        limit: Optional[int] = None,
        sort: Optional[List[tuple]] = None
    ) -> List[Dict[str, Any]]:
        """Find documents in collection"""
        collection = cls.get_collection(collection_name)
        query = collection.find(filter)
        
        if sort:
            query = query.sort(sort)
        
        if limit:
            query = query.limit(limit)
        
        return list(query)
    
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

