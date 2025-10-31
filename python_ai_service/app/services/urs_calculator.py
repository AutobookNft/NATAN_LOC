"""
URS Calculator - Ultra Reliability Score
Calcola il punteggio di affidabilità per ogni claim
"""

from typing import List, Dict, Any
from dataclasses import dataclass


@dataclass
class UrsScore:
    """URS Score result"""
    score: float  # 0.0 - 1.0
    label: str    # A, B, C, X
    breakdown: Dict[str, float]  # Component scores
    reason: str   # Explanation


class UrsCalculator:
    """
    Ultra Reliability Score Calculator
    Formula: URS = (C * 0.30) + (R * 0.25) + (E * 0.20) + (D * 0.15) + (O * 0.10)
    
    Where:
    - C: Coverage (quanta parte del claim è coperta da fonti)
    - R: Reference count (numero di fonti citanti)
    - E: Extractor quality (qualità OCR/estrazione)
    - D: Date coherence (coerenza temporale/semantica)
    - O: Out-of-domain risk (penalità se fonte esterna)
    """
    
    # Reference count weights
    REF_WEIGHTS = {
        0: 0.0,   # No sources = 0
        1: 0.6,   # Single source
        2: 0.8,   # Two sources
    }
    REF_WEIGHT_MULTIPLE = 1.0  # 3+ sources
    
    @staticmethod
    def calculate(
        coverage: float,
        source_count: int,
        extractor_quality: float = 0.9,
        date_coherence: float = 1.0,
        out_of_domain: bool = False
    ) -> UrsScore:
        """
        Calculate URS score
        
        Args:
            coverage: Coverage score (0.0 - 1.0)
            source_count: Number of cited sources
            extractor_quality: OCR/extraction quality (0.0 - 1.0)
            date_coherence: Date/semantic coherence (0.0 - 1.0)
            out_of_domain: True if external source (penalty)
        
        Returns:
            UrsScore object with score, label, breakdown, reason
        """
        # C: Coverage (weight: 0.30)
        c_score = coverage * 0.30
        
        # R: Reference count (weight: 0.25)
        if source_count >= 3:
            r_weight = UrsCalculator.REF_WEIGHT_MULTIPLE
        else:
            r_weight = UrsCalculator.REF_WEIGHTS.get(source_count, 0.0)
        r_score = r_weight * 0.25
        
        # E: Extractor quality (weight: 0.20)
        e_score = extractor_quality * 0.20
        
        # D: Date coherence (weight: 0.15)
        d_score = date_coherence * 0.15
        
        # O: Out-of-domain risk (weight: 0.10)
        # Penalty: external sources reduce score
        o_base = 1.0 if not out_of_domain else 0.5
        o_score = o_base * 0.10
        
        # Total URS
        total_urs = c_score + r_score + e_score + d_score + o_score
        
        # Label assignment
        if total_urs >= 0.85:
            label = "A"  # High reliability
            reason = "Highly reliable: multiple sources, good coverage, verified"
        elif total_urs >= 0.70:
            label = "B"  # Medium-high reliability
            reason = "Reliable: good source coverage, minor uncertainties"
        elif total_urs >= 0.50:
            label = "C"  # Medium reliability
            reason = "Moderate reliability: some sources, possible gaps"
        else:
            label = "X"  # Low reliability / blocked
            reason = "Low reliability: insufficient sources or coverage"
        
        breakdown = {
            "coverage": c_score,
            "reference_count": r_score,
            "extractor_quality": e_score,
            "date_coherence": d_score,
            "out_of_domain": o_score,
            "total": total_urs
        }
        
        return UrsScore(
            score=total_urs,
            label=label,
            breakdown=breakdown,
            reason=reason
        )
    
    @staticmethod
    def calculate_from_claim(claim: Dict[str, Any]) -> UrsScore:
        """
        Calculate URS from claim dictionary
        
        Args:
            claim: Claim dict with keys:
                - text: claim text
                - source_ids: list of source IDs
                - is_inference: bool
                - extractor_quality: float (optional)
                - date_coherence: float (optional)
                - out_of_domain: bool (optional)
        
        Returns:
            UrsScore object
        """
        source_ids = claim.get("source_ids", [])
        source_count = len(source_ids) if source_ids else 0
        
        # Coverage: 1.0 if has sources, 0.5 if inference, 0.0 if no sources
        if source_count > 0:
            coverage = 1.0 if not claim.get("is_inference", False) else 0.7
        else:
            coverage = 0.0
        
        extractor_quality = claim.get("extractor_quality", 0.9)
        date_coherence = claim.get("date_coherence", 1.0)
        out_of_domain = claim.get("out_of_domain", False)
        
        return UrsCalculator.calculate(
            coverage=coverage,
            source_count=source_count,
            extractor_quality=extractor_quality,
            date_coherence=date_coherence,
            out_of_domain=out_of_domain
        )


