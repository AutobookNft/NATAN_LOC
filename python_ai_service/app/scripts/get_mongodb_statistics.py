#!/usr/bin/env python3
"""
Script per ottenere statistiche documenti da MongoDB
Usato da StatisticsController per dashboard statistiche
"""

import sys
import json
import argparse
from pathlib import Path
from datetime import datetime
from collections import defaultdict

# Aggiungi path per importare moduli
NATAN_LOC_ROOT = Path(__file__).parent.parent.parent.parent
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from dotenv import load_dotenv
load_dotenv(NATAN_LOC_ROOT / "python_ai_service" / ".env", override=True)

from app.services.mongodb_service import MongoDBService


def get_documents_statistics(tenant_id: int, year: int = None) -> dict:
    """
    Ottieni statistiche documenti da MongoDB
    
    Args:
        tenant_id: Tenant ID
        year: Anno opzionale per filtrare (None = tutti gli anni)
    
    Returns:
        Dict con statistiche:
        - total: int
        - by_type: dict {document_type: count}
        - by_month: dict {YYYY-MM: count}
    """
    if not MongoDBService.is_connected():
        return {
            'total': 0,
            'by_type': {},
            'by_month': {}
        }
    
    collection = MongoDBService.get_collection('documents')
    if collection is None:
        return {
            'total': 0,
            'by_type': {},
            'by_month': {}
        }
    
    # Build filter
    filter_query = {
        'tenant_id': tenant_id,
        'document_type': 'pa_act'
    }
    
    # Get all documents
    documents = list(collection.find(filter_query))
    
    # Calculate statistics
    total = len(documents)
    by_type = defaultdict(int)
    by_month = defaultdict(int)
    
    for doc in documents:
        # Count by type
        doc_type = doc.get('document_type', 'unknown')
        by_type[doc_type] += 1
        
        # Count by month
        created_at = doc.get('created_at')
        if created_at:
            if isinstance(created_at, str):
                try:
                    created_at = datetime.fromisoformat(created_at.replace('Z', '+00:00'))
                except:
                    pass
            if isinstance(created_at, datetime):
                if year is None or created_at.year == year:
                    month_key = f"{created_at.year}-{created_at.month:02d}"
                    by_month[month_key] += 1
    
    return {
        'total': total,
        'by_type': dict(by_type),
        'by_month': dict(by_month)
    }


def main():
    parser = argparse.ArgumentParser(description='Get MongoDB documents statistics')
    parser.add_argument('--tenant-id', type=int, required=True, help='Tenant ID')
    parser.add_argument('--year', type=int, default=None, help='Filter by year (optional)')
    
    args = parser.parse_args()
    
    try:
        stats = get_documents_statistics(args.tenant_id, args.year)
        print(json.dumps(stats, indent=2))
    except Exception as e:
        print(json.dumps({
            'error': str(e),
            'total': 0,
            'by_type': {},
            'by_month': {}
        }), file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()


