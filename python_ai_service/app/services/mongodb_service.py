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
    
    _connected = False
    
    @classmethod
    def get_client(cls) -> Optional[MongoClient]:
        """Get or create MongoDB client (singleton)"""
        if cls._client is None:
            try:
                cls._client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=5000)
                # Test connection
                cls._client.admin.command('ping')
                logger.info("MongoDB connection established")
                cls._connected = True
            except Exception as e:
                logger.warning(f"MongoDB connection failed: {e}. Service will run without vector search.")
                cls._connected = False
                cls._client = None
                return None
        return cls._client
    
    @classmethod
    def is_connected(cls) -> bool:
        """Check if MongoDB is connected"""
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
        """Insert document into collection (returns None if MongoDB unavailable)"""
        if not cls.is_connected():
            logger.debug(f"MongoDB not available, skipping insert into {collection_name}")
            return None
        
        try:
            collection = cls.get_collection(collection_name)
            if collection is None:
                return None
            result = collection.insert_one(document)
            return str(result.inserted_id)
        except Exception as e:
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





