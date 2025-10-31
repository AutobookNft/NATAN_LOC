"""
Logical Verifier - Verifica post-generazione ogni claim
Calcola URS e assegna label A/B/C/X
"""

from typing import List, Dict, Any, Optional
from app.services.urs_calculator import UrsCalculator, UrsScore


class LogicalVerifier:
    """
    Verifica logica post-generazione dei claims
    Calcola URS e blocca claims con URS < 0.5
    """
    
    def __init__(self, urs_calculator: Optional[UrsCalculator] = None):
        """
        Initialize Logical Verifier
        
        Args:
            urs_calculator: Optional UrsCalculator instance
        """
        self.urs_calculator = urs_calculator or UrsCalculator()
    
    def verify_claims(
        self,
        claims: List[Dict[str, Any]],
        chunks: List[Dict[str, Any]],
        tenant_id: int
    ) -> Dict[str, Any]:
        """
        Verify all claims and calculate URS
        
        Args:
            claims: List of claims to verify
            chunks: Source chunks for validation
            tenant_id: Tenant ID
        
        Returns:
            Dict with:
                - verified_claims: List of verified claims with URS
                - blocked_claims: List of blocked claims (URS < 0.5)
                - avg_urs: Average URS score
                - status: "SAFE" | "WARNING" | "BLOCKED"
        """
        verified_claims = []
        blocked_claims = []
        total_urs = 0.0
        
        # Build chunk map for quick lookup
        chunk_map = {chunk.get("chunk_id"): chunk for chunk in chunks}
        
        for claim in claims:
            # Calculate URS
            urs_score = self.urs_calculator.calculate_from_claim(claim)
            
            # Add URS to claim
            claim_with_urs = {
                **claim,
                "urs": urs_score.score,
                "urs_label": urs_score.label,
                "urs_reason": urs_score.reason,
                "urs_breakdown": urs_score.breakdown
            }
            
            # Block if URS < 0.5
            if urs_score.score < 0.5:
                blocked_claims.append(claim_with_urs)
            else:
                verified_claims.append(claim_with_urs)
                total_urs += urs_score.score
        
        # Calculate average URS
        num_verified = len(verified_claims)
        avg_urs = total_urs / num_verified if num_verified > 0 else 0.0
        
        # Determine status
        if len(blocked_claims) == 0:
            status = "SAFE"
        elif len(blocked_claims) < len(claims) / 2:
            status = "WARNING"  # Some claims blocked
        else:
            status = "BLOCKED"  # Most/all claims blocked
        
        return {
            "verified_claims": verified_claims,
            "blocked_claims": blocked_claims,
            "avg_urs": avg_urs,
            "status": status,
            "total_claims": len(claims),
            "verified_count": len(verified_claims),
            "blocked_count": len(blocked_claims)
        }
    
    def verify_single_claim(
        self,
        claim: Dict[str, Any],
        chunks: List[Dict[str, Any]],
        tenant_id: int = 0
    ) -> Dict[str, Any]:
        """
        Verify a single claim
        
        Args:
            claim: Claim dict
            chunks: Source chunks
            tenant_id: Tenant ID (default: 0)
        
        Returns:
            Verified claim dict with URS
        """
        result = self.verify_claims([claim], chunks, tenant_id=tenant_id)
        if result["verified_claims"]:
            return result["verified_claims"][0]
        elif result["blocked_claims"]:
            return result["blocked_claims"][0]
        return claim

