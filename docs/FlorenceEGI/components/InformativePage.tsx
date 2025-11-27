import React from 'react';
import HeroSection from './HeroSection';
import Section2Problems from './Section2Problems';
import './InformativePage.css';

export default function InformativePage() {
  return (
    <div className="informative-page">
      {/* Section 1: Hero con animazione 3D + testo */}
      <HeroSection />

      {/* Section 2: 12 Problemi risolti */}
      <Section2Problems />

      {/* Placeholder per sezioni future */}
      <section className="section-placeholder">
        <div className="container">
          <h2>🚧 Sezioni 3-17 in arrivo...</h2>
          <p>
            Le prossime sezioni includeranno: Cosa Puoi EGIZZARE, Come Funziona,
            Blockchain Spiegata Semplice, Tokenomics, e molto altro.
          </p>
        </div>
      </section>
    </div>
  );
}
