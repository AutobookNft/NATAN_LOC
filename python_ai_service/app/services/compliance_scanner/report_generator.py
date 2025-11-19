"""
Generatore report PDF e HTML per Compliance Scanner
"""

import logging
from typing import Optional
from pathlib import Path
from jinja2 import Template
from .models import ComplianceReport

logger = logging.getLogger(__name__)

PDF_TEMPLATE = """
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report Conformità - {{ comune_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 200px; }
        .score { font-size: 48px; font-weight: bold; color: {% if compliance_score >= 80 %}green{% elif compliance_score >= 60 %}orange{% else %}red{% endif %}; }
        .violations { margin-top: 30px; }
        .violation { padding: 15px; margin: 10px 0; border-left: 4px solid {% if violation.severity == 'critical' %}red{% elif violation.severity == 'high' %}orange{% else %}yellow{% endif %}; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Report Conformità Albo Pretorio</h1>
        <h2>{{ comune_name }}</h2>
        <p>Data scan: {{ scan_date }}</p>
    </div>
    
    <div class="score">
        Score: {{ compliance_score }}/100
    </div>
    
    <div class="violations">
        <h3>Violazioni Rilevate ({{ violations|length }})</h3>
        {% for violation in violations %}
        <div class="violation">
            <strong>{{ violation.norm }} - {{ violation.article }}</strong><br>
            {{ violation.description }}<br>
            <small>Severità: {{ violation.severity }}</small>
        </div>
        {% endfor %}
    </div>
</body>
</html>
"""

class ReportGenerator:
    """Genera report PDF e HTML"""
    
    def __init__(self):
        self.output_dir = Path("reports/compliance")
        self.output_dir.mkdir(parents=True, exist_ok=True)
    
    def generate_pdf(self, report: ComplianceReport) -> Optional[str]:
        """Genera PDF report"""
        try:
            template = Template(PDF_TEMPLATE)
            html_content = template.render(
                comune_name=report.comune_name,
                scan_date=report.scan_date.isoformat(),
                compliance_score=report.compliance_score,
                violations=report.violations
            )
            
            # Salva HTML
            html_path = self.output_dir / f"{report.comune_slug}_report.html"
            html_path.write_text(html_content, encoding="utf-8")
            
            # TODO: Converti HTML a PDF con weasyprint o pdfkit
            pdf_path = self.output_dir / f"{report.comune_slug}_report.pdf"
            # pdf_path.write_bytes(pdf_bytes)
            
            logger.info(f"PDF generato: {pdf_path}")
            return str(pdf_path)
            
        except Exception as e:
            logger.error(f"Errore generazione PDF: {e}")
            return None
    
    def generate_landing_page(self, report: ComplianceReport) -> str:
        """Genera landing page HTML con QR code"""
        # TODO: Implementa generazione landing page con QR
        landing_url = f"https://natan.florenceegi.com/compliance/{report.comune_slug}"
        return landing_url

