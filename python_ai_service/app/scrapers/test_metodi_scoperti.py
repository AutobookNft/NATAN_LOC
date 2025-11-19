#!/usr/bin/env python3
"""
TEST METODI SCOPERTI - Testa i metodi trovati dall'analisi profonda
"""
import requests
import json
from datetime import datetime

def test_scandicci_soluzionipa():
    """
    Scandicci - Form POST scoperto
    Problema: formato date era DD/MM/YYYY, deve essere YYYY-MM-DD
    """
    print("="*80)
    print("SCANDICCI - SoluzioniPA Form POST")
    print("="*80)
    
    url = "https://scandicci.soluzionipa.it/openweb/albo/albo_pretorio.php"
    
    # Dati form CORRETTI (date in formato YYYY-MM-DD)
    data = {
        'tabella_albo[page]': '1',
        'tabella_albo[start]': '0',
        'search_albo[DATA_AFFIS_1]': '2025-01-01',  # CORRETTO
        'search_albo[DATA_AFFIS_2]': '2025-11-13',  # CORRETTO
        'search_albo[OGGETTO]': '',
        'search_albo[TIPOATTO]': '',
        'search_albo[UFFICIO]': '',
        'search_albo[NRATTO]': '',
        'search_albo[DATA_ATTO_1]': '2025-01-01',
        'search_albo[DATA_ATTO_2]': '2025-11-13',
        'submit': 'Cerca'
    }
    
    try:
        response = requests.post(url, data=data, timeout=15)
        print(f"Status: {response.status_code}")
        
        # Analizza risposta
        content = response.text
        
        # Cerca indicatori atti
        import re
        delibere = re.findall(r'delibera', content, re.I)
        determine = re.findall(r'determinazione', content, re.I)
        
        # Cerca righe tabella
        tr_count = content.count('<tr')
        
        print(f"Delibere keyword: {len(delibere)}")
        print(f"Determine keyword: {len(determine)}")
        print(f"Table rows: {tr_count}")
        
        if tr_count > 5 or len(delibere) > 0:
            print("✅ METODO FUNZIONANTE - Atti trovati!")
            return True
        else:
            print(f"❌ 0 atti (HTML length: {len(content)})")
            # Salva HTML per debug
            with open('scandicci_response.html', 'w') as f:
                f.write(content)
            print("HTML salvato in scandicci_response.html")
            return False
            
    except Exception as e:
        print(f"❌ Errore: {e}")
        return False

def test_sesto_fiorentino_api():
    """
    Sesto Fiorentino - API DataTables scoperta
    """
    print("\n" + "="*80)
    print("SESTO FIORENTINO - DataTables API")
    print("="*80)
    
    # URL base API
    base_url = "http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php"
    
    # Parametri semplificati
    params = {
        'draw': '1',
        'start': '0',
        'length': '100',  # 100 risultati
        'search[value]': '',
        'search[regex]': 'false'
    }
    
    try:
        response = requests.get(base_url, params=params, timeout=15)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            
            print(f"Struttura JSON:")
            print(f"  - recordsTotal: {data.get('recordsTotal', 'N/A')}")
            print(f"  - recordsFiltered: {data.get('recordsFiltered', 'N/A')}")
            print(f"  - data array length: {len(data.get('data', []))}")
            
            if data.get('recordsTotal', 0) > 0:
                print("✅ METODO FUNZIONANTE - API risponde con dati!")
                
                # Mostra primi 3 atti
                for i, atto in enumerate(data.get('data', [])[:3], 1):
                    print(f"\n  Atto {i}: {atto}")
                
                return True
            else:
                print("❌ API risponde ma 0 atti")
                return False
        else:
            print(f"❌ Status code: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Errore: {e}")
        return False

def test_trasparenzavm_portlet():
    """
    TrasparenzaVM - Molti comuni usano questa piattaforma
    Es: Empoli, Campi Bisenzio, Bagno a Ripoli, Fiesole, Pontassieve, Borgo San Lorenzo
    
    Dobbiamo capire come funziona il loro portlet di ricerca
    """
    print("\n" + "="*80)
    print("TRASPARENZA VM - Analisi portlet")
    print("="*80)
    
    # Testa Empoli come esempio
    url = "https://empoli.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio"
    
    try:
        # Prima richiesta per capire struttura
        response = requests.get(url, timeout=15)
        content = response.text
        
        # Cerca portlet namespace
        import re
        namespace_match = re.search(r'namespace[\'"]?\s*:\s*[\'"]([^\'"]+)[\'"]', content)
        if namespace_match:
            namespace = namespace_match.group(1)
            print(f"Namespace trovato: {namespace}")
        
        # Cerca form action
        form_action = re.search(r'action=[\'"]([^\'"]*/albo[^\'"]*)[\'"]', content)
        if form_action:
            print(f"Form action: {form_action.group(1)}")
        
        # Cerca parametri nascosti
        hidden_inputs = re.findall(r'<input[^>]*type=[\'"]hidden[\'"][^>]*>', content)
        print(f"Hidden inputs: {len(hidden_inputs)}")
        
        # Cerca se c'è un'API REST
        api_matches = re.findall(r'[\'"]([^\'"]*\/api\/[^\'"]*)[\'"]', content)
        if api_matches:
            print(f"Possibili API: {api_matches[:3]}")
        
        print("\n⚠️  TrasparenzaVM richiede analisi più approfondita")
        print("Suggerimento: Usare Playwright per intercettare chiamate AJAX")
        return False
        
    except Exception as e:
        print(f"❌ Errore: {e}")
        return False


if __name__ == '__main__':
    risultati = {
        'timestamp': datetime.now().isoformat(),
        'tests': []
    }
    
    # Test 1: Scandicci
    print("\n\n")
    scandicci_ok = test_scandicci_soluzionipa()
    risultati['tests'].append({
        'comune': 'Scandicci',
        'metodo': 'SoluzioniPA_FormPOST',
        'funzionante': scandicci_ok
    })
    
    # Test 2: Sesto Fiorentino
    print("\n\n")
    sesto_ok = test_sesto_fiorentino_api()
    risultati['tests'].append({
        'comune': 'Sesto Fiorentino',
        'metodo': 'DataTables_API',
        'funzionante': sesto_ok
    })
    
    # Test 3: TrasparenzaVM
    print("\n\n")
    trasparenza_ok = test_trasparenzavm_portlet()
    risultati['tests'].append({
        'comune': 'Empoli (esempio TrasparenzaVM)',
        'metodo': 'TrasparenzaVM_Portlet',
        'funzionante': trasparenza_ok
    })
    
    # Riepilogo
    print("\n" + "="*80)
    print("RIEPILOGO TEST METODI SCOPERTI")
    print("="*80)
    
    funzionanti = sum(1 for t in risultati['tests'] if t['funzionante'])
    print(f"Metodi testati: {len(risultati['tests'])}")
    print(f"Funzionanti: {funzionanti}")
    
    for test in risultati['tests']:
        status = "✅" if test['funzionante'] else "❌"
        print(f"{status} {test['comune']} - {test['metodo']}")
    
    # Salva risultati
    with open('metodi_scoperti_results.json', 'w') as f:
        json.dump(risultati, f, indent=2)
    
    print("\n💾 Risultati salvati in metodi_scoperti_results.json")
