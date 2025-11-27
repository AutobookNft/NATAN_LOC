import React, { useEffect, useRef } from 'react';
import './Section2Problems.css';

interface Problem {
  id: number;
  title: string;
  before: string;
  after: string;
}

const problems: Problem[] = [
  {
    id: 1,
    title: "I marketplace tradizionali ti spremono",
    before: "Fee 15-30%, zero controllo, algoritmi oscuri",
    after: "Fee 10% (dinamica fino a 5%), royalty automatiche 4.5% per sempre"
  },
  {
    id: 2,
    title: "Non hai prove di autenticità",
    before: "Certificati cartacei falsificabili, perizie costose",
    after: "Certificato blockchain immutabile, QR code verificabile da chiunque"
  },
  {
    id: 3,
    title: "I crypto wallet sono un incubo",
    before: "Seed phrase da ricordare, rischio perdita totale",
    after: "Wallet auto-generato invisibile, export quando vuoi, pagamento carta/bonifico"
  },
  {
    id: 4,
    title: "NFT = speculazione e truffe",
    before: "Progetti pump&dump, rug pull, zero utilità",
    after: "Asset REALI con valore intrinseco, 20% automatico a progetti ambientali verificati"
  },
  {
    id: 5,
    title: "Non sai chi ti ha fregato l'opera",
    before: "Provenance opaca, passaggi di mano non tracciati",
    after: "Storia completa on-chain, ogni passaggio certificato, trasparenza totale"
  },
  {
    id: 6,
    title: "Le royalty non le vedi mai",
    before: "Accordi verbali, pagamenti ritardati o mancanti",
    after: "Smart contract automatici, royalty 4.5% istantanee su ogni rivendita, zero intermediari"
  },
  {
    id: 7,
    title: "GDPR e privacy sono un labirinto",
    before: "Cookie banner illegali, dati venduti a terzi",
    after: "GDPR by design, consenso granulare, export/cancellazione dati in 1 click"
  },
  {
    id: 8,
    title: "MiCA e crypto regulations ti bloccano",
    before: "Serve licenza CASP, KYC invasivo, costi legali enormi",
    after: "MiCA-safe by design, nessuna custodia crypto, PSP autorizzati gestiscono tutto"
  },
  {
    id: 9,
    title: "I pagamenti internazionali sono lenti e costosi",
    before: "Bonifici T+7, fee bancarie 3-5%, conversioni valuta sfavorevoli",
    after: "4 metodi (FIAT/Crypto/Egili), settlement T+2, fee PSP 1-3%"
  },
  {
    id: 10,
    title: "Non sai quanto vale realmente la tua opera",
    before: "Prezzi a caso, zero dati di mercato",
    after: "NATAN AI analizza mercato, suggerisce pricing, identifica trend"
  },
  {
    id: 11,
    title: "Le piattaforme ti trattano da numero",
    before: "Algoritmi che premiano chi paga, zero supporto",
    after: "Mecenatismo certificato, ranking impatto, community attiva"
  },
  {
    id: 12,
    title: "La fatturazione fiscale è un casino",
    before: "Devi fare tutto manualmente, errori costano caro",
    after: "Fatturazione automatica SDI, export CSV/XML, report trimestrale, conformità EU/US/Global"
  }
];

export default function Section2Problems() {
  const sectionRef = useRef<HTMLElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      },
      { threshold: 0.1 }
    );

    const cards = sectionRef.current?.querySelectorAll('.problem-card');
    cards?.forEach((card) => observer.observe(card));

    return () => observer.disconnect();
  }, []);

  return (
    <section ref={sectionRef} className="section-problems">
      <div className="container">
        <header className="section-header">
          <h2 className="section-title">
            Cosa Risolviamo Davvero
            <br />
            <span className="subtitle">(E Perché Ti Serve)</span>
          </h2>
          <p className="section-description">
            12 problemi concreti che ogni creator, collezionista e mecenate 
            affronta ogni giorno. E le nostre soluzioni.
          </p>
        </header>

        <div className="problems-grid">
          {problems.map((problem, index) => (
            <div 
              key={problem.id} 
              className="problem-card"
              style={{ animationDelay: `${index * 0.1}s` }}
            >
              <div className="problem-number">
                {String(problem.id).padStart(2, '0')}
              </div>
              
              <h3 className="problem-title">{problem.title}</h3>
              
              <div className="problem-comparison">
                <div className="comparison-item before">
                  <div className="comparison-label">
                    <span className="icon">❌</span>
                    <span className="text">Prima</span>
                  </div>
                  <p className="comparison-text">{problem.before}</p>
                </div>

                <div className="comparison-arrow">→</div>

                <div className="comparison-item after">
                  <div className="comparison-label">
                    <span className="icon">✅</span>
                    <span className="text">Con FlorenceEGI</span>
                  </div>
                  <p className="comparison-text">{problem.after}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
