#!/usr/bin/env python3
"""
Script per re-importare un documento da MongoDB estraendo il testo completo dal PDF
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


async def reimport_document(document_id: str, tenant_id: int, force: bool = False):
    """
    Re-importa un documento estraendo il testo completo dal PDF
    
    Args:
        document_id: Document ID in MongoDB
        tenant_id: Tenant ID
        force: Se True, aggiorna anche se il documento ha già testo completo
    """
    if not MongoDBService.is_connected():
        print("❌ MongoDB non connesso")
        return False
    
    collection = MongoDBService.get_collection("documents")
    if collection is None:
        print("❌ Collection 'documents' non trovata")
        return False
    
    # Trova documento
    documents = MongoDBService.find_documents("documents", {
        "document_id": document_id,
        "tenant_id": tenant_id
    }, limit=1)
    
    if not documents:
        print(f"❌ Documento {document_id} non trovato")
        return False
    
    doc = documents[0]
    pdf_url = doc.get("metadata", {}).get("pdf_url") or doc.get("pdf_url")
    full_text = doc.get("content", {}).get("full_text", "")
    current_chars = len(full_text)
    
    print(f"📄 Documento: {doc.get('title', 'N/A')}")
    print(f"📊 Caratteri attuali: {current_chars}")
    print(f"🔗 PDF URL: {pdf_url}")
    
    # Se ha già testo completo e non force, salta
    if current_chars > 1000 and not force:
        print(f"✅ Documento ha già testo completo ({current_chars} caratteri). Usa --force per re-importare.")
        return True
    
    if not pdf_url:
        print("❌ PDF URL non disponibile")
        return False
    
    # Crea importer
    importer = PAActMongoDBImporter(tenant_id=tenant_id, dry_run=False)
    
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
    print(f"🔄 Re-importazione documento con estrazione PDF...")
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
            print(f"✅ Re-importazione completata!")
            print(f"📊 Nuovi caratteri: {new_chars} (prima: {current_chars})")
            print(f"📈 Incremento: {new_chars - current_chars} caratteri")
            return True
        else:
            print("⚠️  Re-importazione completata ma documento non trovato dopo l'update")
            return False
    else:
        print("❌ Re-importazione fallita")
        return False


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Re-importa documento da MongoDB estraendo testo completo dal PDF")
    parser.add_argument("--document-id", type=str, required=True, help="Document ID in MongoDB")
    parser.add_argument("--tenant-id", type=int, required=True, help="Tenant ID")
    parser.add_argument("--force", action="store_true", help="Forza re-importazione anche se documento ha già testo completo")
    
    args = parser.parse_args()
    
    try:
        success = asyncio.run(reimport_document(args.document_id, args.tenant_id, args.force))
        sys.exit(0 if success else 1)
    except Exception as e:
        print(f"❌ Errore: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

