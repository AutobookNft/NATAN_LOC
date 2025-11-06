#!/usr/bin/env python3
"""
Script per ottenere un documento da MongoDB per document_id
Usato da MongoDocumentController per visualizzare documenti
"""

import sys
import json
import argparse
from pathlib import Path

# Aggiungi path per importare moduli
NATAN_LOC_ROOT = Path(__file__).parent.parent.parent.parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from dotenv import load_dotenv
load_dotenv(NATAN_LOC_ROOT / "python_ai_service" / ".env", override=True)

from app.services.mongodb_service import MongoDBService


def get_document_by_id(document_id: str, tenant_id: int) -> dict:
    """
    Ottieni documento da MongoDB per document_id
    
    Args:
        document_id: Document ID
        tenant_id: Tenant ID
    
    Returns:
        Dict con documento o None se non trovato
    """
    if not MongoDBService.is_connected():
        return None
    
    collection = MongoDBService.get_collection('documents')
    if collection is None:
        return None
    
    # Cerca documento per document_id e tenant_id
    documents = MongoDBService.find_documents('documents', {
        'document_id': document_id,
        'tenant_id': tenant_id
    }, limit=1)
    
    if not documents:
        return None
    
    doc = documents[0]
    
    # Converti ObjectId e datetime in stringhe per JSON
    if '_id' in doc:
        doc['_id'] = str(doc['_id'])
    
    # Converti datetime in ISO string
    if 'created_at' in doc and doc['created_at']:
        if hasattr(doc['created_at'], 'isoformat'):
            doc['created_at'] = doc['created_at'].isoformat()
    
    if 'updated_at' in doc and doc['updated_at']:
        if hasattr(doc['updated_at'], 'isoformat'):
            doc['updated_at'] = doc['updated_at'].isoformat()
    
    return doc


def main():
    parser = argparse.ArgumentParser(description='Get MongoDB document by ID')
    parser.add_argument('--document-id', type=str, required=True, help='Document ID')
    parser.add_argument('--tenant-id', type=int, required=True, help='Tenant ID')
    
    args = parser.parse_args()
    
    try:
        doc = get_document_by_id(args.document_id, args.tenant_id)
        if doc:
            print(json.dumps(doc, indent=2, default=str))
        else:
            print(json.dumps({'error': 'Document not found'}), file=sys.stderr)
            sys.exit(1)
    except Exception as e:
        print(json.dumps({
            'error': str(e)
        }), file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()

