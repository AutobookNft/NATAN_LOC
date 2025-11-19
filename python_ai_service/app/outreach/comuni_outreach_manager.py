#!/usr/bin/env python3
"""
Sistema di Outreach Automatico ai Comuni
==========================================
Gestisce l'invio programmato di email ai comuni e traccia le risposte.

Features:
- Template email personalizzati
- Invio programmato (rate limiting)
- Tracking risposte
- Dashboard stato comuni
- Escalation automatica (email1 -> email2 -> ANAC)
"""

import json
import time
from datetime import datetime, timedelta
from typing import Dict, List
from enum import Enum


class StatoComune(Enum):
    """Stati possibili di un comune"""
    NON_CONTATTATO = "non_contattato"
    EMAIL1_INVIATA = "email1_inviata"
    EMAIL2_INVIATA = "email2_inviata"  # Follow-up dopo 15gg
    RISPOSTA_RICEVUTA = "risposta_ricevuta"
    COLLABORAZIONE_ATTIVA = "collaborazione_attiva"
    ACCESSO_CIVICO_FORMALE = "accesso_civico_formale"  # Dopo 30gg senza risposta
    SEGNALAZIONE_ANAC = "segnalazione_anac"  # Dopo 60gg
    FUNZIONANTE_TECNICO = "funzionante_tecnico"  # Scraping funziona
    RIFIUTO = "rifiuto"


