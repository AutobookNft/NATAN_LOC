#!/usr/bin/env python3
"""
Script to count documents for Firenze tenant in MongoDB Atlas
"""

import sys
import os
from pathlib import Path

# Add parent directory to path to import app modules
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_DATABASE

def count_firenze_documents():
    """Count documents for Firenze tenant"""
    print("🔍 Counting documents for Firenze tenant")
    print("=" * 50)

    try:
        # Test connection
        if not MongoDBService.is_connected():
            print("❌ Cannot connect to MongoDB")
            return

        db = MongoDBService.get_database()
        if db is None:
            print("❌ Cannot access database")
            return

        # Get all collections
        collections = db.list_collection_names()
        print(f"📊 Available collections: {collections}")

        # Check if 'documents' collection exists
        if 'documents' not in collections:
            print("❌ No 'documents' collection found")
            print("Available collections:", collections)
            return

        documents_collection = db['documents']

        # Count total documents
        total_docs = documents_collection.count_documents({})
        print(f"\n📊 Total documents in database: {total_docs}")

        # Find unique tenant_ids
        tenant_ids = documents_collection.distinct('tenant_id')
        print(f"📋 Unique tenant_ids found: {tenant_ids}")

        # Try different possible tenant identifiers for Firenze
        firenze_identifiers = [
            'firenze',
            'FIRENZE',
            'comune_firenze',
            'COMUNE_FIRENZE',
            1,  # Common default ID
            '1'  # String version
        ]

        print(f"\n🏛️ Checking documents for Firenze (various identifiers):")
        for identifier in firenze_identifiers:
            count = documents_collection.count_documents({'tenant_id': identifier})
            if count > 0:
                print(f"  ✅ tenant_id '{identifier}': {count} documents")

        # Also check for partial matches in case of different naming
        print(f"\n🔍 Checking for Firenze-related tenant_ids:")
        for tenant_id in tenant_ids:
            if isinstance(tenant_id, str) and 'firenze' in tenant_id.lower():
                count = documents_collection.count_documents({'tenant_id': tenant_id})
                print(f"  🎯 tenant_id '{tenant_id}': {count} documents")

        # Show a sample document to understand the structure
        sample_doc = documents_collection.find_one()
        if sample_doc:
            print(f"\n📄 Sample document structure:")
            print(f"  Keys: {list(sample_doc.keys())}")
            print(f"  tenant_id: {sample_doc.get('tenant_id', 'N/A')}")
            print(f"  created_at: {sample_doc.get('created_at', 'N/A')}")

    except Exception as e:
        print(f"❌ Error: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    count_firenze_documents()
