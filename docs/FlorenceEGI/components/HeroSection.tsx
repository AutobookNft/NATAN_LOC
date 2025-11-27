import React from 'react';
import HeroAnimation3D from './HeroAnimation3D';
import './HeroSection.css';

export default function HeroSection() {
  return (
    <section className="hero-section">
      {/* Background: Animazione 3D esistente */}
      <div className="hero-animation-background">
        <HeroAnimation3D />
      </div>

      {/* Overlay: Testo con glassmorphism */}
      <div className="hero-content-overlay">
        <div className="hero-text-container">
          <h1 className="hero-headline">
            Se esiste, <span className="highlight-egizza">EGIZZALO</span>.
            <br />
            Se lo EGIZZI, vale.
          </h1>

          <p className="hero-subheadline">
            FlorenceEGI è <strong>l'infrastruttura SaaS multi-tenant</strong> che 
            permette a chiunque di creare il proprio <strong>Asset Market Maker</strong>.
          </p>

          <p className="hero-description">
            Più simile a <strong>Shopify</strong> che a OpenSea. 
            Trasforma qualsiasi cosa in un asset digitale certificato chiamato{' '}
            <strong>EGI (Eco Goods Invent)</strong>.
          </p>

          <div className="hero-cta-buttons">
            <button className="btn-primary">
              Inizia a EGIZZARE →
            </button>
            <button className="btn-secondary">
              Scopri Come Funziona
            </button>
          </div>

          <div className="hero-features">
            <div className="feature-badge">
              <span className="icon">✅</span>
              <span className="text">Arte & Design</span>
            </div>
            <div className="feature-badge">
              <span className="icon">✅</span>
              <span className="text">Esperienze</span>
            </div>
            <div className="feature-badge">
              <span className="icon">✅</span>
              <span className="text">Documenti PA</span>
            </div>
            <div className="feature-badge">
              <span className="icon">✅</span>
              <span className="text">Progetti Green</span>
            </div>
          </div>
        </div>
      </div>

      {/* Scroll Indicator */}
      <div className="scroll-indicator">
        <div className="scroll-icon"></div>
        <span className="scroll-text">Scorri per scoprire</span>
      </div>
    </section>
  );
}
