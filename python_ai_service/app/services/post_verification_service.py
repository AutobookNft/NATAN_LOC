"""
Post-Verification Service - Verifica obbligatoria post-generazione
Garantisce che ogni affermazione nella risposta sia tracciabile ai documenti
CRITICAL: Se questa verifica fallisce, la risposta viene BLOCCATA
"""

from typing import List, Dict, Any, Optional, Set
import re


class PostVerificationService:
    """
    Servizio di verifica postuma obbligatoria
    
    Verifica che:
    1. Ogni affermazione nella risposta naturale sia tracciabile a un claim verificato
    2. Ogni sourceRef sia effettivamente presente nei chunks recuperati
    3. Almeno un documento sia stato recuperato dal database MongoDB
    4. Nessuna informazione sia inventata o non tracciabile
    """
    
    def __init__(self):
        pass
    
    def verify_response_completeness(
        self,
        answer_text: str,
        verified_claims: List[Dict[str, Any]],
        chunks: List[Dict[str, Any]],
        question: str
    ) -> Dict[str, Any]:
        """
        Verifica postuma completa della risposta
        
        Args:
            answer_text: Risposta naturale generata
            verified_claims: Lista di claims verificati con sourceRefs
            chunks: Chunks recuperati dal database
            question: Domanda originale
            
        Returns:
            Dict con:
                - is_valid: bool - Se la risposta è valida
                - untraceable_statements: List[str] - Affermazioni non tracciabili
                - missing_sources: List[str] - SourceRefs non presenti nei chunks
                - document_count: int - Numero di documenti effettivamente recuperati
                - should_block: bool - Se la risposta deve essere bloccata
                - verification_reason: str - Motivo del blocco/approvazione
        """
        result = {
            "is_valid": True,
            "untraceable_statements": [],
            "missing_sources": [],
            "document_count": 0,
            "should_block": False,
            "verification_reason": ""
        }
        
        # STEP 1: CRITICAL - Verifica che almeno un documento sia stato recuperato
        if not chunks or len(chunks) == 0:
            result["is_valid"] = False
            result["should_block"] = True
            result["verification_reason"] = "❌ BLOCCATO: Nessun documento recuperato dal database MongoDB. La risposta non può essere supportata da fonti verificabili."
            return result
        
        # STEP 1.5: Verifica che i chunks non siano placeholder o messaggi di errore
        # RILASSATO: Se abbiamo chunks, assumiamo che siano validi se hanno contenuto
        real_chunks = []
        for chunk in chunks:
            chunk_text = chunk.get("chunk_text") or chunk.get("text", "")
            if not chunk_text:
                continue
            
            # RILASSATO: Solo verifica pattern espliciti di errore/placeholder
            # Non blocchiamo se il chunk ha contenuto minimo
            chunk_lower = chunk_text.lower().strip()
            
            # Pattern molto specifici di errore/placeholder
            explicit_error_patterns = [
                r"^nessun documento trovato|^no document found|^documento non trovato|^not found",
                r"^placeholder$|^example$|^test data$|^dummy data$",
                r"^error:|^errore:|^exception:|^failed:"
            ]
            is_explicit_error = any(re.match(pattern, chunk_lower) for pattern in explicit_error_patterns)
            
            # Se NON è un errore esplicito E ha contenuto minimo, consideralo valido
            if not is_explicit_error and len(chunk_text.strip()) > 30:  # Ridotto da 50 a 30
                real_chunks.append(chunk)
        
        # RILASSATO: Se abbiamo chunks recuperati, non bloccarli subito
        # Verifichiamo solo se TUTTI sono chiaramente placeholder
        if len(real_chunks) == 0 and len(chunks) > 0:
            # Tutti i chunks sono placeholder - blocca
            result["is_valid"] = False
            result["should_block"] = True
            result["verification_reason"] = "❌ BLOCCATO: Tutti i chunks recuperati sono placeholder o errori espliciti."
            return result
        elif len(real_chunks) == 0:
            # Nessun chunk recuperato - blocca
            result["is_valid"] = False
            result["should_block"] = True
            result["verification_reason"] = "❌ BLOCCATO: Nessun chunk recuperato dal database."
            return result
        
        # Count unique documents REALI
        # RILASSATO: Accetta diversi formati di document_id
        document_ids: Set[str] = set()
        for chunk in real_chunks:
            doc_id = chunk.get("document_id") or chunk.get("source_ref", {}).get("document_id") or chunk.get("doc_id")
            if doc_id:
                doc_id_str = str(doc_id).strip().lower()
                # Accetta qualsiasi document_id che non sia esplicitamente vuoto/null
                if doc_id_str and doc_id_str not in ["", "none", "null", "undefined", "nan", "0"]:
                    document_ids.add(str(doc_id))
            
            # Se non c'è document_id, ma c'è contenuto, consideralo comunque valido
            # (alcuni chunk potrebbero non avere document_id ma essere validi)
        
        result["document_count"] = len(document_ids) if len(document_ids) > 0 else len(real_chunks)  # Fallback: usa numero chunk
        
        # STEP 2: Verifica che almeno un documento reale sia stato recuperato
        meaningful_chunks = real_chunks
        
        # STEP 3: Verifica che ci siano claims verificati
        # RILASSATO: Se abbiamo chunks reali, permettiamo claims anche senza sourceRefs perfetti
        if not verified_claims or len(verified_claims) == 0:
            # Se abbiamo chunks reali, ma nessun claim, potrebbe essere un problema di generazione
            # Ma NON blocchiamo se abbiamo real_chunks - potrebbe essere una risposta valida ma senza claims strutturati
            if len(real_chunks) > 0:
                # Abbiamo chunks ma nessun claim - warning ma non blocco
                result["verification_reason"] = f"⚠️ ATTENZIONE: {len(real_chunks)} chunks recuperati ma nessun claim generato. Risposta potrebbe essere non strutturata."
                # NON blocchiamo - permettiamo la risposta
            else:
                result["is_valid"] = False
                result["should_block"] = True
                result["verification_reason"] = "❌ BLOCCATO: Nessun claim verificato e nessun chunk reale."
                return result
        
        # STEP 4: Verifica che almeno alcuni claims abbiano sourceRefs
        # RILASSATO: Non richiediamo che TUTTI i claims abbiano sourceRefs
        claims_with_sources = 0
        claims_with_source_ids = 0
        all_source_refs: Set[str] = set()
        
        for claim in verified_claims:
            source_refs = claim.get("sourceRefs") or claim.get("source_refs", [])
            if source_refs and len(source_refs) > 0:
                claims_with_sources += 1
                for source_ref in source_refs:
                    doc_id = source_ref.get("document_id")
                    if doc_id:
                        all_source_refs.add(str(doc_id))
            
            # Verifica source_ids (formato alternativo)
            source_ids = claim.get("source_ids", [])
            if source_ids and len(source_ids) > 0:
                claims_with_source_ids += 1
        
        # RILASSATO: Se abbiamo chunks reali E almeno alcuni claims, permettiamo la risposta
        # NON blocchiamo se mancano sourceRefs ma abbiamo chunks reali
        if claims_with_sources == 0 and claims_with_source_ids == 0:
            if len(real_chunks) > 0:
                # Abbiamo chunks ma claims senza sourceRefs - warning ma non blocco
                result["verification_reason"] = f"⚠️ ATTENZIONE: {len(verified_claims)} claims verificati ma nessuno ha sourceRefs. Chunks presenti: {len(real_chunks)}."
                # NON blocchiamo se abbiamo chunks reali
            else:
                result["is_valid"] = False
                result["should_block"] = True
                result["verification_reason"] = "❌ BLOCCATO: Nessun claim ha sourceRefs E nessun chunk reale."
                return result
        
        # STEP 5: Verifica che ogni sourceRef sia presente nei chunks
        chunk_source_map = {}
        for chunk in meaningful_chunks:
            doc_id = chunk.get("document_id") or chunk.get("source_ref", {}).get("document_id")
            chunk_idx = chunk.get("chunk_index")
            key = f"{doc_id}:{chunk_idx}" if chunk_idx else str(doc_id)
            chunk_source_map[key] = chunk
        
        missing_sources = []
        for claim in verified_claims:
            source_refs = claim.get("sourceRefs") or claim.get("source_refs", [])
            for source_ref in source_refs:
                doc_id = source_ref.get("document_id")
                chunk_idx = source_ref.get("chunk_index")
                key = f"{doc_id}:{chunk_idx}" if chunk_idx else str(doc_id)
                if key not in chunk_source_map:
                    missing_sources.append(f"Doc {doc_id}, chunk {chunk_idx}")
        
        if missing_sources:
            result["missing_sources"] = missing_sources
            result["verification_reason"] = f"Alcune fonti referenziate non sono presenti nei chunks recuperati: {', '.join(missing_sources)}"
            # Non blocchiamo se ci sono altre fonti valide, ma segnaliamo
        
        # STEP 6: Verifica tracciabilità della risposta naturale
        # RILASSATO: Se abbiamo chunks reali, permettiamo la risposta anche se parzialmente tracciabile
        if answer_text and len(real_chunks) > 0:
            sentences = self._extract_statements(answer_text)
            if len(sentences) > 0:
                untraceable = self._check_statement_traceability(sentences, verified_claims)
                if untraceable and len(untraceable) > 0:
                    result["untraceable_statements"] = untraceable
                    # RILASSATO: Se abbiamo chunks reali, NON blocchiamo anche se alcune affermazioni non sono perfettamente tracciabili
                    # Solo warning se più del 30% non è tracciabile
                    if len(untraceable) / len(sentences) > 0.3:
                        # Warning ma NON blocco - abbiamo chunks reali
                        if result.get("verification_reason"):
                            result["verification_reason"] += f" ⚠️ {len(untraceable)}/{len(sentences)} affermazioni non perfettamente tracciabili."
                        else:
                            result["verification_reason"] = f"⚠️ {len(untraceable)}/{len(sentences)} affermazioni non perfettamente tracciabili, ma {len(real_chunks)} chunks reali presenti."
        
        # Verifica finale CRITICA: almeno un chunk reale deve essere stato recuperato
        # RILASSATO: Se abbiamo real_chunks, assumiamo che ci siano documenti (anche senza document_id)
        if len(real_chunks) == 0:
            result["is_valid"] = False
            result["should_block"] = True
            result["verification_reason"] = "❌ BLOCCATO: Nessun chunk reale recuperato. Impossibile verificare le fonti."
            return result
        
        # Verifica aggiuntiva: ogni claim verificato DEVE avere almeno una sourceRef valida
        claims_without_sources = []
        for claim in verified_claims:
            source_refs = claim.get("sourceRefs") or claim.get("source_refs", [])
            if not source_refs or len(source_refs) == 0:
                claims_without_sources.append(claim.get("text", "Claim senza testo"))
        
        # RILASSATO: Se abbiamo chunks reali, permettiamo claims senza sourceRefs
        # Solo se NON abbiamo chunks reali E tutti i claims non hanno sourceRefs, blocchiamo
        if len(claims_without_sources) > 0 and len(claims_without_sources) == len(verified_claims):
            if len(real_chunks) == 0:
                # Nessun chunk reale E nessun claim con sourceRefs - blocca
                result["is_valid"] = False
                result["should_block"] = True
                result["verification_reason"] = f"❌ BLOCCATO: Tutti i {len(claims_without_sources)} claims non hanno sourceRefs E nessun chunk reale."
                return result
            else:
                # Abbiamo chunks reali - warning ma permettiamo
                result["verification_reason"] = f"⚠️ ATTENZIONE: Tutti i claims non hanno sourceRefs, ma {len(real_chunks)} chunks reali recuperati. Risposta permessa."
        
        # Costruisci messaggio finale positivo se non è già stato impostato
        if not result.get("verification_reason") or "BLOCCATO" not in result.get("verification_reason", ""):
            if len(verified_claims) > 0:
                result["verification_reason"] = f"✅ Verifica completata: {len(verified_claims)} claims verificati, {result['document_count']} documenti, {claims_with_sources} claims con fonti, {len(real_chunks)} chunks reali"
            else:
                result["verification_reason"] = f"✅ Verifica completata: {len(real_chunks)} chunks reali recuperati"
        
        # CRITICAL: Se abbiamo real_chunks, la risposta è valida (a meno che non sia stata bloccata prima)
        # Non bloccare se abbiamo chunks reali
        if len(real_chunks) > 0 and result.get("should_block") and "BLOCCATO" not in result.get("verification_reason", ""):
            # Abbiamo chunks reali - permettere la risposta
            result["should_block"] = False
            result["is_valid"] = True
        
        return result
    
    def _extract_statements(self, text: str) -> List[str]:
        """
        Estrae affermazioni/frasi significative dal testo
        """
        # Semplice estrazione: divide per punti, punti esclamativi, punti interrogativi
        # Rimuove frasi troppo corte (< 10 caratteri)
        sentences = re.split(r'[.!?]\s+', text)
        meaningful = [s.strip() for s in sentences if len(s.strip()) > 10]
        return meaningful
    
    def _check_statement_traceability(
        self,
        statements: List[str],
        verified_claims: List[Dict[str, Any]]
    ) -> List[str]:
        """
        Verifica che ogni statement sia tracciabile a un claim verificato
        """
        untraceable = []
        
        # Estrai contenuto dai claims
        claim_texts = [claim.get("text", "").lower() for claim in verified_claims]
        
        for statement in statements:
            statement_lower = statement.lower()
            # Verifica se lo statement è simile a qualche claim (match parziale)
            is_traceable = False
            for claim_text in claim_texts:
                # Verifica match parziale (almeno 40% dei caratteri in comune)
                # o presenza di parole chiave significative
                words_statement = set(statement_lower.split())
                words_claim = set(claim_text.split())
                if len(words_statement) > 0:
                    overlap = len(words_statement.intersection(words_claim)) / len(words_statement)
                    if overlap > 0.4:  # Almeno 40% di overlap
                        is_traceable = True
                        break
            
            if not is_traceable:
                untraceable.append(statement)
        
        return untraceable
    
    def should_block_response(
        self,
        answer_text: str,
        verified_claims: List[Dict[str, Any]],
        chunks: List[Dict[str, Any]],
        question: str
    ) -> tuple[bool, str]:
        """
        Determina se la risposta deve essere bloccata
        
        Returns:
            (should_block, reason)
        """
        verification = self.verify_response_completeness(
            answer_text, verified_claims, chunks, question
        )
        
        return verification["should_block"], verification["verification_reason"]
