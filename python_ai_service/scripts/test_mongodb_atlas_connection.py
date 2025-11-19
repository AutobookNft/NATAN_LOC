#!/usr/bin/env python3
"""
Test script for MongoDB Atlas connection
Tests connection to MongoDB Atlas and verifies configuration
"""

import sys
import os
from pathlib import Path

# Add parent directory to path to import app modules
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_URI, MONGODB_HOST, MONGODB_DATABASE

def test_connection():
    """Test MongoDB Atlas connection"""
    print("🔍 Testing MongoDB Atlas Connection")
    print("=" * 50)
    
    # Display configuration
    print(f"\n📋 Configuration:")
    print(f"  Host: {MONGODB_HOST}")
    print(f"  Database: {MONGODB_DATABASE}")
    print(f"  URI: {MONGODB_URI[:50]}..." if len(MONGODB_URI) > 50 else f"  URI: {MONGODB_URI}")
    
    # Test connection
    print(f"\n🔌 Testing connection...")
    try:
        is_connected = MongoDBService.is_connected()
        
        if is_connected:
            print("✅ MongoDB connection successful!")
            
            # Get database info
            db = MongoDBService.get_database()
            if db is not None:
                # List collections
                collections = db.list_collection_names()
                print(f"\n📊 Database Info:")
                print(f"  Collections: {len(collections)}")
                if collections:
                    print(f"  Collection names: {', '.join(collections[:5])}")
                    if len(collections) > 5:
                        print(f"  ... and {len(collections) - 5} more")
                
                # Test basic operation
                test_collection = MongoDBService.get_collection("_test_connection")
                if test_collection is not None:
                    # Insert test document
                    test_doc = {"test": True, "timestamp": "2025-01-28"}
                    result = test_collection.insert_one(test_doc)
                    print(f"\n✅ Test document inserted: {result.inserted_id}")
                    
                    # Delete test document
                    test_collection.delete_one({"_id": result.inserted_id})
                    print("✅ Test document deleted")
            
            return True
        else:
            print("❌ MongoDB connection failed!")
            print("\n💡 Troubleshooting:")
            print("  1. Check MONGODB_URI in .env file")
            print("  2. Verify IP whitelist in MongoDB Atlas")
            print("  3. Check username/password")
            print("  4. Verify network connectivity")
            return False
            
    except Exception as e:
        print(f"❌ Error: {e}")
        print("\n💡 Troubleshooting:")
        print("  1. Check MONGODB_URI in .env file")
        print("  2. Verify IP whitelist in MongoDB Atlas")
        print("  3. Check username/password")
        print("  4. Verify network connectivity")
        print(f"  5. Error details: {type(e).__name__}: {str(e)}")
        return False

if __name__ == "__main__":
    success = test_connection()
    sys.exit(0 if success else 1)

