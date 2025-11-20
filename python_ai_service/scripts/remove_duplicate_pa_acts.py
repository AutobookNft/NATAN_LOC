#!/usr/bin/env python3
"""
Script per rimuovere duplicati di atti PA in MongoDB Atlas.

Mantiene solo il documento più recente per ogni protocol_number + tenant_id.
Elimina tutti gli altri duplicati.

Usage:
    python scripts/remove_duplicate_pa_acts.py [--tenant-id TENANT_ID] [--dry-run]
"""

import sys
import os
from pathlib import Path
from collections import defaultdict
from datetime import datetime

# Aggiungi il path del progetto
project_root = Path(__file__).parent.parent
sys.path.insert(0, str(project_root))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_URI

def find_duplicates(tenant_id: int = None, dry_run: bool = True):
    """
    Trova e rimuove duplicati per protocol_number + tenant_id.
    
    Args:
        tenant_id: ID tenant (opzionale, se None cerca in tutti i tenant)
        dry_run: Se True, solo mostra cosa verrebbe eliminato senza eliminare
    """
    print("=" * 80)
    print("🔍 RIMOZIONE DUPLICATI ATTI PA")
    print("=" * 80)
    print(f"Database: MongoDB Atlas")
    print(f"URI: {MONGODB_URI.split('@')[1] if '@' in MONGODB_URI else 'N/A'}")
    print(f"Tenant ID: {tenant_id if tenant_id else 'Tutti'}")
    print(f"Dry Run: {'✅ SÌ (solo simulazione)' if dry_run else '❌ NO (eliminazione reale)'}")
    print("=" * 80)
    print()
    
    # Connetti a MongoDB
    if not MongoDBService.is_connected():
        MongoDBService.get_client()
    
    if not MongoDBService.is_connected():
        print("❌ ERRORE: Impossibile connettersi a MongoDB")
        return False
    
    db = MongoDBService.get_database()
    if db is None:
        print("❌ ERRORE: Database non disponibile")
        return False
    
    coll = db['documents']
    
    # Query per trovare tutti i documenti PA
    query = {'document_type': 'pa_act'}
    if tenant_id is not None:
        query['tenant_id'] = tenant_id
    
    print(f"📊 Analizzando documenti PA...")
    all_docs = list(coll.find(query, {
        '_id': 1,
        'document_id': 1,
        'protocol_number': 1,
        'tenant_id': 1,
        'created_at': 1,
        'title': 1
    }))
    
    total_docs = len(all_docs)
    print(f"   Totale documenti trovati: {total_docs}")
    print()
    
    if total_docs == 0:
        print("✅ Nessun documento da analizzare")
        return True
    
    # Raggruppa per protocol_number + tenant_id
    print("📋 Raggruppando per protocol_number + tenant_id...")
    groups = defaultdict(list)
    
    for doc in all_docs:
        protocol_num = doc.get('protocol_number', '')
        tenant = doc.get('tenant_id', 0)
        key = (protocol_num, tenant)
        
        if protocol_num:  # Solo se ha protocol_number
            groups[key].append(doc)
    
    print(f"   Gruppi unici trovati: {len(groups)}")
    print()
    
    # Trova duplicati (gruppi con più di 1 documento)
    duplicates = {k: v for k, v in groups.items() if len(v) > 1}
    unique_count = len(groups) - len(duplicates)
    
    print("📊 STATISTICHE:")
    print(f"   Documenti unici: {unique_count}")
    print(f"   Gruppi con duplicati: {len(duplicates)}")
    
    total_duplicates = sum(len(v) - 1 for v in duplicates.values())
    print(f"   Documenti duplicati da eliminare: {total_duplicates}")
    print(f"   Documenti che rimarranno: {total_docs - total_duplicates}")
    print()
    
    if len(duplicates) == 0:
        print("✅ Nessun duplicato trovato!")
        return True
    
    # Mostra alcuni esempi
    print("📄 ESEMPI DUPLICATI (primi 5):")
    for i, ((protocol_num, tenant), docs) in enumerate(list(duplicates.items())[:5]):
        print(f"\n   {i+1}. Protocol: {protocol_num} (Tenant: {tenant})")
        print(f"      Duplicati: {len(docs)}")
        # Ordina per created_at (più recente prima)
        sorted_docs = sorted(docs, key=lambda x: x.get('created_at', datetime.min), reverse=True)
        print(f"      Mantiene: {sorted_docs[0].get('_id')} (created: {sorted_docs[0].get('created_at', 'N/A')})")
        print(f"      Elimina: {len(sorted_docs) - 1} documenti")
    print()
    
    if dry_run:
        print("⚠️  DRY RUN: Nessun documento verrà eliminato")
        print("   Esegui senza --dry-run per eliminare i duplicati")
        return True
    
    # Elimina duplicati (mantieni solo il più recente)
    print("🗑️  Eliminando duplicati...")
    deleted_count = 0
    errors = []
    
    for (protocol_num, tenant), docs in duplicates.items():
        # Ordina per created_at (più recente prima)
        sorted_docs = sorted(docs, key=lambda x: x.get('created_at', datetime.min), reverse=True)
        
        # Mantieni il primo (più recente)
        keep_id = sorted_docs[0]['_id']
        
        # Elimina gli altri
        for doc in sorted_docs[1:]:
            try:
                result = coll.delete_one({'_id': doc['_id']})
                if result.deleted_count > 0:
                    deleted_count += 1
                else:
                    errors.append(f"Documento {doc['_id']} non eliminato")
            except Exception as e:
                errors.append(f"Errore eliminazione {doc['_id']}: {e}")
    
    print()
    print("=" * 80)
    print("✅ RIMOZIONE COMPLETATA")
    print("=" * 80)
    print(f"   Documenti eliminati: {deleted_count}")
    print(f"   Errori: {len(errors)}")
    
    if errors:
        print("\n⚠️  ERRORI:")
        for error in errors[:10]:
            print(f"   - {error}")
        if len(errors) > 10:
            print(f"   ... e altri {len(errors) - 10} errori")
    
    # Verifica finale
    print("\n🔍 Verifica finale...")
    remaining_docs = coll.count_documents(query)
    print(f"   Documenti rimanenti: {remaining_docs}")
    print(f"   Attesi: {total_docs - total_duplicates}")
    
    if remaining_docs == (total_docs - total_duplicates):
        print("✅ Verifica OK: numero documenti corretto")
    else:
        print(f"⚠️  Discrepanza: {remaining_docs} vs {total_docs - total_duplicates} attesi")
    
    return True


if __name__ == '__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='Rimuove duplicati atti PA da MongoDB')
    parser.add_argument('--tenant-id', type=int, default=None, help='ID tenant (opzionale)')
    parser.add_argument('--dry-run', action='store_true', help='Solo simulazione, non elimina')
    
    args = parser.parse_args()
    
    try:
        success = find_duplicates(tenant_id=args.tenant_id, dry_run=args.dry_run)
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n\n⚠️  Operazione interrotta dall'utente")
        sys.exit(1)
    except Exception as e:
        print(f"\n\n❌ ERRORE: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

