"""
Admin router - Endpoint protetti per amministratori
"""

from fastapi import APIRouter, HTTPException, Depends, Header
from pydantic import BaseModel
from typing import Optional, List, Dict
from app.services.compliance_scanner import AlboPretorioComplianceScanner
from app.services.compliance_scanner.report_generator import ReportGenerator
from app.services.compliance_scanner.email_sender import EmailSender
from app.services.compliance_scanner.comuni_tracker import ComuniScrapingTracker

router = APIRouter()

# Dependency per verificare admin
async def verify_admin(authorization: Optional[str] = Header(None)):
    """Verifica che l'utente sia admin"""
    # TODO: Implementa verifica token JWT o API key
    if not authorization or not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Admin authorization required")
    # Per ora accetta qualsiasi Bearer token
    return True

class ComplianceScanResponse(BaseModel):
    """Risposta scan conformità"""
    comune_slug: str
    compliance_score: float
    violations_count: int
    pdf_path: Optional[str] = None
    email_sent: bool = False
    landing_page_url: Optional[str] = None
    atti_estratti: Optional[int] = None  # Numero atti estratti (per dry-run)
    atti_sample: Optional[List[Dict]] = None  # Primi atti estratti (per preview)

@router.post("/admin/compliance-scan/{comune_slug}", response_model=ComplianceScanResponse, dependencies=[Depends(verify_admin)])
async def scan_compliance(
    comune_slug: str, 
    mongodb_import: bool = False, 
    tenant_id: Optional[int] = None
):
    """
    Lancia scan conformità per un comune specifico
    
    Query params:
    - mongodb_import: Se True, importa atti in MongoDB con embeddings (default: False)
    - tenant_id: ID tenant per multi-tenancy (opzionale)
    
    Endpoint protetto - solo admin
    """
    try:
        # Per esecuzione normale, usa output_dir standard
        scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
        report = await scanner.scan_comune(comune_slug, tenant_id=tenant_id, dry_run=False, mongodb_import=mongodb_import)
        
        # Genera PDF
        report_generator = ReportGenerator()
        pdf_path = report_generator.generate_pdf(report)
        landing_url = report_generator.generate_landing_page(report)
        
        # Invia email
        email_sender = EmailSender()
        # TODO: Ottieni email da configurazione comune
        recipients = [
            f"segreteria@{comune_slug}.it",
            f"trasparenza@{comune_slug}.it"
        ]
        email_sent = await email_sender.send_report(report, recipients)
        
        return ComplianceScanResponse(
            comune_slug=comune_slug,
            compliance_score=report.compliance_score,
            violations_count=len(report.violations),
            pdf_path=pdf_path,
            email_sent=email_sent,
            landing_page_url=landing_url
        )
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Scan failed: {str(e)}")

