#!/usr/bin/env python3
"""
Sistema di Invio Email ai Comuni
=================================
Invia email programmate con rate limiting.

CONFIGURAZIONE RICHIESTA:
1. Configurare SMTP server
2. Impostare credenziali email
3. Rate limiting (max 10 email/ora per evitare spam)
"""

import smtplib
import time
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import datetime
from comuni_outreach_manager import ComuniOutreachManager


class EmailSender:
    """Gestisce invio email con rate limiting"""
    
    def __init__(self, smtp_server='smtp.gmail.com', smtp_port=587):
        self.smtp_server = smtp_server
        self.smtp_port = smtp_port
        self.from_email = None
        self.password = None
        
        # Rate limiting
        self.max_emails_per_hour = 10
        self.emails_sent_last_hour = []
    
    def configure(self, email: str, password: str):
        """Configura credenziali"""
        self.from_email = email
        self.password = password
    
    def can_send(self) -> bool:
        """Verifica se si può inviare email (rate limiting)"""
        now = datetime.now()
        
        # Rimuovi email inviate più di 1 ora fa
        self.emails_sent_last_hour = [
            ts for ts in self.emails_sent_last_hour 
            if (now - ts).seconds < 3600
        ]
        
        return len(self.emails_sent_last_hour) < self.max_emails_per_hour
    
    def send_email(self, to: str, subject: str, body: str, dry_run=True) -> bool:
        """
        Invia email
        
        Args:
            to: Destinatario
            subject: Oggetto
            body: Corpo email
            dry_run: Se True, simula invio senza inviare realmente
        
        Returns:
            True se inviata con successo
        """
        if not self.can_send():
            print(f"⚠️ Rate limit raggiunto. Attendi prima di inviare altre email.")
            return False
        
        msg = MIMEMultipart()
        msg['From'] = self.from_email
        msg['To'] = to
        msg['Subject'] = subject
        
        msg.attach(MIMEText(body, 'plain'))
        
        if dry_run:
            print(f"\n{'='*60}")
            print(f"🧪 DRY RUN - Email NON inviata")
            print(f"{'='*60}")
            print(f"To: {to}")
            print(f"Subject: {subject}")
            print(f"\n{body[:500]}...")
            print(f"{'='*60}\n")
            return True
        
        try:
            # Connessione SMTP
            server = smtplib.SMTP(self.smtp_server, self.smtp_port)
            server.starttls()
            server.login(self.from_email, self.password)
            
            # Invio
            text = msg.as_string()
            server.sendmail(self.from_email, to, text)
            server.quit()
            
            # Registra timestamp
            self.emails_sent_last_hour.append(datetime.now())
            
            print(f"✅ Email inviata a {to}")
            return True
            
        except Exception as e:
            print(f"❌ Errore invio email a {to}: {e}")
            return False


def campagna_outreach_comuni(dry_run=True, max_emails=5):
    """
    Esegue campagna di outreach ai comuni
    
    Args:
        dry_run: Se True, simula invio senza inviare
        max_emails: Massimo numero di email da inviare in questa sessione
    """
    print("🚀 Avvio Campagna Outreach Comuni NATAN\n")
    
    # Inizializza manager
    manager = ComuniOutreachManager()
    
    # Dashboard iniziale
    print(manager.genera_dashboard())
    
    # Identifica comuni da contattare
    da_contattare = manager.identifica_comuni_da_contattare()
    
    if not da_contattare:
        print("✅ Nessun comune da contattare al momento.")
        return
    
    print(f"\n📨 {len(da_contattare)} comuni da contattare")
    print(f"📤 Invierò massimo {max_emails} email in questa sessione\n")
    
    # Configura email sender
    sender = EmailSender()
    
    if not dry_run:
        email = input("Email mittente: ")
        password = input("Password: ")
        sender.configure(email, password)
    
    # Invia email
    inviati = 0
    
    for comune in da_contattare[:max_emails]:
        info = manager.comuni_status[comune]
        stato = info['stato']
        
        # Determina tipo email
        if stato == 'non_contattato':
            email_data = manager.genera_email_1(comune)
            tipo = 1
        elif stato == 'email1_inviata':
            email_data = manager.genera_email_2_followup(comune)
            tipo = 2
        else:
            continue
        
        # Invia
        print(f"\n📧 Invio email {tipo} a {comune}...")
        
        success = sender.send_email(
            to=email_data['to'],
            subject=email_data['subject'],
            body=email_data['body'],
            dry_run=dry_run
        )
        
        if success:
            manager.segna_email_inviata(comune, tipo)
            inviati += 1
            
            # Rate limiting
            if not dry_run:
                time.sleep(10)  # 10 secondi tra email
    
    print(f"\n✅ Campagna completata: {inviati} email inviate")
    
    # Dashboard finale
    print("\n" + manager.genera_dashboard())
    
    # Esporta report
    manager.esporta_report_csv()
    print("📊 Report aggiornato in comuni_status_report.csv")


if __name__ == '__main__':
    import sys
    
    # Modalità
    dry_run = '--send' not in sys.argv
    max_emails = 5
    
    if '--max' in sys.argv:
        idx = sys.argv.index('--max')
        max_emails = int(sys.argv[idx + 1])
    
    if dry_run:
        print("🧪 MODALITÀ DRY RUN - Nessuna email verrà inviata")
        print("   Per inviare realmente: python3 send_emails_comuni.py --send\n")
    else:
        print("⚠️  MODALITÀ INVIO REALE")
        conferma = input("Sei sicuro di voler inviare email? (sì/no): ")
        if conferma.lower() != 'sì':
            print("❌ Operazione annullata")
            sys.exit(0)
    
    campagna_outreach_comuni(dry_run=dry_run, max_emails=max_emails)
