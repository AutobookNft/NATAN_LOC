#!/usr/bin/env python3
"""
Diagnostic Scraper - Analizza Problemi e Suggerisce Soluzioni
==============================================================
Per ogni comune che non funziona:
1. Identifica il problema tecnico specifico
2. Formula la soluzione ideale
3. Genera suggerimento costruttivo per il comune

Questo dimostra la nostra volontà di collaborare, non solo criticare.
"""

import asyncio
import json
from datetime import datetime
from typing import Dict, List, Optional
from enum import Enum
import requests
from bs4 import BeautifulSoup
from playwright.async_api import async_playwright


class ProblemaType(Enum):
    """Tipologie di problemi identificati"""
    CLOUDFLARE_PROTECTION = "cloudflare_protection"
    CAPTCHA = "captcha"
    JAVASCRIPT_REQUIRED = "javascript_required"
    NO_API = "no_api"
    FORM_COMPLEX = "form_complex"
    NO_OPEN_FORMAT = "no_open_format"
    PAGINATION_BROKEN = "pagination_broken"
    NO_SEARCH = "no_search"
    LOGIN_REQUIRED = "login_required"
    IFRAME_EXTERNAL = "iframe_external"
    MIXED_CONTENT = "mixed_content"
    CORS_BLOCKING = "cors_blocking"
    UNKNOWN = "unknown"


