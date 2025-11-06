#!/usr/bin/env python3
"""
Script per eliminare tutti i documenti di un tenant da MongoDB
Usato per pulire il database prima di re-importare
"""

import sys
import argparse
from pathlib import Path
from dotenv import load_dotenv

# Add the parent directory of python_ai_service to sys.path
NATAN_LOC_ROOT = Path(__file__).resolve().parents[3]
sys.path.insert(0, str(NATAN_LOC_ROOT / "python_ai_service"))

from app.services.mongodb_service import MongoDBService

# Load environment variables from .env file
env_path = NATAN_LOC_ROOT / "python_ai_service" / ".env"
if env_path.exists():
    load_dotenv(env_path, override=True)


def clear_tenant_documents(tenant_id: int, confirm: bool = False):
    """
    Elimina tutti i documenti di un tenant da MongoDB
    
    Args:
        tenant_id: Tenant ID da eliminare
        confirm: Se True, esegue senza chiedere conferma
    """
    if not MongoDBService.is_connected():
        print("❌ MongoDB non connesso")
        return False
    
    collection = MongoDBService.get_collection("documents")
    if collection is None:
        print("❌ Collection 'documents' non trovata")
        return False
    
    # Conta documenti da eliminare
    count = collection.count_documents({"tenant_id": tenant_id})
    
    if count == 0:
        print(f"✅ Nessun documento trovato per tenant_id: {tenant_id}")
        return True
    
    print(f"⚠️  ATTENZIONE: Verranno eliminati {count} documenti per tenant_id: {tenant_id}")
    
    if not confirm:
        response = input("Sei sicuro? (scrivi 'SI' per confermare): ")
        if response.upper() != 'SI':
            print("❌ Operazione annullata")
            return False
    
    # Elimina documenti
    result = collection.delete_many({"tenant_id": tenant_id})
    
    print(f"✅ Eliminati {result.deleted_count} documenti per tenant_id: {tenant_id}")
    return True


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Elimina tutti i documenti di un tenant da MongoDB")
    parser.add_argument("--tenant-id", type=int, required=True, help="Tenant ID da eliminare")
    parser.add_argument("--confirm", action="store_true", help="Esegue senza chiedere conferma")
    
    args = parser.parse_args()
    
    try:
        success = clear_tenant_documents(args.tenant_id, confirm=args.confirm)
        sys.exit(0 if success else 1)
    except Exception as e:
        print(f"❌ Errore: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

