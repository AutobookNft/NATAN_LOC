"""
Admin router - Endpoint protetti per amministratori
"""

from fastapi import APIRouter, HTTPException, Depends, Header
from pydantic import BaseModel
from typing import Optional
from app.services.compliance_scanner import AlboPretorioComplianceScanner
from app.services.compliance_scanner.report_generator import ReportGenerator
from app.services.compliance_scanner.email_sender import EmailSender

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

@router.post("/admin/compliance-scan/{comune_slug}", response_model=ComplianceScanResponse, dependencies=[Depends(verify_admin)])
async def scan_compliance(comune_slug: str):
    """
    Lancia scan conformità per un comune specifico
    
    Endpoint protetto - solo admin
    """
    try:
        scanner = AlboPretorioComplianceScanner()
        report = await scanner.scan_comune(comune_slug)
        
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