@router.post("/admin/compliance-scan/{comune_slug}/dry-run", response_model=ComplianceScanResponse, dependencies=[Depends(verify_admin)])
async def scan_compliance_dry_run(comune_slug: str):
    """
    Dry-run scan conformità - restituisce solo report senza generare PDF/email
    
    Endpoint protetto - solo admin
    """
    try:
        # Per dry-run, usa output_dir standard (come gli altri scraper)
        # DRY RUN: passa dry_run=True per contare senza estrarre tutti gli atti
        scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
        report = await scanner.scan_comune(comune_slug, dry_run=True)
        
        # Estrai conteggio atti dal metadata
        atti_estratti = 0
        atti_sample = []
        json_backup_path = None
        
        if report.metadata:
            # In dry-run, atti_estratti contiene il conteggio totale
            atti_estratti = report.metadata.get('atti_estratti', 0)
            
            # In dry-run abbiamo atti_list con primo e ultimo atto (per preview)
            atti_list = report.metadata.get('atti_list', [])
            if isinstance(atti_list, list) and len(atti_list) > 0:
                # TUTTI gli atti disponibili per preview (in dry-run sono primo e ultimo)
                # Limite lunghezza oggetto per display: 200 caratteri (esplicito)
                MAX_OGGETTO_LENGTH = 200
                atti_sample = [{
                    'numero': str(atto.get('numero', atto.get('numero_atto', atto.get('numero_registro', 'N/A')))),
                    'data': str(atto.get('data', atto.get('data_pubblicazione', atto.get('data_adozione', 'N/A')))),
                    'oggetto': str(atto.get('oggetto', atto.get('descrizione', 'N/A')))[:MAX_OGGETTO_LENGTH],
                    'tipo': str(atto.get('tipo', atto.get('tipo_atto', 'Documento'))),
                } for atto in atti_list]  # TUTTI gli atti, nessun limite nascosto
        
        # Costruisci output come gli altri scraper (include "Backup JSON salvato" se disponibile)
        output_lines = []
        if json_backup_path:
            output_lines.append(f"\n   💾 Backup JSON salvato: {json_backup_path}")
        
        response = ComplianceScanResponse(
            comune_slug=comune_slug,
            compliance_score=report.compliance_score,
            violations_count=len(report.violations),
            pdf_path=None,  # Non generato in dry-run
            email_sent=False,  # Non inviata in dry-run
            landing_page_url=None,  # Non generata in dry-run
            atti_estratti=atti_estratti,
            atti_sample=atti_sample if atti_sample else None
        )
        
        # Aggiungi output al response (per compatibilità con sistema esistente)
        if hasattr(response, 'output'):
            response.output = '\n'.join(output_lines) if output_lines else ''
        elif json_backup_path:
            # Se il modello non ha campo output, aggiungilo al metadata
            if not hasattr(response, 'metadata'):
                response.metadata = {}
            response.metadata['json_backup_path'] = json_backup_path
        
        return response
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Dry-run scan failed: {str(e)}")

