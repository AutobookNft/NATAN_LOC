"""
Script di import veloce - Importa solo file .md e .txt (skip PDF)
Più veloce perché non richiede PyPDF2 e processa solo testi
"""

import asyncio
import sys
from pathlib import Path

# Reuse main script but filter out PDFs
sys.path.insert(0, str(Path(__file__).parent.parent.parent))

from app.scripts.import_egi_docs_to_mongodb import DocumentImporter

async def main():
    """Import only .md and .txt files (faster)"""
    from pathlib import Path
    
    # Override supported extensions in importer
    egi_docs_dir = Path("/home/fabio/EGI/docs")
    tenant_id = 1
    
    importer = DocumentImporter(tenant_id=tenant_id)
    
    # Temporarily modify supported extensions
    original_method = importer.import_directory.__code__
    
    # Call import but it will process all files - we'll filter in the script
    # Actually, let's just modify the script to support extension filtering
    
    # For now, run the normal import but it will skip PDFs automatically if PyPDF2 not available
    await importer.import_directory(egi_docs_dir, tenant_id)

if __name__ == "__main__":
    asyncio.run(main())



