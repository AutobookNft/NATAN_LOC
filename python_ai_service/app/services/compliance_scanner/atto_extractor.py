"""
Estrattore atti da Albo Pretorio
Conta e estrae realmente gli atti pubblicati
"""

import re
import logging
from typing import List, Dict, Optional
from bs4 import BeautifulSoup
from datetime import datetime

logger = logging.getLogger(__name__)

class AttoExtractor:
    """
    Estrae atti reali da HTML Albo Pretorio
    Pattern comuni per comuni toscani
    """
    
    def extract_atti(self, html_content: str, url: str) -> List[Dict]:
        """
        Estrae atti dall'HTML con pattern multipli
        
        Returns:
            Lista di atti con: numero, data, oggetto, tipo, link
        """
        if not html_content:
            return []
        
        try:
            soup = BeautifulSoup(html_content, 'html.parser')
            atti = []
            
            # Pattern 0: Cerca nel testo completo per pattern delibera/determina
            full_text = soup.get_text()
            text_patterns = re.findall(
                r'(delibera|determina|ordinanza|atto)\s+n\.?\s*(\d+)[/\-](\d{4})',
                full_text,
                re.IGNORECASE
            )
            for tipo, num, anno in text_patterns:
                atti.append({
                    'numero': f"{num}/{anno}",
                    'data': 'N/A',
                    'oggetto': f"{tipo.title()} n. {num}/{anno}",
                    'tipo': tipo.lower(),
                    'link': url
                })
            
            # Pattern 1: Tabelle con atti (pattern più comune)
            tables = soup.find_all('table')
            for table in tables:
                rows = table.find_all('tr')
                for row in rows[1:]:  # Skip header
                    cells = row.find_all(['td', 'th'])
                    if len(cells) >= 2:  # Almeno 2 celle
                        atto = self._parse_table_row(cells, url)
                        if atto:
                            atti.append(atto)
            
            # Pattern 2: Liste con atti
            lists = soup.find_all(['ul', 'ol'])
            for list_elem in lists:
                items = list_elem.find_all('li')
                for item in items:
                    atto = self._parse_list_item(item, url)
                    if atto:
                        atti.append(atto)
            
            # Pattern 3: Div con classi specifiche (es: "atto", "albo-item")
            divs = soup.find_all('div', class_=re.compile(r'atto|albo|pubblicazione|item', re.I))
            for div in divs:
                atto = self._parse_div(div, url)
                if atto:
                    atti.append(atto)
            
            # Pattern 4: Link con pattern numeri atti (es: "Delibera n. 123/2024")
            links = soup.find_all('a', href=True)
            for link in links:
                text = link.get_text(strip=True)
                if re.search(r'(delibera|determina|ordinanza|atto).*?\d+[/\-]\d{4}', text, re.I):
                    atto = self._parse_link(link, url)
                    if atto:
                        atti.append(atto)
            
            # Pattern 5: Cerca in tutti i tag con testo che contiene pattern
            all_elements = soup.find_all(['p', 'div', 'span', 'td', 'li'])
            for elem in all_elements:
                text = elem.get_text(strip=True)
                match = re.search(r'(delibera|determina|ordinanza|atto)\s+n\.?\s*(\d+)[/\-](\d{4})', text, re.I)
                if match and len(text) < 500:  # Evita elementi troppo lunghi
                    atti.append({
                        'numero': f"{match.group(2)}/{match.group(3)}",
                        'data': 'N/A',
                        'oggetto': text[:200],
                        'tipo': match.group(1).lower(),
                        'link': url
                    })
            
            # Rimuovi duplicati (stesso numero)
            unique_atti = {}
            for atto in atti:
                key = str(atto.get('numero', '')) + str(atto.get('tipo', ''))
                if key and key not in unique_atti:
                    unique_atti[key] = atto
            
            logger.info(f"Estratti {len(unique_atti)} atti unici da {url} (su {len(atti)} trovati)")
            
            return list(unique_atti.values())
            
        except Exception as e:
            logger.error(f"Errore estrazione atti: {e}", exc_info=True)
            return []
    
    def _parse_table_row(self, cells, base_url: str) -> Optional[Dict]:
        """Estrae atto da riga tabella"""
        try:
            # Cerca numero atto
            numero = None
            data = None
            oggetto = None
            tipo = None
            link = None
            
            for cell in cells:
                text = cell.get_text(strip=True)
                
                # Cerca numero (es: "123/2024", "n. 456")
                if not numero:
                    match = re.search(r'(?:n\.?\s*)?(\d+)[/\-](\d{4})', text)
                    if match:
                        numero = f"{match.group(1)}/{match.group(2)}"
                
                # Cerca data (es: "15/03/2024", "2024-03-15")
                if not data:
                    match = re.search(r'(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})', text)
                    if match:
                        data = text
                
                # Cerca tipo (delibera, determina, ordinanza)
                if not tipo:
                    tipo_match = re.search(r'(delibera|determina|ordinanza|atto)', text, re.I)
                    if tipo_match:
                        tipo = tipo_match.group(1).lower()
                
                # Cerca link
                link_elem = cell.find('a', href=True)
                if link_elem and not link:
                    href = link_elem['href']
                    if href.startswith('http'):
                        link = href
                    elif href.startswith('/'):
                        from urllib.parse import urljoin
                        link = urljoin(base_url, href)
            
            # Oggetto è di solito la cella più lunga
            oggetto = max([c.get_text(strip=True) for c in cells], key=len)
            
            if numero or data or oggetto:
                return {
                    'numero': numero or 'N/A',
                    'data': data or 'N/A',
                    'oggetto': oggetto[:200] if oggetto else 'N/A',
                    'tipo': tipo or 'N/A',
                    'link': link or base_url
                }
        except Exception as e:
            logger.debug(f"Errore parsing riga tabella: {e}")
        
        return None
    
    def _parse_list_item(self, item, base_url: str) -> Optional[Dict]:
        """Estrae atto da item lista"""
        try:
            text = item.get_text(strip=True)
            
            # Cerca pattern atto
            match = re.search(r'(delibera|determina|ordinanza|atto)\s*n\.?\s*(\d+)[/\-](\d{4})', text, re.I)
            if match:
                tipo = match.group(1).lower()
                numero = f"{match.group(2)}/{match.group(3)}"
                
                # Cerca data
                data_match = re.search(r'(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})', text)
                data = data_match.group(0) if data_match else 'N/A'
                
                # Link
                link_elem = item.find('a', href=True)
                link = None
                if link_elem:
                    href = link_elem['href']
                    if href.startswith('http'):
                        link = href
                    elif href.startswith('/'):
                        from urllib.parse import urljoin
                        link = urljoin(base_url, href)
                
                return {
                    'numero': numero,
                    'data': data,
                    'oggetto': text[:200],
                    'tipo': tipo,
                    'link': link or base_url
                }
        except:
            pass
        
        return None
    
    def _parse_div(self, div, base_url: str) -> Optional[Dict]:
        """Estrae atto da div"""
        text = div.get_text(strip=True)
        match = re.search(r'(delibera|determina|ordinanza|atto)\s*n\.?\s*(\d+)[/\-](\d{4})', text, re.I)
        if match:
            return {
                'numero': f"{match.group(2)}/{match.group(3)}",
                'data': 'N/A',
                'oggetto': text[:200],
                'tipo': match.group(1).lower(),
                'link': base_url
            }
        return None
    
    def _parse_link(self, link, base_url: str) -> Optional[Dict]:
        """Estrae atto da link"""
        text = link.get_text(strip=True)
        match = re.search(r'(delibera|determina|ordinanza|atto)\s*n\.?\s*(\d+)[/\-](\d{4})', text, re.I)
        if match:
            href = link['href']
            if not href.startswith('http'):
                from urllib.parse import urljoin
                href = urljoin(base_url, href)
            
            return {
                'numero': f"{match.group(2)}/{match.group(3)}",
                'data': 'N/A',
                'oggetto': text[:200],
                'tipo': match.group(1).lower(),
                'link': href
            }
        return None

