#!/usr/bin/env python3
"""
Create MongoDB indexes for optimal performance
Creates indexes for multi-tenancy and common query patterns
"""

import sys
import os
from pathlib import Path

# Add parent directory to path to import app modules
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_DATABASE

# Colors
GREEN = '\033[0;32m'
RED = '\033[0;31m'
YELLOW = '\033[1;33m'
BLUE = '\033[0;34m'
CYAN = '\033[0;36m'
NC = '\033[0m'

def create_indexes():
    """Create indexes for optimal MongoDB performance"""
    print(f"{BLUE}{'='*60}{NC}")
    print(f"{BLUE}📊 MongoDB Index Creation{NC}")
    print(f"{BLUE}{'='*60}{NC}\n")
    
    if not MongoDBService.is_connected():
        print(f"{RED}❌ MongoDB not connected!{NC}")
        return False
    
    collection = MongoDBService.get_collection("documents")
    if collection is None:
        print(f"{RED}❌ Collection 'documents' not accessible!{NC}")
        return False
    
    indexes_created = 0
    indexes_existing = 0
    
    # Index definitions
    index_definitions = [
        {
            "name": "tenant_id_created_at",
            "keys": [("tenant_id", 1), ("created_at", -1)],
            "description": "Multi-tenant queries with date sorting"
        },
        {
            "name": "tenant_id_scraper_id",
            "keys": [("tenant_id", 1), ("scraper_id", 1)],
            "description": "Multi-tenant queries filtered by scraper"
        },
        {
            "name": "tenant_id_document_id",
            "keys": [("tenant_id", 1), ("document_id", 1)],
            "description": "Multi-tenant queries by document ID"
        },
        {
            "name": "created_at",
            "keys": [("created_at", -1)],
            "description": "Date-based queries and sorting"
        },
        {
            "name": "tenant_id",
            "keys": [("tenant_id", 1)],
            "description": "Tenant isolation queries"
        }
    ]
    
    print(f"{CYAN}Creating indexes on collection 'documents'...{NC}\n")
    
    for index_def in index_definitions:
        try:
            index_name = index_def["name"]
            index_keys = index_def["keys"]
            description = index_def["description"]
            
            # Check if index already exists
            existing_indexes = list(collection.list_indexes())
            index_exists = any(
                idx.get("name") == index_name 
                for idx in existing_indexes
            )
            
            if index_exists:
                print(f"{YELLOW}⏭️  Index '{index_name}' already exists{NC}")
                indexes_existing += 1
            else:
                # Create index
                collection.create_index(index_keys, name=index_name)
                print(f"{GREEN}✅ Created index '{index_name}'{NC}")
                print(f"   {description}")
                print(f"   Keys: {index_keys}")
                indexes_created += 1
                
        except Exception as e:
            print(f"{RED}❌ Failed to create index '{index_def['name']}': {e}{NC}")
    
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}📊 Index Creation Summary{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    print(f"{GREEN}✅ Created: {indexes_created}{NC}")
    print(f"{YELLOW}⏭️  Existing: {indexes_existing}{NC}")
    print(f"{CYAN}📋 Total: {len(index_definitions)}{NC}\n")
    
    # List all indexes
    print(f"{CYAN}📋 All indexes on 'documents' collection:{NC}")
    all_indexes = list(collection.list_indexes())
    for idx in all_indexes:
        keys = idx.get("key", {})
        name = idx.get("name", "unnamed")
        print(f"  - {name}: {keys}")
    
    return indexes_created > 0 or indexes_existing > 0

if __name__ == "__main__":
    success = create_indexes()
    sys.exit(0 if success else 1)