@router.get("/admin/compliance-scanner/comuni-scrapati", dependencies=[Depends(verify_admin)])
async def get_scraped_comuni(tenant_id: Optional[int] = None, status: Optional[str] = None):
    """
    Ottiene lista di tutti i comuni già scrapati.
    
    Query params:
    - tenant_id: Filtra per tenant (opzionale)
    - status: Filtra per status (completed, failed, partial) (opzionale)
    """
    try:
        comuni = ComuniScrapingTracker.get_scraped_comuni(tenant_id=tenant_id, status=status)
        return {
            "total": len(comuni),
            "comuni": comuni
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get scraped comuni: {str(e)}")

@router.get("/admin/compliance-scanner/comuni-non-scrapati", dependencies=[Depends(verify_admin)])
async def get_unscraped_comuni(tenant_id: Optional[int] = None):
    """
    Ottiene lista di comuni non ancora scrapati dalla lista standard.
    
    Query params:
    - tenant_id: Filtra per tenant (opzionale)
    """
    try:
        # Lista standard comuni toscani supportati
        comuni_standard = [
            "firenze", "sesto_fiorentino", "empoli", "pisa", "prato",
            "arezzo", "livorno", "pistoia", "grosseto", "massa", "lucca", "siena"
        ]
        
        unscraped = ComuniScrapingTracker.get_unscraped_comuni(comuni_standard, tenant_id=tenant_id)
        return {
            "total": len(unscraped),
            "comuni": unscraped
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get unscraped comuni: {str(e)}")

@router.get("/admin/compliance-scanner/stats", dependencies=[Depends(verify_admin)])
async def get_scraping_stats(tenant_id: Optional[int] = None):
    """
    Ottiene statistiche aggregate sui comuni scrapati.
    
    Query params:
    - tenant_id: Filtra per tenant (opzionale)
    """
    try:
        stats = ComuniScrapingTracker.get_comuni_stats(tenant_id=tenant_id)
        return stats
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get stats: {str(e)}")

@router.post("/admin/compliance-scanner/scrape-all", dependencies=[Depends(verify_admin)])
async def scrape_all_comuni(
    skip_scraped: bool = True,
    tenant_id: Optional[int] = None,
    dry_run: bool = False
):
    """
    Scrapa tutti i comuni standard, saltando quelli già scrapati se richiesto.
    
    Body params:
    - skip_scraped: Se True, salta comuni già scrapati (default: True)
    - tenant_id: ID tenant (opzionale)
    - dry_run: Se True, solo dry-run senza estrarre atti (default: False)
    """
    try:
        # Lista standard comuni toscani supportati
        comuni_standard = [
            "firenze", "sesto_fiorentino", "empoli", "pisa", "prato",
            "arezzo", "livorno", "pistoia", "grosseto", "massa", "lucca", "siena"
        ]
        
        # Filtra comuni già scrapati se richiesto
        comuni_to_scrape = comuni_standard
        if skip_scraped:
            comuni_to_scrape = ComuniScrapingTracker.get_unscraped_comuni(
                comuni_standard, 
                tenant_id=tenant_id
            )
        
        if not comuni_to_scrape:
            return {
                "message": "Tutti i comuni sono già stati scrapati",
                "total": 0,
                "scraped": [],
                "skipped": len(comuni_standard)
            }
        
        scanner = AlboPretorioComplianceScanner(output_dir="storage/testing/compliance_scanner")
        
        results = []
        skipped = []
        
        for comune_slug in comuni_to_scrape:
            try:
                report = await scanner.scan_comune(comune_slug, tenant_id=tenant_id, dry_run=dry_run)
                
                results.append({
                    "comune_slug": comune_slug,
                    "status": "success",
                    "atti_estratti": report.metadata.get("atti_estratti", 0) if report.metadata else 0,
                    "compliance_score": report.compliance_score,
                    "violations_count": len(report.violations)
                })
                
            except Exception as e:
                skipped.append({
                    "comune_slug": comune_slug,
                    "status": "failed",
                    "error": str(e)
                })
        
        return {
            "total": len(comuni_to_scrape),
            "scraped": len(results),
            "failed": len(skipped),
            "results": results,
            "skipped_comuni": skipped
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to scrape all comuni: {str(e)}")

@router.post("/admin/compliance-scanner/import-atti/{comune_slug}", dependencies=[Depends(verify_admin)])
async def import_atti_to_mongodb(comune_slug: str, tenant_id: Optional[int] = None):
    """
    Importa atti già scrapati di un comune in MongoDB con embeddings.
    
    Legge il file JSON più recente per il comune e importa tutti gli atti.
    
    Query params:
    - tenant_id: ID tenant per multi-tenancy (opzionale)
    
    Endpoint protetto - solo admin
    """
    try:
        from pathlib import Path
        import json
        from app.services.pa_act_mongodb_importer import PAActMongoDBImporter
        
        # Trova file JSON più recente per questo comune
        json_dir = Path("storage/testing/compliance_scanner/json")
        json_files = list(json_dir.glob(f"atti_{comune_slug}_*.json"))
        
        if not json_files:
            raise HTTPException(status_code=404, detail=f"Nessun file JSON trovato per comune {comune_slug}")
        
        # Prendi il file più recente
        latest_file = max(json_files, key=lambda p: p.stat().st_mtime)
        
        # Carica atti dal JSON
        with open(latest_file, 'r', encoding='utf-8') as f:
            atti_list = json.load(f)
        
        if not atti_list:
            raise HTTPException(status_code=404, detail=f"File JSON vuoto per comune {comune_slug}")
        
        # Inizializza importer
        importer = PAActMongoDBImporter(tenant_id=tenant_id or 1, dry_run=False)
        
        # CRITICAL: Check which atti are already imported BEFORE processing
        from app.services.mongodb_service import MongoDBService
        already_imported = set()
        if MongoDBService.is_connected():
            db = MongoDBService.get_database()
            if db is not None:
                coll = db['documents']
                # Get all existing protocol_numbers for this tenant
                existing_protocols = set(
                    doc.get('protocol_number', '')
                    for doc in coll.find(
                        {'tenant_id': tenant_id or 1, 'document_type': 'pa_act'},
                        {'protocol_number': 1}
                    )
                )
                already_imported = existing_protocols
        
        imported_count = 0
        skipped_count = 0
        errors_count = 0
        errors_detail = []
        
        for atto_dict in atti_list:
            # Skip if already imported
            protocol_num = atto_dict.get('numero', atto_dict.get('numero_atto', ''))
            if protocol_num in already_imported:
                skipped_count += 1
                continue
            try:
                # Converti formato atto
                atto_data = {
                    'numero_atto': atto_dict.get('numero', atto_dict.get('numero_atto', '')),
                    'tipo_atto': atto_dict.get('tipo', atto_dict.get('tipo_atto', '')),
                    'oggetto': atto_dict.get('oggetto', atto_dict.get('descrizione', '')),
                    'data_atto': atto_dict.get('data', atto_dict.get('data_pubblicazione', atto_dict.get('data_adozione', ''))),
                    'anno': atto_dict.get('anno', ''),
                    'scraper_type': 'compliance_scanner',
                    'comune_slug': comune_slug
                }
                
                # URL PDF se disponibile
                pdf_url = atto_dict.get('url_pdf') or atto_dict.get('pdf_url') or atto_dict.get('link')
                
                # Import atto
                success = await importer.import_atto(
                    atto_data=atto_data,
                    pdf_url=pdf_url,
                    ente=comune_slug.title()
                )
                
                if success:
                    imported_count += 1
                else:
                    errors_count += 1
                    errors_detail.append({
                        'numero': atto_data['numero_atto'],
                        'error': 'Import failed'
                    })
                    
            except Exception as e:
                errors_count += 1
                errors_detail.append({
                    'numero': atto_dict.get('numero', 'N/A'),
                    'error': str(e)
                })
        
        return {
            "success": True,
            "comune_slug": comune_slug,
            "json_file": str(latest_file),
            "total_atti": len(atti_list),
            "imported": imported_count,
            "skipped": skipped_count,
            "errors": errors_count,
            "errors_detail": errors_detail[:10] if errors_detail else []  # Primi 10 errori
        }
        
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Import failed: {str(e)}")

@router.post("/admin/compliance-scanner/generate-reports", dependencies=[Depends(verify_admin)])
async def generate_reports_for_scraped_comuni(tenant_id: Optional[int] = None):
    """
    Genera report PDF per tutti i comuni già scrapati che non hanno ancora report.
    
    Query params:
    - tenant_id: Filtra per tenant (opzionale)
    """
    try:
        scraped = ComuniScrapingTracker.get_scraped_comuni(tenant_id=tenant_id, status="completed")
        report_generator = ReportGenerator()
        
        generated = []
        failed = []
        
        for comune_doc in scraped:
            comune_slug = comune_doc.get("comune_slug")
            
            # Se ha già PDF, salta
            if comune_doc.get("pdf_report_path"):
                continue
            
            try:
                # Ricrea report dal tracking (semplificato)
                from app.services.compliance_scanner.models import ComplianceReport, ComplianceViolation
                from datetime import datetime
                
                report = ComplianceReport(
                    comune_slug=comune_slug,
                    comune_name=comune_doc.get("comune_nome", comune_slug.title()),
                    scan_date=comune_doc.get("scraped_at", datetime.now()),
                    albo_url=comune_doc.get("metodo_usato", "Unknown"),
                    compliance_score=comune_doc.get("compliance_score", 0.0) * 100.0,
                    violations=[]  # Non abbiamo dettagli violazioni nel tracking
                )
                
                # Genera PDF
                pdf_path = report_generator.generate_pdf(report)
                
                # Aggiorna tracking con PDF path
                ComuniScrapingTracker.mark_comune_scraped(
                    comune_slug=comune_slug,
                    comune_nome=comune_doc.get("comune_nome"),
                    atti_estratti=comune_doc.get("atti_estratti", 0),
                    compliance_score=comune_doc.get("compliance_score"),
                    violations_count=comune_doc.get("violations_count", 0),
                    metodo_usato=comune_doc.get("metodo_usato"),
                    json_backup_path=comune_doc.get("json_backup_path"),
                    pdf_report_path=pdf_path,
                    landing_page_url=comune_doc.get("landing_page_url"),
                    email_sent=comune_doc.get("email_sent", False),
                    tenant_id=tenant_id,
                    status=comune_doc.get("status", "completed")
                )
                
                generated.append({
                    "comune_slug": comune_slug,
                    "pdf_path": pdf_path
                })
                
            except Exception as e:
                failed.append({
                    "comune_slug": comune_slug,
                    "error": str(e)
                })
        
        return {
            "generated": len(generated),
            "failed": len(failed),
            "reports": generated,
            "errors": failed
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to generate reports: {str(e)}")