class DiagnosticResult:
    """Risultato diagnostica comune"""
    
    def __init__(self, comune: str, url: str):
        self.comune = comune
        self.url = url
        self.problemi = []
        self.soluzioni = []
        self.suggerimento_email = ""
        self.priorita = "media"
        self.fattibilita_fix = "alta"
        self.timestamp = datetime.now().isoformat()
    
    def add_problema(self, tipo: ProblemaType, dettagli: str):
        """Aggiunge problema identificato"""
        self.problemi.append({
            'tipo': tipo.value,
            'dettagli': dettagli
        })
    
    def add_soluzione(self, soluzione: str, costo: str, tempo: str):
        """Aggiunge soluzione proposta"""
        self.soluzioni.append({
            'descrizione': soluzione,
            'costo_stimato': costo,
            'tempo_implementazione': tempo
        })
    
    def genera_suggerimento_email(self):
        """Genera email costruttiva per il comune"""
        
        if not self.problemi:
            return "Nessun problema rilevato - comune già conforme!"
        
        problema_principale = self.problemi[0]
        tipo = problema_principale['tipo']
        
        # Template base
        email = f"""Spett.le Comune di {self.comune},

Durante l'analisi tecnica del vostro Albo Pretorio per il progetto NATAN, abbiamo riscontrato alcune criticità nell'accesso automatizzato ai dati pubblici che vorremmo segnalarvi in modo costruttivo.

## 📋 Problema Identificato

{self._descrivi_problema(tipo)}

Dettagli tecnici: {problema_principale['dettagli']}

## ✅ Soluzione Proposta

{self._soluzione_per_tipo(tipo)}

## 🎯 Benefici della Soluzione

✅ **Conformità normativa**: Aderenza all'Art. 35 D.lgs 33/2013 (formato aperto)
✅ **Migliore servizio cittadini**: Accesso facilitato alle informazioni
✅ **Riduzione carico URP**: Meno richieste manuali di documentazione
✅ **Interoperabilità**: Dati utilizzabili da altri servizi digitali
✅ **Trasparenza proattiva**: Il Comune come esempio di PA digitale

## 🤝 Nostra Disponibilità

Siamo disponibili a:
- Fornire consulenza tecnica gratuita
- Testare la soluzione implementata
- Documentare il vostro Comune come caso di successo
- Supportare l'ufficio tecnico nell'implementazione

## 📊 Stima Implementazione

"""
        
        if self.soluzioni:
            sol = self.soluzioni[0]
            email += f"""Costo stimato: {sol['costo_stimato']}
Tempo stimato: {sol['tempo_implementazione']}
Fattibilità: {self.fattibilita_fix}
Priorità: {self.priorita}

"""
        
        email += """Questo intervento rientra negli obblighi di accessibilità previsti dalla normativa vigente e rappresenta un'opportunità per migliorare il servizio ai cittadini.

Restiamo a disposizione per ogni chiarimento e supporto tecnico.

Cordiali saluti,

Fabio Massacci
Progetto NATAN
Email: fabio@natan.it
"""
        
        self.suggerimento_email = email
        return email
    
    def _descrivi_problema(self, tipo: str) -> str:
        """Descrizione user-friendly del problema"""
        
        descrizioni = {
            'cloudflare_protection': """
Il vostro Albo Pretorio utilizza **Cloudflare** con protezione anti-bot attiva.

Problema: Cloudflare blocca l'accesso automatizzato ai dati pubblici, impedendo a cittadini e servizi digitali di accedere programmaticamente agli atti amministrativi.

Violazione normativa: L'Art. 35 D.lgs 33/2013 prevede che gli atti siano pubblicati in "formato di tipo aperto" e riutilizzabili. Una protezione anti-bot su dati pubblici contrasta questo obbligo.""",
            
            'javascript_required': """
Il vostro Albo Pretorio richiede **JavaScript obbligatorio** per visualizzare i dati.

Problema: Gli atti sono caricati dinamicamente via JavaScript, rendendo impossibile l'accesso per screen reader (accessibilità) e sistemi automatici.

Impatto: Cittadini con disabilità visive e servizi di automazione non possono accedere ai dati.""",
            
            'no_api': """
Il vostro Albo Pretorio **non espone API** per l'accesso automatizzato.

Problema: I dati sono disponibili solo tramite interfaccia web HTML, senza endpoint API per il recupero in formato strutturato (JSON, XML, CSV).

Limitazione: Impossibile creare servizi digitali che utilizzino i vostri dati pubblici.""",
            
            'captcha': """
Il vostro Albo Pretorio utilizza **CAPTCHA** per l'accesso.

Problema: Il CAPTCHA impedisce l'accesso automatizzato e crea barriere per utenti con disabilità visive.

Violazione accessibilità: Contrasta le WCAG 2.1 (Web Content Accessibility Guidelines).""",
            
            'form_complex': """
Il form di ricerca del vostro Albo Pretorio presenta **complessità tecniche** eccessive.

Problema: Form multi-step, validazioni JavaScript complesse, campi obbligatori non documentati.

Impatto: Difficile accesso anche per cittadini comuni, non solo sistemi automatici.""",
            
            'no_open_format': """
Gli atti pubblicati non sono disponibili in **formato aperto**.

Problema: Documenti solo in PDF scansionato o formati proprietari, senza metadati strutturati.

Violazione: Art. 35 D.lgs 33/2013 richiede formato aperto e riutilizzabile.""",
            
            'iframe_external': """
Il vostro Albo Pretorio utilizza **iframe esterni** per la pubblicazione.

Problema: Contenuti caricati da dominio di terze parti, difficili da verificare e accedere.

Rischio: Dipendenza da fornitore esterno, possibili problemi di continuità."""
        }
        
        return descrizioni.get(tipo, "Problema tecnico non classificato nell'accesso ai dati.")
    
    def _soluzione_per_tipo(self, tipo: str) -> str:
        """Soluzione ideale per tipo di problema"""
        
        soluzioni = {
            'cloudflare_protection': """
**Soluzione 1 (Ideale)**: Disabilitare Cloudflare anti-bot per la sezione Albo Pretorio
- Configurazione Cloudflare: Whitelist path /albo-pretorio/* 
- Mantiene protezione DDoS ma permette accesso legittimo
- Implementazione: 30 minuti
- Costo: Zero

**Soluzione 2**: Creare endpoint API pubblico
- API REST in JSON con elenco atti: GET /api/albo/atti?anno=2025
- Espone solo dati già pubblici
- Implementazione: 2-3 giorni sviluppo
- Costo: ~€500-1.000 (una tantum)

**Soluzione 3**: Whitelist IP NATAN
- Semplice ma non risolve problema per altri servizi
- Solo soluzione temporanea""",
            
            'javascript_required': """
**Soluzione Ideale**: Server-Side Rendering + Progressive Enhancement
- Renderizzare HTML completo lato server
- JavaScript solo per funzionalità aggiuntive
- Garantisce accessibilità e SEO
- Implementazione: 3-5 giorni
- Costo: ~€1.000-2.000

**Soluzione Rapida**: Endpoint API parallelo
- Mantenere interfaccia web attuale
- Aggiungere API JSON per accesso programmatico
- Implementazione: 2 giorni
- Costo: ~€500-800""",
            
            'no_api': """
**Soluzione Ideale**: API RESTful Completa
- GET /api/albo/atti?anno=2025&tipo=delibera
- GET /api/albo/atti/{id}
- Paginazione, filtri, ordinamento
- Documentazione OpenAPI/Swagger
- Implementazione: 5-7 giorni
- Costo: ~€2.000-3.000

**Soluzione Minima Conforme**: Export CSV/JSON
- Pulsante "Esporta risultati ricerca" → CSV/JSON
- Già soddisfa obbligo formato aperto
- Implementazione: 1-2 giorni
- Costo: ~€300-500""",
            
            'captcha': """
**Soluzione Ideale**: Rimuovere CAPTCHA per dati pubblici
- Dati già pubblici → non servono protezioni
- Mantenere CAPTCHA solo per form di invio (se presenti)
- Implementazione: 1 ora
- Costo: Zero

**Soluzione Alternativa**: Rate Limiting Intelligente
- Max 100 richieste/minuto per IP
- Blocco temporaneo in caso abuso
- Nessun CAPTCHA per accesso normale
- Implementazione: 1 giorno
- Costo: ~€200-400""",
            
            'form_complex': """
**Soluzione Ideale**: Semplificare Form + Aggiungere API
- Form con solo campi essenziali (date, tipo atto)
- Validazioni client-side semplici
- API GET per bypass form
- Implementazione: 3 giorni
- Costo: ~€800-1.200

**Soluzione Rapida**: Documentare API esistente
- Se backend ha già API, documentarla pubblicamente
- Implementazione: 1 giorno
- Costo: ~€200-300""",
            
            'no_open_format': """
**Soluzione Ideale**: Metadati JSON + PDF accessibili
- File JSON con metadati atti (numero, data, oggetto, link PDF)
- PDF/A per accessibilità
- Export CSV risultati ricerca
- Implementazione: 4-6 giorni
- Costo: ~€1.500-2.500

**Soluzione Minima**: Feed RSS/Atom
- Feed XML con ultimi atti pubblicati
- Implementazione: 1-2 giorni
- Costo: ~€300-500"""
        }
        
        return soluzioni.get(tipo, "Contattare progetto NATAN per consulenza specifica.")
    
    def to_dict(self) -> dict:
        """Export risultato come dizionario"""
        return {
            'comune': self.comune,
            'url': self.url,
            'timestamp': self.timestamp,
            'problemi': self.problemi,
            'soluzioni': self.soluzioni,
            'suggerimento_email': self.suggerimento_email,
            'priorita': self.priorita,
            'fattibilita_fix': self.fattibilita_fix
        }