class ComuniOutreachManager:
    """Gestisce l'outreach verso i comuni"""
    
    def __init__(self, db_file='comuni_outreach_status.json'):
        self.db_file = db_file
        self.comuni_status = self.load_status()
    
    def load_status(self) -> Dict:
        """Carica stato comuni da JSON"""
        try:
            with open(self.db_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except FileNotFoundError:
            return {}
    
    def save_status(self):
        """Salva stato comuni"""
        with open(self.db_file, 'w', encoding='utf-8') as f:
            json.dump(self.comuni_status, f, indent=2, ensure_ascii=False)
    
    def inizializza_comune(self, nome: str, url: str, abitanti: int, 
                          email_pec: str = None, funzionante_tecnico: bool = False):
        """Inizializza stato di un comune"""
        if nome not in self.comuni_status:
            self.comuni_status[nome] = {
                'nome': nome,
                'url': url,
                'abitanti': abitanti,
                'email_pec': email_pec,
                'stato': StatoComune.FUNZIONANTE_TECNICO.value if funzionante_tecnico 
                        else StatoComune.NON_CONTATTATO.value,
                'data_primo_contatto': None,
                'data_ultima_azione': None,
                'note': [],
                'funzionante_tecnico': funzionante_tecnico,
                'atti_recuperabili': 0 if not funzionante_tecnico else None
            }
            self.save_status()
    
    def genera_email_1(self, nome: str) -> Dict:
        """Genera prima email di presentazione"""
        comune = self.comuni_status.get(nome)
        if not comune:
            return None
        
        # Determina piattaforma (da analisi precedente)
        piattaforma = "il vostro sistema di pubblicazione"  # Da personalizzare
        
        email = {
            'to': comune.get('email_pec', f'protocollo@pec.comune.{nome.lower().replace(" ", "")}.it'),
            'subject': f'Progetto NATAN - Sistema AI per migliorare l\'accesso agli atti amministrativi',
            'body': f"""Spett.le Comune di {nome},
Responsabile della Trasparenza,

Mi presento: sono Fabio Massacci, sviluppatore del progetto NATAN (Notifiche e Accesso Trasparente Atti Normativi), un sistema basato su intelligenza artificiale che mira a facilitare l'accesso dei cittadini agli atti della Pubblica Amministrazione.

## Il Nostro Progetto

NATAN è un assistente AI che aiuta i cittadini a:
- Trovare rapidamente delibere, determine e altri atti amministrativi
- Essere notificati su temi di loro interesse (lavori pubblici, urbanistica, etc)
- Comprendere il linguaggio tecnico-amministrativo grazie all'AI
- Monitorare l'attività amministrativa in modo semplice e trasparente

## Benefici per il Comune di {nome}

Il vostro Comune ({abitanti:,} abitanti) trarrebbe vantaggio da questo progetto:
✅ Riduzione carico URP: I cittadini trovano informazioni autonomamente
✅ Conformità normativa: Piena aderenza a D.lgs 33/2013 e CAD
✅ Trasparenza proattiva: Sistema che valorizza il vostro lavoro
✅ Innovazione digitale: Il Comune come esempio di PA 2.0
✅ Servizio gratuito: Nessun costo per l'amministrazione

## Richiesta di Collaborazione

Per integrare il vostro Comune nel sistema NATAN, avremmo bisogno di:

Accesso tecnico agli atti in formato aperto (Art. 35 D.lgs 33/2013):
- Formato: JSON, CSV o XML
- Contenuto: Atti albo pretorio 2024-2025
- Campi: Tipo, numero, data, oggetto, allegati

OPPURE

Documentazione API del vostro sistema di pubblicazione.

## Perché Vi Stiamo Contattando

Il vostro portale attualmente utilizza {piattaforma}, che rende difficile l'accesso automatizzato. Questa è un'opportunità di collaborazione per offrire ai cittadini un servizio migliore.

## Trasparenza del Progetto

NATAN è:
🔓 Open source: Codice pubblico su GitHub
🤝 Non profit: Obiettivo sociale, non commerciale
🇮🇹 Italiano: Sviluppato per i cittadini italiani
⚖️ Conforme GDPR: Nessun dato personale

## Prossimi Passi

Sarei lieto di:
1. Presentarvi il progetto in videoconferenza (30 min)
2. Collaborare con il vostro ufficio tecnico
3. Fornirvi demo del sistema

Resto a disposizione per ogni chiarimento.

Cordiali saluti,

Fabio Massacci
Progetto NATAN
Email: fabio@natan.it
GitHub: https://github.com/AutobookNft/NATAN_LOC
"""
        }
        
        return email
    
    def genera_email_2_followup(self, nome: str) -> Dict:
        """Genera email di follow-up dopo 15 giorni"""
        comune = self.comuni_status.get(nome)
        
        email = {
            'to': comune.get('email_pec'),
            'subject': f'Re: Progetto NATAN - Richiesta accesso dati formato aperto',
            'body': f"""Spett.le Comune di {nome},

Vi scrivo nuovamente in merito al progetto NATAN.

## Accesso Civico Semplice (Art. 5, c. 1 D.lgs 33/2013)

In alternativa alla collaborazione tecnica, formalizzo una richiesta di accesso civico semplice agli atti dell'albo pretorio in formato aperto (Art. 35 D.lgs 33/2013).

Richiesta specifica:
- Delibere e Determine 2024-2025
- Formato: JSON, CSV o XML
- Campi: Tipo, numero, data, oggetto, URL allegati

## Finalità Sociale

L'obiettivo è creare un servizio pubblico gratuito che:
- Aiuta i cittadini a esercitare il diritto di informazione
- Riduce il carico URP
- Valorizza il lavoro dell'amministrazione
- Promuove la cultura della trasparenza

Ai sensi dell'Art. 5 D.lgs 33/2013, chiedo riscontro entro 30 giorni.

Cordiali saluti,

Fabio Massacci
Progetto NATAN
"""
        }
        
        return email
    
    def segna_email_inviata(self, nome: str, tipo: int = 1):
        """Segna email come inviata"""
        if nome in self.comuni_status:
            oggi = datetime.now().isoformat()
            
            if tipo == 1:
                self.comuni_status[nome]['stato'] = StatoComune.EMAIL1_INVIATA.value
                self.comuni_status[nome]['data_primo_contatto'] = oggi
            elif tipo == 2:
                self.comuni_status[nome]['stato'] = StatoComune.EMAIL2_INVIATA.value
            
            self.comuni_status[nome]['data_ultima_azione'] = oggi
            self.comuni_status[nome]['note'].append({
                'data': oggi,
                'azione': f'Email {tipo} inviata'
            })
            
            self.save_status()
    
    def segna_risposta_ricevuta(self, nome: str, tipo_risposta: str, note: str = ""):
        """Segna risposta ricevuta da comune"""
        if nome in self.comuni_status:
            oggi = datetime.now().isoformat()
            
            if tipo_risposta == "positiva":
                self.comuni_status[nome]['stato'] = StatoComune.COLLABORAZIONE_ATTIVA.value
            elif tipo_risposta == "rifiuto":
                self.comuni_status[nome]['stato'] = StatoComune.RIFIUTO.value
            else:
                self.comuni_status[nome]['stato'] = StatoComune.RISPOSTA_RICEVUTA.value
            
            self.comuni_status[nome]['data_ultima_azione'] = oggi
            self.comuni_status[nome]['note'].append({
                'data': oggi,
                'azione': f'Risposta ricevuta: {tipo_risposta}',
                'dettagli': note
            })
            
            self.save_status()
    
    def identifica_comuni_da_contattare(self) -> List[str]:
        """Identifica comuni che devono essere contattati"""
        da_contattare = []
        
        for nome, info in self.comuni_status.items():
            # Salta comuni già funzionanti tecnicamente
            if info['funzionante_tecnico']:
                continue
            
            # Salta comuni in collaborazione
            if info['stato'] == StatoComune.COLLABORAZIONE_ATTIVA.value:
                continue
            
            # Non contattati
            if info['stato'] == StatoComune.NON_CONTATTATO.value:
                da_contattare.append(nome)
            
            # Follow-up dopo 15 giorni
            elif info['stato'] == StatoComune.EMAIL1_INVIATA.value:
                data_invio = datetime.fromisoformat(info['data_ultima_azione'])
                if datetime.now() - data_invio > timedelta(days=15):
                    da_contattare.append(nome)
        
        return da_contattare
    
    def genera_dashboard(self) -> str:
        """Genera dashboard testuale stato comuni"""
        totale = len(self.comuni_status)
        
        stats = {
            'funzionanti_tecnico': 0,
            'non_contattati': 0,
            'email1_inviata': 0,
            'email2_inviata': 0,
            'risposta_ricevuta': 0,
            'collaborazione_attiva': 0,
            'rifiuto': 0,
            'abitanti_funzionanti': 0,
            'abitanti_totali': 0
        }
        
        for info in self.comuni_status.values():
            stato = info['stato']
            abitanti = info['abitanti']
            
            stats['abitanti_totali'] += abitanti
            
            if info['funzionante_tecnico']:
                stats['funzionanti_tecnico'] += 1
                stats['abitanti_funzionanti'] += abitanti
            
            if stato == StatoComune.NON_CONTATTATO.value:
                stats['non_contattati'] += 1
            elif stato == StatoComune.EMAIL1_INVIATA.value:
                stats['email1_inviata'] += 1
            elif stato == StatoComune.EMAIL2_INVIATA.value:
                stats['email2_inviata'] += 1
            elif stato == StatoComune.RISPOSTA_RICEVUTA.value:
                stats['risposta_ricevuta'] += 1
            elif stato == StatoComune.COLLABORAZIONE_ATTIVA.value:
                stats['collaborazione_attiva'] += 1
                stats['abitanti_funzionanti'] += abitanti
            elif stato == StatoComune.RIFIUTO.value:
                stats['rifiuto'] += 1
        
        coverage = (stats['abitanti_funzionanti'] / stats['abitanti_totali'] * 100) if stats['abitanti_totali'] > 0 else 0
        
        dashboard = f"""
╔══════════════════════════════════════════════════════════════╗
║         DASHBOARD OUTREACH COMUNI - Progetto NATAN          ║
╚══════════════════════════════════════════════════════════════╝

📊 STATO GENERALE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Totale comuni:              {totale}
Abitanti totali:            {stats['abitanti_totali']:,}

✅ FUNZIONANTI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Tecnico (scraping):         {stats['funzionanti_tecnico']} comuni
Collaborazione attiva:      {stats['collaborazione_attiva']} comuni
Coverage popolazione:       {coverage:.1f}%
Abitanti coperti:           {stats['abitanti_funzionanti']:,}

📧 OUTREACH IN CORSO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Non contattati:             {stats['non_contattati']} comuni
Email 1 inviata:            {stats['email1_inviata']} comuni
Email 2 inviata (follow):   {stats['email2_inviata']} comuni
Risposta ricevuta:          {stats['risposta_ricevuta']} comuni

❌ PROBLEMATICI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Rifiuto collaborazione:     {stats['rifiuto']} comuni

🎯 OBIETTIVO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Target:                     100% comuni
Target popolazione:         100% abitanti
Mancante:                   {100 - coverage:.1f}%

"""
        return dashboard
    
    def esporta_report_csv(self, filename='comuni_status_report.csv'):
        """Esporta report CSV per analisi"""
        import csv
        
        with open(filename, 'w', newline='', encoding='utf-8') as f:
            writer = csv.writer(f)
            writer.writerow(['Comune', 'Abitanti', 'Stato', 'Funzionante Tecnico', 
                           'Data Primo Contatto', 'Ultima Azione', 'Note'])
            
            for info in self.comuni_status.values():
                writer.writerow([
                    info['nome'],
                    info['abitanti'],
                    info['stato'],
                    'Sì' if info['funzionante_tecnico'] else 'No',
                    info.get('data_primo_contatto', ''),
                    info.get('data_ultima_azione', ''),
                    len(info.get('note', []))
                ])


# ESEMPIO DI USO
if __name__ == '__main__':
    manager = ComuniOutreachManager()
    
    # Inizializza comuni da dati trivella
    print("📥 Carico risultati trivella...")
    
    try:
        with open('trivella_brutale_results.json', 'r') as f:
            risultati = json.load(f)
            
            for comune_data in risultati['comuni']:
                manager.inizializza_comune(
                    nome=comune_data['comune'],
                    url=comune_data.get('url', ''),
                    abitanti=comune_data['abitanti'],
                    funzionante_tecnico=comune_data['funzionante']
                )
    except FileNotFoundError:
        print("⚠️ File risultati trivella non trovato")
    
    # Dashboard
    print(manager.genera_dashboard())
    
    # Comuni da contattare
    da_contattare = manager.identifica_comuni_da_contattare()
    print(f"\n📨 Comuni da contattare: {len(da_contattare)}")
    
    if da_contattare:
        print("\nPrimi 5 comuni da contattare:")
        for comune in da_contattare[:5]:
            print(f"  - {comune}")
            
            # Genera email (non inviata)
            email = manager.genera_email_1(comune)
            if email:
                print(f"    Email pronta per: {email['to']}")
    
    # Esporta report
    manager.esporta_report_csv()
    print("\n✅ Report esportato in comuni_status_report.csv")
