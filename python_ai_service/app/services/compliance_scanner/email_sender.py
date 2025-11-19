"""
Invio email report conformità
"""

import logging
from typing import List
from .models import ComplianceReport

logger = logging.getLogger(__name__)

EMAIL_TEMPLATE = """
Oggetto: Violazioni Normative Albo Pretorio - {comune_name}

Gentile Segretario Comunale / Responsabile Trasparenza,

Il presente report evidenzia violazioni normative rilevate nell'Albo Pretorio del Comune di {comune_name}.

SCORE CONFORMITÀ: {compliance_score}/100

VIOLAZIONI CRITICHE:
{violations_critical}

VIOLAZIONI ALTE:
{violations_high}

Per maggiori dettagli, consultare il report PDF allegato o visitare:
{landing_page_url}

Cordiali saluti,
NATAN Compliance Scanner
"""

class EmailSender:
    """Invia email report conformità"""
    
    def __init__(self):
        pass
    
    async def send_report(self, report: ComplianceReport, recipients: List[str]) -> bool:
        """
        Invia report via email
        
        Args:
            report: Report conformità
            recipients: Lista email destinatari
            
        Returns:
            True se invio riuscito
        """
        try:
            # Prepara contenuto email
            violations_critical = [
                f"- {v.description} ({v.norm} {v.article})"
                for v in report.violations
                if v.severity == "critical"
            ]
            
            violations_high = [
                f"- {v.description} ({v.norm} {v.article})"
                for v in report.violations
                if v.severity == "high"
            ]
            
            email_content = EMAIL_TEMPLATE.format(
                comune_name=report.comune_name,
                compliance_score=report.compliance_score,
                violations_critical="\n".join(violations_critical) if violations_critical else "Nessuna",
                violations_high="\n".join(violations_high) if violations_high else "Nessuna",
                landing_page_url=report.landing_page_url or "N/A"
            )
            
            # TODO: Implementa invio email con SMTP o servizio esterno
            logger.info(f"Email preparata per {len(recipients)} destinatari")
            
            return True
            
        except Exception as e:
            logger.error(f"Errore invio email: {e}")
            return False

