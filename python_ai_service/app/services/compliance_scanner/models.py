"""
Modelli Pydantic per Compliance Scanner
"""

from typing import List, Dict, Optional
from pydantic import BaseModel
from datetime import datetime


class ComplianceViolation(BaseModel):
    """Violazione normativa rilevata"""
    norm: str  # L.69/2009, CAD, AgID 2025
    article: str
    violation_type: str
    description: str
    severity: str  # critical, high, medium, low


class ComplianceReport(BaseModel):
    """Report completo di conformità"""
    comune_slug: str
    comune_name: str
    scan_date: datetime
    albo_url: str
    compliance_score: float  # 0-100
    violations: List[ComplianceViolation]
    screenshot_path: Optional[str] = None
    pdf_path: Optional[str] = None
    landing_page_url: Optional[str] = None
    metadata: Optional[Dict] = None  # Metadata aggiuntivi (atti estratti, etc.)

