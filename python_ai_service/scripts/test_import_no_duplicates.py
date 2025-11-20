#!/usr/bin/env python3
"""
Test per verificare che l'import non crei duplicati.

Usage:
    python scripts/test_import_no_duplicates.py [--tenant-id TENANT_ID]
"""

import sys
import os
import asyncio
import json
from pathlib import Path
from collections import Counter

# Aggiungi il path del progetto
project_root = Path(__file__).parent.parent
sys.path.insert(0, str(project_root))

from app.services.pa_act_mongodb_importer import PAActMongoDBImporter
from app.services.mongodb_service import MongoDBService

async def test_import_no_duplicates(tenant_id: int = 2):
    """Test import per verificare che non crei duplicati"""
    print("=" * 80)
    print("🧪 TEST: Import senza duplicati")
    print("=" * 80)
    
    # Connetti a MongoDB
    MongoDBService.get_client()
    
    # Conta documenti prima
    db = MongoDBService.get_database()
    coll = db['documents'] if db is not None else None
    total_before = coll.count_documents({'tenant_id': tenant_id, 'document_type': 'pa_act'}) if coll is not None else 0
    
    print(f"\n📊 STATO INIZIALE:")
    print(f"   Tenant ID: {tenant_id}")
    print(f"   Documenti prima: {total_before}")
    
    # Trova file JSON
    json_dir = Path("storage/testing/compliance_scanner/json")
    if not json_dir.exists():
        # Prova nel container
        json_dir = Path("/app/storage/testing/compliance_scanner/json")
    
    json_files = list(json_dir.glob("atti_firenze_*.json"))
    
    if not json_files:
        print(f"\n❌ Nessun file JSON trovato in {json_dir}")
        print("   Creando atti di test artificiali...")
        
        # Crea atti di test
        test_atti = [
            {
                'numero': f'TEST_{i:05d}',
                'tipo': 'DD',
                'oggetto': f'Test atto {i} per verifica duplicati',
                'data': '2025-01-01',
                'anno': '2025'
            }
            for i in range(1, 6)  # 5 atti di test
        ]
    else:
        latest_file = max(json_files, key=lambda p: p.stat().st_mtime)
        print(f"\n📄 File JSON: {latest_file.name}")
        
        with open(latest_file, 'r', encoding='utf-8') as f:
            atti_list = json.load(f)
        
        print(f"   Atti nel file: {len(atti_list)}")
        
        # Prendi solo i primi 10 atti per test rapido
        test_atti = atti_list[:10]
        print(f"   Atti da importare (test): {len(test_atti)}")
    
    # Inizializza importer
    importer = PAActMongoDBImporter(tenant_id=tenant_id, dry_run=False)
    
    # Conta quanti sono già presenti
    existing_protocols = set()
    if MongoDBService.is_connected() and coll is not None:
        existing_protocols = set(
            doc.get('protocol_number', '')
            for doc in coll.find(
                {'tenant_id': tenant_id, 'document_type': 'pa_act'},
                {'protocol_number': 1}
            )
        )
    
    print(f"\n🔍 ANALISI:")
    already_present = [a for a in test_atti if a.get('numero', a.get('numero_atto', '')) in existing_protocols]
    new_atti = [a for a in test_atti if a.get('numero', a.get('numero_atto', '')) not in existing_protocols]
    
    print(f"   Atti già presenti: {len(already_present)}")
    print(f"   Atti nuovi: {len(new_atti)}")
    
    if already_present:
        print(f"   Esempi già presenti: {[a.get('numero', 'N/A') for a in already_present[:3]]}")
    
    # Import atti di test (PRIMA VOLTA)
    print(f"\n📥 IMPORT PRIMA VOLTA...")
    imported_1 = 0
    skipped_1 = 0
    
    for atto_dict in test_atti:
        protocol_num = atto_dict.get('numero', atto_dict.get('numero_atto', ''))
        
        atto_data = {
            'numero_atto': protocol_num,
            'tipo_atto': atto_dict.get('tipo', atto_dict.get('tipo_atto', '')),
            'oggetto': atto_dict.get('oggetto', atto_dict.get('descrizione', '')),
            'data_atto': atto_dict.get('data', atto_dict.get('data_pubblicazione', atto_dict.get('data_adozione', ''))),
            'anno': atto_dict.get('anno', ''),
            'scraper_type': 'compliance_scanner',
            'comune_slug': 'firenze'
        }
        
        pdf_url = atto_dict.get('url_pdf') or atto_dict.get('pdf_url') or atto_dict.get('link')
        
        success = await importer.import_atto(
            atto_data=atto_data,
            pdf_url=pdf_url,
            ente='Firenze'
        )
        
        if success:
            imported_1 += 1
        else:
            skipped_1 += 1
    
    # Conta documenti dopo prima import
    total_after_1 = coll.count_documents({'tenant_id': tenant_id, 'document_type': 'pa_act'}) if coll is not None else 0
    
    print(f"   Importati: {imported_1}")
    print(f"   Saltati: {skipped_1}")
    print(f"   Documenti dopo prima import: {total_after_1}")
    print(f"   Incremento: {total_after_1 - total_before}")
    
    # IMPORT SECONDA VOLTA (dovrebbe saltare tutti)
    print(f"\n📥 IMPORT SECONDA VOLTA (test duplicati)...")
    imported_2 = 0
    skipped_2 = 0
    
    for atto_dict in test_atti:
        protocol_num = atto_dict.get('numero', atto_dict.get('numero_atto', ''))
        
        atto_data = {
            'numero_atto': protocol_num,
            'tipo_atto': atto_dict.get('tipo', atto_dict.get('tipo_atto', '')),
            'oggetto': atto_dict.get('oggetto', atto_dict.get('descrizione', '')),
            'data_atto': atto_dict.get('data', atto_dict.get('data_pubblicazione', atto_dict.get('data_adozione', ''))),
            'anno': atto_dict.get('anno', ''),
            'scraper_type': 'compliance_scanner',
            'comune_slug': 'firenze'
        }
        
        pdf_url = atto_dict.get('url_pdf') or atto_dict.get('pdf_url') or atto_dict.get('link')
        
        success = await importer.import_atto(
            atto_data=atto_data,
            pdf_url=pdf_url,
            ente='Firenze'
        )
        
        if success:
            imported_2 += 1
        else:
            skipped_2 += 1
    
    # Conta documenti dopo seconda import
    total_after_2 = coll.count_documents({'tenant_id': tenant_id, 'document_type': 'pa_act'}) if coll is not None else 0
    
    print(f"   Importati: {imported_2}")
    print(f"   Saltati: {skipped_2}")
    print(f"   Documenti dopo seconda import: {total_after_2}")
    print(f"   Incremento rispetto a prima: {total_after_2 - total_after_1}")
    
    # Verifica duplicati
    print(f"\n🔍 VERIFICA DUPLICATI:")
    all_protocols = coll.distinct('protocol_number', {'tenant_id': tenant_id, 'document_type': 'pa_act'}) if coll is not None else []
    total_docs = coll.count_documents({'tenant_id': tenant_id, 'document_type': 'pa_act'}) if coll is not None else 0
    
    print(f"   Protocol numbers unici: {len(all_protocols)}")
    print(f"   Documenti totali: {total_docs}")
    
    # Conta duplicati
    protocols_list = [doc.get('protocol_number', '') for doc in coll.find({'tenant_id': tenant_id, 'document_type': 'pa_act'}, {'protocol_number': 1})] if coll is not None else []
    duplicates = {k: v for k, v in Counter(protocols_list).items() if v > 1}
    
    print(f"\n📊 RISULTATI FINALI:")
    print(f"   Documenti prima: {total_before}")
    print(f"   Documenti dopo: {total_docs}")
    print(f"   Incremento totale: {total_docs - total_before}")
    print(f"   Duplicati trovati: {len(duplicates)}")
    
    if len(all_protocols) == total_docs:
        print(f"\n✅ TEST PASSATO: NESSUN DUPLICATO!")
        print(f"   ✅ Tutti i documenti sono unici")
        if total_after_2 == total_after_1:
            print(f"   ✅ Seconda import non ha creato nuovi documenti (corretto!)")
        else:
            print(f"   ⚠️  Seconda import ha creato {total_after_2 - total_after_1} nuovi documenti")
    else:
        print(f"\n❌ TEST FALLITO: DUPLICATI TROVATI!")
        print(f"   ❌ {total_docs - len(all_protocols)} duplicati trovati")
        if duplicates:
            print(f"   Primi 5 duplicati: {dict(list(duplicates.items())[:5])}")
    
    print("\n" + "=" * 80)
    
    return len(all_protocols) == total_docs and total_after_2 == total_after_1


if __name__ == '__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='Test import senza duplicati')
    parser.add_argument('--tenant-id', type=int, default=2, help='ID tenant')
    
    args = parser.parse_args()
    
    try:
        result = asyncio.run(test_import_no_duplicates(tenant_id=args.tenant_id))
        sys.exit(0 if result else 1)
    except KeyboardInterrupt:
        print("\n\n⚠️  Test interrotto dall'utente")
        sys.exit(1)
    except Exception as e:
        print(f"\n\n❌ ERRORE: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