class DiagnosticScraper:
    """Scraper diagnostico con analisi problemi"""
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'NATAN-Diagnostic/1.0 (Progetto Trasparenza PA; +https://github.com/AutobookNft/NATAN_LOC)'
        })
    
    async def diagnose_comune(self, nome: str, url: str) -> DiagnosticResult:
        """
        Analizza comune e identifica problemi
        
        Returns:
            DiagnosticResult con problemi e soluzioni
        """
        result = DiagnosticResult(nome, url)
        
        print(f"\n{'='*60}")
        print(f"🔍 DIAGNOSI: {nome}")
        print(f"{'='*60}")
        
        # Test 1: Accesso HTTP base
        try:
            resp = self.session.get(url, timeout=10)
            print(f"✓ HTTP Status: {resp.status_code}")
            
            # Cloudflare?
            if 'cloudflare' in resp.text.lower() or 'cf-ray' in str(resp.headers):
                if 'checking your browser' in resp.text.lower():
                    result.add_problema(
                        ProblemaType.CLOUDFLARE_PROTECTION,
                        "Cloudflare anti-bot attivo - blocca accesso automatizzato"
                    )
                    result.add_soluzione(
                        "Disabilitare Cloudflare anti-bot per /albo-pretorio/*",
                        "€0 (solo configurazione)",
                        "30 minuti"
                    )
                    result.priorita = "alta"
                    result.fattibilita_fix = "molto alta"
                    print("❌ Cloudflare protection rilevato")
        
        except Exception as e:
            print(f"❌ Errore HTTP: {e}")
            result.add_problema(ProblemaType.UNKNOWN, f"Errore connessione: {e}")
        
        # Test 2: Playwright (JavaScript, form, CAPTCHA)
        p = await async_playwright().start()
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        try:
            await page.goto(url, wait_until='networkidle', timeout=15000)
            
            content = await page.content()
            
            # CAPTCHA?
            if any(k in content.lower() for k in ['captcha', 'recaptcha', 'hcaptcha']):
                result.add_problema(
                    ProblemaType.CAPTCHA,
                    "CAPTCHA rilevato su dati pubblici"
                )
                result.add_soluzione(
                    "Rimuovere CAPTCHA per accesso Albo Pretorio pubblico",
                    "€0 (rimozione configurazione)",
                    "1 ora"
                )
                result.priorita = "alta"
                print("❌ CAPTCHA rilevato")
            
            # JavaScript obbligatorio?
            noscript = await page.query_selector('noscript')
            if noscript:
                noscript_text = await noscript.text_content()
                if 'javascript' in noscript_text.lower():
                    result.add_problema(
                        ProblemaType.JAVASCRIPT_REQUIRED,
                        "JavaScript obbligatorio per visualizzare dati"
                    )
                    result.add_soluzione(
                        "Implementare server-side rendering per accessibilità",
                        "€1.000-2.000",
                        "3-5 giorni"
                    )
                    print("⚠️  JavaScript obbligatorio")
            
            # Form search disponibile?
            forms = await page.query_selector_all('form')
            if len(forms) > 0:
                print(f"✓ Form ricerca: {len(forms)} trovati")
                
                # Form complesso?
                inputs = await page.query_selector_all('input, select, textarea')
                if len(inputs) > 10:
                    result.add_problema(
                        ProblemaType.FORM_COMPLEX,
                        f"Form con {len(inputs)} campi - eccessiva complessità"
                    )
                    result.add_soluzione(
                        "Semplificare form ricerca (solo date + tipo atto)",
                        "€500-800",
                        "2-3 giorni"
                    )
                    print(f"⚠️  Form complesso: {len(inputs)} campi")
            
            # API detection
            scripts = await page.query_selector_all('script')
            api_found = False
            
            for script in scripts[:20]:  # Check primi 20 script
                script_content = await script.text_content() or ''
                if any(k in script_content for k in ['/api/', 'ajax', 'fetch(', 'axios']):
                    api_found = True
                    print("✓ Possibile API backend rilevata")
                    break
            
            if not api_found:
                result.add_problema(
                    ProblemaType.NO_API,
                    "Nessuna API pubblica per accesso dati strutturati"
                )
                result.add_soluzione(
                    "Creare API REST pubblica (GET /api/albo/atti)",
                    "€500-1.000",
                    "2-3 giorni"
                )
                result.priorita = "media"
                print("⚠️  Nessuna API rilevata")
            
            # iframe esterni?
            iframes = await page.query_selector_all('iframe')
            for iframe in iframes:
                src = await iframe.get_attribute('src') or ''
                if src and not any(k in src for k in [url, nome.lower()]):
                    result.add_problema(
                        ProblemaType.IFRAME_EXTERNAL,
                        f"Iframe esterno: {src[:100]}"
                    )
                    result.add_soluzione(
                        "Integrare contenuti direttamente nel sito comunale",
                        "€1.000-2.000",
                        "5-7 giorni"
                    )
                    print(f"⚠️  Iframe esterno: {src[:60]}")
        
        except Exception as e:
            print(f"❌ Errore Playwright: {e}")
            result.add_problema(ProblemaType.UNKNOWN, f"Errore analisi: {e}")
        
        finally:
            await browser.close()
            await p.stop()
        
        # Genera suggerimento email
        result.genera_suggerimento_email()
        
        # Summary
        print(f"\n📊 RISULTATI DIAGNOSI:")
        print(f"   Problemi trovati: {len(result.problemi)}")
        print(f"   Soluzioni proposte: {len(result.soluzioni)}")
        print(f"   Priorità fix: {result.priorita}")
        print(f"   Fattibilità: {result.fattibilita_fix}")
        
        return result
    
    async def diagnose_batch(self, comuni: List[dict]) -> Dict[str, DiagnosticResult]:
        """
        Diagnostica batch comuni
        
        Args:
            comuni: [{'nome': 'Pisa', 'url': '...'}, ...]
        
        Returns:
            {'Pisa': DiagnosticResult, ...}
        """
        risultati = {}
        
        for comune in comuni:
            nome = comune['nome']
            url = comune['url']
            
            result = await self.diagnose_comune(nome, url)
            risultati[nome] = result
            
            # Pausa tra comuni
            await asyncio.sleep(2)
        
        return risultati
    
    def export_results(self, risultati: Dict[str, DiagnosticResult], 
                      filename='diagnostic_results.json'):
        """Esporta risultati diagnosi"""
        
        data = {
            'timestamp': datetime.now().isoformat(),
            'totale_comuni': len(risultati),
            'comuni': {nome: result.to_dict() for nome, result in risultati.items()}
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        
        print(f"\n✅ Risultati esportati in {filename}")
    
    def export_emails(self, risultati: Dict[str, DiagnosticResult], 
                     output_dir='emails_suggerimenti'):
        """Esporta email suggerimenti come file separati"""
        
        import os
        os.makedirs(output_dir, exist_ok=True)
        
        for nome, result in risultati.items():
            if result.suggerimento_email:
                filename = f"{output_dir}/{nome.lower().replace(' ', '_')}_suggerimento.txt"
                
                with open(filename, 'w', encoding='utf-8') as f:
                    f.write(result.suggerimento_email)
        
        print(f"\n✅ Email suggerimenti esportate in {output_dir}/")


# ESEMPIO DI USO
if __name__ == '__main__':
    async def main():
        scraper = DiagnosticScraper()
        
        # Comuni da diagnosticare (quelli che non funzionano con trivella)
        comuni_problematici = [
            {'nome': 'Pisa', 'url': 'https://albopretorio.comune.pisa.it/'},
            {'nome': 'Lucca', 'url': 'https://lucca.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio'},
            {'nome': 'Livorno', 'url': 'https://www.comune.livorno.it/trasparenza-vm/albo-pretorio'},
            {'nome': 'Arezzo', 'url': 'https://servizionline.comune.arezzo.it/jattipubblicazioni/'},
            {'nome': 'Siena', 'url': 'https://www.comune.siena.it/albo-pretorio'},
        ]
        
        print("🏥 DIAGNOSTIC SCRAPER - Analisi Problemi e Soluzioni")
        print("="*60)
        print(f"Comuni da analizzare: {len(comuni_problematici)}")
        print()
        
        # Diagnosi
        risultati = await scraper.diagnose_batch(comuni_problematici)
        
        # Export JSON
        scraper.export_results(risultati)
        
        # Export email suggerimenti
        scraper.export_emails(risultati)
        
        # Statistiche finali
        print("\n" + "="*60)
        print("📊 STATISTICHE FINALI")
        print("="*60)
        
        stats = {
            'cloudflare': 0,
            'no_api': 0,
            'javascript': 0,
            'captcha': 0,
            'form_complex': 0
        }
        
        for result in risultati.values():
            for problema in result.problemi:
                tipo = problema['tipo']
                if 'cloudflare' in tipo:
                    stats['cloudflare'] += 1
                elif 'api' in tipo:
                    stats['no_api'] += 1
                elif 'javascript' in tipo:
                    stats['javascript'] += 1
                elif 'captcha' in tipo:
                    stats['captcha'] += 1
                elif 'form' in tipo:
                    stats['form_complex'] += 1
        
        print(f"Cloudflare protection:      {stats['cloudflare']}")
        print(f"Nessuna API pubblica:       {stats['no_api']}")
        print(f"JavaScript obbligatorio:    {stats['javascript']}")
        print(f"CAPTCHA su dati pubblici:   {stats['captcha']}")
        print(f"Form troppo complessi:      {stats['form_complex']}")
        
        print("\n✅ Analisi completata!")
        print("📧 Email suggerimenti pronte in emails_suggerimenti/")
        print("📊 Report completo in diagnostic_results.json")
    
    asyncio.run(main())
