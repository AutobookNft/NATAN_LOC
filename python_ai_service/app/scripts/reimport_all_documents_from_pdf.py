#!/usr/bin/env python3
"""
Script per re-importare TUTTI i documenti da MongoDB estraendo il testo completo dal PDF
Usato per aggiornare documenti che hanno solo l'oggetto invece del testo completo
"""

import sys
import json
import argparse
import asyncio
from pathlib import Path
from dotenv import load_dotenv

# Add the parent directory of python_ai_service to sys.path
NATAN_LOC_ROOT = Path(__file__).resolve().parents[3]
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from app.services.mongodb_service import MongoDBService
from app.services.pa_act_mongodb_importer import PAActMongoDBImporter

# Load environment variables from .env file
env_path = NATAN_LOC_ROOT / "python_ai_service" / ".env"
if env_path.exists():
    load_dotenv(env_path, override=True)


async def reimport_all_documents(tenant_id: int, min_chars: int = 1000, force: bool = False, limit: int = None):
    """
    Re-importa tutti i documenti che hanno testo incompleto (meno di min_chars caratteri)
    
    Args:
        tenant_id: Tenant ID
        min_chars: Soglia minima caratteri per considerare un documento "completo"
        force: Se True, re-importa anche documenti con testo completo
        limit: Limite massimo documenti da processare (None = tutti)
    """
    if not MongoDBService.is_connected():
        print("❌ MongoDB non connesso")
        return False
    
    collection = MongoDBService.get_collection("documents")
    if collection is None:
        print("❌ Collection 'documents' non trovata")
        return False
    
    # Trova tutti i documenti del tenant
    filter_query = {
        "tenant_id": tenant_id,
        "document_type": "pa_act"
    }
    
    all_documents = MongoDBService.find_documents("documents", filter_query, limit=None)
    
    if not all_documents:
        print(f"❌ Nessun documento trovato per tenant_id {tenant_id}")
        return False
    
    print(f"📊 Trovati {len(all_documents)} documenti totali")
    
    # Filtra documenti che necessitano re-importazione
    documents_to_reimport = []
    for doc in all_documents:
        full_text = doc.get("content", {}).get("full_text", "")
        current_chars = len(full_text)
        pdf_url = doc.get("metadata", {}).get("pdf_url") or doc.get("pdf_url")
        
        # Se non ha PDF URL, salta
        if not pdf_url:
            continue
        
        # Se ha già testo completo e non force, salta
        if current_chars >= min_chars and not force:
            continue
        
        documents_to_reimport.append({
            "doc": doc,
            "current_chars": current_chars,
            "pdf_url": pdf_url
        })
    
    print(f"🔄 Documenti da re-importare: {len(documents_to_reimport)}")
    
    if limit:
        documents_to_reimport = documents_to_reimport[:limit]
        print(f"📌 Limite applicato: processerò solo i primi {limit} documenti")
    
    if not documents_to_reimport:
        print("✅ Nessun documento da re-importare")
        return True
    
    # Crea importer
    importer = PAActMongoDBImporter(tenant_id=tenant_id, dry_run=False)
    
    # Statistiche
    stats = {
        "total": len(documents_to_reimport),
        "success": 0,
        "failed": 0,
        "skipped": 0,
        "total_chars_before": 0,
        "total_chars_after": 0
    }
    
    # Processa ogni documento
    for i, item in enumerate(documents_to_reimport, 1):
        doc = item["doc"]
        document_id = doc.get("document_id")
        current_chars = item["current_chars"]
        pdf_url = item["pdf_url"]
        
        print(f"\n[{i}/{len(documents_to_reimport)}] 📄 {document_id}")
        print(f"   Titolo: {doc.get('title', 'N/A')[:60]}")
        print(f"   Caratteri attuali: {current_chars}")
        print(f"   PDF URL: {pdf_url[:80]}...")
        
        try:
            # Prepara atto_data dal documento esistente
            atto_data = {
                "numero_atto": doc.get("protocol_number") or doc.get("metadata", {}).get("numero_atto", ""),
                "tipo_atto": doc.get("metadata", {}).get("tipo_atto", ""),
                "oggetto": doc.get("title", ""),
                "data_atto": doc.get("protocol_date") or doc.get("metadata", {}).get("data_atto", ""),
                "anno": doc.get("metadata", {}).get("anno", ""),
                "scraper_type": doc.get("metadata", {}).get("scraper_type", "unknown")
            }
            
            # Re-importa con PDF URL
            success = await importer.import_atto(
                atto_data=atto_data,
                pdf_path=None,  # Non abbiamo il path locale
                pdf_url=pdf_url,
                ente=doc.get("metadata", {}).get("ente", "Firenze")
            )
            
            if success:
                # Verifica il nuovo documento
                new_docs = MongoDBService.find_documents("documents", {
                    "document_id": document_id,
                    "tenant_id": tenant_id
                }, limit=1)
                
                if new_docs:
                    new_doc = new_docs[0]
                    new_full_text = new_doc.get("content", {}).get("full_text", "")
                    new_chars = len(new_full_text)
                    has_structure = "structure" in new_doc.get("content", {})
                    structure_sections = len(new_doc.get("content", {}).get("structure", {}).get("sections", {}))
                    
                    stats["success"] += 1
                    stats["total_chars_before"] += current_chars
                    stats["total_chars_after"] += new_chars
                    
                    print(f"   ✅ Re-importato: {new_chars} caratteri (+{new_chars - current_chars})")
                    print(f"   📋 Sezioni strutturate: {structure_sections}")
                else:
                    stats["failed"] += 1
                    print(f"   ⚠️  Re-importato ma documento non trovato dopo update")
            else:
                stats["failed"] += 1
                print(f"   ❌ Re-importazione fallita")
        
        except Exception as e:
            stats["failed"] += 1
            print(f"   ❌ Errore: {e}")
            import traceback
            traceback.print_exc()
    
    # Report finale
    print(f"\n{'='*60}")
    print(f"📊 REPORT FINALE")
    print(f"{'='*60}")
    print(f"Totale documenti processati: {stats['total']}")
    print(f"✅ Successi: {stats['success']}")
    print(f"❌ Falliti: {stats['failed']}")
    print(f"⏭️  Saltati: {stats['skipped']}")
    print(f"📊 Caratteri totali prima: {stats['total_chars_before']:,}")
    print(f"📊 Caratteri totali dopo: {stats['total_chars_after']:,}")
    print(f"📈 Incremento totale: {stats['total_chars_after'] - stats['total_chars_before']:,} caratteri")
    print(f"{'='*60}")
    
    return stats["failed"] == 0


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Re-importa TUTTI i documenti da MongoDB estraendo testo completo dal PDF")
    parser.add_argument("--tenant-id", type=int, required=True, help="Tenant ID")
    parser.add_argument("--min-chars", type=int, default=1000, help="Soglia minima caratteri per considerare documento completo (default: 1000)")
    parser.add_argument("--force", action="store_true", help="Forza re-importazione anche se documento ha già testo completo")
    parser.add_argument("--limit", type=int, default=None, help="Limite massimo documenti da processare (default: tutti)")
    
    args = parser.parse_args()
    
    try:
        success = asyncio.run(reimport_all_documents(
            args.tenant_id,
            min_chars=args.min_chars,
            force=args.force,
            limit=args.limit
        ))
        sys.exit(0 if success else 1)
    except Exception as e:
        print(f"❌ Errore: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

