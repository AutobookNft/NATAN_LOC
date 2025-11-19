#!/usr/bin/env python3
"""
Ricerca Automatica PEC Comuni
==============================
Trova automaticamente gli indirizzi PEC dei comuni italiani.

Fonti:
1. IndicePA (ufficiale)
2. IPA Pubblica Amministrazione
3. Sito web comune (parsing)
"""

import requests
from bs4 import BeautifulSoup
import re
import json
from typing import Optional


class PECFinder:
    """Trova indirizzi PEC comuni"""
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
        })
        
        # Cache PEC trovate
        self.cache = {}
    
    def cerca_indicepa(self, nome_comune: str) -> Optional[str]:
        """
        Cerca PEC su IndicePA (fonte ufficiale)
        
        IndicePA: https://indicepa.gov.it/
        """
        try:
            # API IndicePA
            url = 'https://indicepa.gov.it/ipa-dati/api/rest/ws-read-nomefile'
            
            # Ricerca comune
            params = {
                'search': nome_comune,
                'tipo': 'c'  # Comuni
            }
            
            resp = self.session.get(url, params=params, timeout=10)
            
            if resp.status_code == 200:
                data = resp.json()
                
                # Estrai PEC
                if 'data' in data and len(data['data']) > 0:
                    comune_data = data['data'][0]
                    pec = comune_data.get('mail1', '')
                    
                    if pec and '@' in pec:
                        return pec.strip().lower()
        
        except Exception as e:
            pass
        
        return None
    
    def cerca_sito_comune(self, url_sito: str) -> Optional[str]:
        """
        Cerca PEC sul sito del comune (parsing HTML)
        """
        try:
            resp = self.session.get(url_sito, timeout=10)
            
            if resp.status_code != 200:
                return None
            
            soup = BeautifulSoup(resp.content, 'html.parser')
            
            # Regex PEC
            pec_pattern = r'[\w\.-]+@pec\.[\w\.-]+'
            
            # Cerca nel testo
            text = soup.get_text()
            matches = re.findall(pec_pattern, text, re.IGNORECASE)
            
            if matches:
                # Filtra PEC comune (non personali)
                for match in matches:
                    match_lower = match.lower()
                    if any(keyword in match_lower for keyword in 
                          ['comune', 'protocollo', 'segreteria', 'trasparenza']):
                        return match.strip().lower()
                
                # Se non trovata PEC specifica, restituisci prima
                return matches[0].strip().lower()
        
        except Exception as e:
            pass
        
        return None
    
    def genera_pec_probabile(self, nome_comune: str) -> str:
        """
        Genera PEC probabile basata su pattern comuni
        
        Pattern tipici:
        - protocollo@pec.comune.{nome}.{provincia}.it
        - comune.{nome}@pec.it
        - segreteria@pec.comune.{nome}.it
        """
        # Normalizza nome
        nome_norm = nome_comune.lower().replace(' ', '').replace("'", '')
        
        # Pattern comuni
        patterns = [
            f'protocollo@pec.comune.{nome_norm}.it',
            f'comune.{nome_norm}@pec.it',
            f'protocollo.comune.{nome_norm}@pec.it',
            f'segreteria@pec.comune.{nome_norm}.it'
        ]
        
        return patterns[0]  # Restituisci più probabile
    
    def trova_pec(self, nome_comune: str, url_sito: str = None) -> dict:
        """
        Trova PEC comune usando tutte le fonti disponibili
        
        Returns:
            {
                'pec': 'indirizzo@pec.it',
                'fonte': 'indicepa|sito|generata',
                'affidabilita': 'alta|media|bassa'
            }
        """
        # Check cache
        if nome_comune in self.cache:
            return self.cache[nome_comune]
        
        result = {
            'pec': None,
            'fonte': None,
            'affidabilita': 'bassa'
        }
        
        # 1. IndicePA (fonte ufficiale - affidabilità alta)
        pec = self.cerca_indicepa(nome_comune)
        if pec:
            result['pec'] = pec
            result['fonte'] = 'indicepa'
            result['affidabilita'] = 'alta'
            self.cache[nome_comune] = result
            return result
        
        # 2. Sito comune (affidabilità media)
        if url_sito:
            pec = self.cerca_sito_comune(url_sito)
            if pec:
                result['pec'] = pec
                result['fonte'] = 'sito'
                result['affidabilita'] = 'media'
                self.cache[nome_comune] = result
                return result
        
        # 3. Genera PEC probabile (affidabilità bassa)
        result['pec'] = self.genera_pec_probabile(nome_comune)
        result['fonte'] = 'generata'
        result['affidabilita'] = 'bassa'
        
        self.cache[nome_comune] = result
        return result
    
    def batch_trova_pec(self, comuni: list) -> dict:
        """
        Trova PEC per lista di comuni
        
        Args:
            comuni: [{'nome': 'Firenze', 'url': '...'}, ...]
        
        Returns:
            {'Firenze': {'pec': '...', 'fonte': '...', 'affidabilita': '...'}}
        """
        risultati = {}
        
        for i, comune in enumerate(comuni, 1):
            nome = comune['nome']
            url = comune.get('url', '')
            
            print(f"[{i}/{len(comuni)}] Cerco PEC {nome}...", end=' ')
            
            result = self.trova_pec(nome, url)
            risultati[nome] = result
            
            print(f"✓ {result['pec']} ({result['affidabilita']})")
        
        return risultati
    
    def salva_cache(self, filename='pec_comuni_cache.json'):
        """Salva cache PEC"""
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(self.cache, f, indent=2, ensure_ascii=False)
    
    def carica_cache(self, filename='pec_comuni_cache.json'):
        """Carica cache PEC"""
        try:
            with open(filename, 'r', encoding='utf-8') as f:
                self.cache = json.load(f)
        except FileNotFoundError:
            pass


# ESEMPIO DI USO
if __name__ == '__main__':
    finder = PECFinder()
    
    # Carica cache esistente
    finder.carica_cache()
    
    # Test comuni
    comuni_test = [
        {'nome': 'Firenze', 'url': 'https://www.comune.fi.it'},
        {'nome': 'Pisa', 'url': 'https://www.comune.pisa.it'},
        {'nome': 'Livorno', 'url': 'https://www.comune.livorno.it'},
        {'nome': 'Arezzo', 'url': 'https://www.comune.arezzo.it'},
        {'nome': 'Siena', 'url': 'https://www.comune.siena.it'}
    ]
    
    print("🔍 Ricerca PEC Comuni Toscana\n")
    
    risultati = finder.batch_trova_pec(comuni_test)
    
    # Statistiche
    print("\n📊 STATISTICHE")
    print("="*60)
    
    stats = {
        'indicepa': 0,
        'sito': 0,
        'generata': 0
    }
    
    for nome, info in risultati.items():
        stats[info['fonte']] += 1
    
    print(f"Fonte IndicePA (alta affidabilità): {stats['indicepa']}")
    print(f"Fonte Sito (media affidabilità):    {stats['sito']}")
    print(f"Generata (bassa affidabilità):      {stats['generata']}")
    
    # Salva cache
    finder.salva_cache()
    print("\n✅ Cache salvata in pec_comuni_cache.json")
    
    # Output JSON
    print("\n📋 Risultati JSON:")
    print(json.dumps(risultati, indent=2, ensure_ascii=False))
