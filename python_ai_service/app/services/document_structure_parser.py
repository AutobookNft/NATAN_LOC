"""
Document Structure Parser - Analizza e struttura documenti PA in sezioni logiche
Usa pattern matching + LLM per identificare sezioni senza conoscere a priori i campi
"""

import re
import logging
from typing import Dict, List, Any, Optional
from app.services.ai_router import AIRouter

logger = logging.getLogger(__name__)


class DocumentStructureParser:
    """
    Parser intelligente per documenti PA che identifica sezioni logiche
    senza conoscere a priori i nomi dei campi
    """
    
    # Pattern comuni per documenti PA italiani
    SECTION_PATTERNS = {
        'intestazione': [
            r'^(GIUNTA|CONSIGLIO|COMUNE|PROVINCIA|REGIONE)\s+(COMUNALE|PROVINCIALE|REGIONALE)?',
            r'^ESTRATTO\s+DAL\s+VERBALE',
            r'^DELIBERAZIONE\s+N\.',
        ],
        'numero_data': [
            r'DELIBERAZIONE\s+N\.\s+([A-Z]+/\d+/\d+)',
            r'PROPOSTA\s+N\.\s*([A-Z]+/\d+/\d+)',
            r'(\d{2}/\d{2}/\d{4})',  # Date
        ],
        'oggetto': [
            r'^Oggetto:\s*(.+?)(?=\n\n|\n[A-Z]{2,})',
            r'^OGGETTO:\s*(.+?)(?=\n\n|\n[A-Z]{2,})',
            r'^Oggetto\s+(.+?)(?=\n\n|\n[A-Z]{2,})',
        ],
        'premesso': [
            r'^PREMESSO\s+[Cc]he\s+(.+?)(?=\n\nVISTO|\n\nDELIBERA|\n\nCONSIDERATO)',
            r'^Premesso\s+[Cc]he\s+(.+?)(?=\n\nVISTO|\n\nDELIBERA|\n\nCONSIDERATO)',
        ],
        'visto': [
            r'^VISTO\s+(.+?)(?=\n\nDELIBERA|\n\nCONSIDERATO|\n\nRITENUTO)',
            r'^Visto\s+(.+?)(?=\n\nDELIBERA|\n\nCONSIDERATO|\n\nRITENUTO)',
        ],
        'considerato': [
            r'^CONSIDERATO\s+(.+?)(?=\n\nDELIBERA|\n\nRITENUTO)',
            r'^Considerato\s+(.+?)(?=\n\nDELIBERA|\n\nRITENUTO)',
        ],
        'delibera': [
            r'^DELIBERA\s+(.+?)(?=\n\nALLEGATI|\n\nFirma|\n\nIl\s+Presidente)',
            r'^Delibera\s+(.+?)(?=\n\nALLEGATI|\n\nFirma|\n\nIl\s+Presidente)',
            r'^SI\s+DELIBERA\s+(.+?)(?=\n\nALLEGATI|\n\nFirma|\n\nIl\s+Presidente)',
        ],
        'allegati': [
            r'^ALLEGATI?:\s*(.+?)(?=\n\nFirma|\n\nIl\s+Presidente|\n\nPag\.)',
            r'^Allegati?:\s*(.+?)(?=\n\nFirma|\n\nIl\s+Presidente|\n\nPag\.)',
        ],
        'firme': [
            r'^(Il\s+Presidente|Il\s+Sindaco|La\s+Vice\s+Segretaria|Il\s+Segretario)',
            r'^Le\s+firme',
            r'^Firma',
        ],
    }
    
    def __init__(self):
        self.ai_router = AIRouter()
    
    def parse_structure(self, text: str, use_llm: bool = True) -> Dict[str, Any]:
        """
        Analizza il testo e identifica sezioni logiche
        
        Args:
            text: Testo completo del documento
            use_llm: Se True, usa LLM per analisi più approfondita
        
        Returns:
            Dict con sezioni identificate e testo strutturato
        """
        if not text or len(text.strip()) < 100:
            return {
                'sections': {},
                'structured_text': text,
                'section_count': 0
            }
        
        # Step 1: Pattern matching per sezioni comuni
        sections = self._extract_sections_with_patterns(text)
        
        # Step 2: Se use_llm, usa LLM per identificare sezioni mancanti o migliorare
        if use_llm and len(sections) < 3:
            llm_sections = self._extract_sections_with_llm(text)
            # Merge: LLM sections hanno priorità se pattern matching ha trovato poco
            if len(llm_sections) > len(sections):
                sections = llm_sections
        
        # Step 3: Organizza il testo in sezioni ordinate
        structured_text = self._organize_text_by_sections(text, sections)
        
        return {
            'sections': sections,
            'structured_text': structured_text,
            'section_count': len(sections),
            'section_names': list(sections.keys())
        }
    
    def _extract_sections_with_patterns(self, text: str) -> Dict[str, str]:
        """Estrae sezioni usando pattern matching"""
        sections = {}
        lines = text.split('\n')
        
        # Normalizza testo per ricerca
        normalized_text = text.replace('\r', '\n')
        
        # Cerca ogni tipo di sezione
        for section_type, patterns in self.SECTION_PATTERNS.items():
            for pattern in patterns:
                matches = re.finditer(pattern, normalized_text, re.MULTILINE | re.IGNORECASE | re.DOTALL)
                for match in matches:
                    if section_type not in sections:  # Prendi solo la prima occorrenza
                        start_pos = match.start()
                        # Estrai il contenuto della sezione fino alla prossima sezione o fine documento
                        section_content = self._extract_section_content(
                            normalized_text, start_pos, section_type
                        )
                        if section_content and len(section_content.strip()) > 20:
                            sections[section_type] = section_content.strip()
                            break
                if section_type in sections:
                    break
        
        return sections
    
    def _extract_section_content(self, text: str, start_pos: int, section_type: str) -> str:
        """Estrae il contenuto di una sezione fino alla prossima sezione"""
        # Trova la fine della sezione (prossima sezione o fine documento)
        remaining_text = text[start_pos:]
        
        # Cerca la prossima sezione (pattern che iniziano con maiuscole o keyword)
        next_section_patterns = [
            r'\n\n[A-Z]{2,}\s+[A-Z]',  # Parola tutta maiuscola seguita da spazio
            r'\n\n(OGGETTO|PREMESSO|VISTO|DELIBERA|ALLEGATI|Firma|Il\s+Presidente)',
            r'\n\nPag\.\s+\d+',  # Fine documento
        ]
        
        end_pos = len(remaining_text)
        for pattern in next_section_patterns:
            match = re.search(pattern, remaining_text, re.IGNORECASE)
            if match and match.start() < end_pos:
                end_pos = match.start()
        
        section_text = remaining_text[:end_pos].strip()
        
        # Rimuovi il titolo della sezione dal contenuto
        lines = section_text.split('\n')
        if len(lines) > 1:
            # Rimuovi prima riga se è il titolo (es: "OGGETTO:", "PREMESSO che")
            first_line = lines[0].strip()
            if any(keyword in first_line.upper() for keyword in ['OGGETTO', 'PREMESSO', 'VISTO', 'DELIBERA', 'ALLEGATI']):
                section_text = '\n'.join(lines[1:]).strip()
        
        return section_text
    
    def _extract_sections_with_llm(self, text: str) -> Dict[str, str]:
        """Usa LLM per identificare sezioni nel documento (DISABILITATO - richiede async)"""
        # TODO: Implementare versione async o chiamata sync
        # Per ora, ritorna vuoto e usa solo pattern matching
        logger.debug("LLM structure parsing disabilitato (richiede async context)")
        return {}
        
        # Codice originale (da riabilitare quando avremo async):
        try:
            # Prendi solo i primi 8000 caratteri per non superare limiti token
            text_sample = text[:8000] if len(text) > 8000 else text
            
            prompt = f"""Analizza questo documento amministrativo italiano e identifica le sezioni logiche principali.

Documento:
{text_sample}

Identifica e estrai le seguenti sezioni (se presenti):
1. "intestazione" - Intestazione del documento (es: "GIUNTA COMUNALE", "ESTRATTO DAL VERBALE")
2. "numero_data" - Numero e data della delibera/atto
3. "oggetto" - Oggetto della delibera/atto
4. "premesso" - Sezione "PREMESSO che" con considerazioni preliminari
5. "visto" - Sezione "VISTO" con riferimenti normativi
6. "considerato" - Sezione "CONSIDERATO" con valutazioni
7. "delibera" - Parte operativa "DELIBERA" o "SI DELIBERA"
8. "allegati" - Elenco allegati
9. "firme" - Sezione firme e validazione

Rispondi SOLO con un JSON valido in questo formato:
{{
  "intestazione": "testo sezione o null",
  "numero_data": "testo sezione o null",
  "oggetto": "testo sezione o null",
  "premesso": "testo sezione o null",
  "visto": "testo sezione o null",
  "considerato": "testo sezione o null",
  "delibera": "testo sezione o null",
  "allegati": "testo sezione o null",
  "firme": "testo sezione o null"
}}

Se una sezione non esiste, usa null. Non inventare contenuti."""
            
            # Usa AI Router per chiamare LLM
            adapter = self.ai_router.get_chat_adapter({"model": "anthropic.sonnet-3.5"})
            response = adapter.generate(
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1
            )
            
            if response and 'content' in response:
                content = response['content']
                # Estrai JSON dalla risposta
                json_match = re.search(r'\{.*\}', content, re.DOTALL)
                if json_match:
                    import json
                    try:
                        sections_json = json.loads(json_match.group())
                        # Filtra solo sezioni non null
                        sections = {k: v for k, v in sections_json.items() if v and v != 'null'}
                        logger.info(f"LLM ha identificato {len(sections)} sezioni")
                        return sections
                    except json.JSONDecodeError:
                        logger.warning("Errore parsing JSON da LLM")
            
        except Exception as e:
            logger.warning(f"Errore estrazione sezioni con LLM: {e}")
        
        return {}
    
    def _organize_text_by_sections(self, text: str, sections: Dict[str, str]) -> str:
        """Organizza il testo in sezioni ordinate"""
        if not sections:
            return text
        
        # Ordine logico delle sezioni
        section_order = [
            'intestazione',
            'numero_data',
            'oggetto',
            'premesso',
            'visto',
            'considerato',
            'delibera',
            'allegati',
            'firme'
        ]
        
        organized_parts = []
        for section_name in section_order:
            if section_name in sections:
                organized_parts.append(f"[{section_name.upper()}]\n{sections[section_name]}\n")
        
        # Aggiungi testo rimanente se ci sono sezioni non identificate
        if organized_parts:
            return '\n---\n\n'.join(organized_parts)
        else:
            return text

