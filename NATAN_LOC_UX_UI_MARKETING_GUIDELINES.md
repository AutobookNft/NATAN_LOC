# NATAN_LOC - Linee Guida UX/UI & Marketing

## Design System & Brand Guidelines per Cognitive Trust Layer

**Versione:** 1.0.0  
**Data:** 31 Ottobre 2025  
**Autore:** Padmin D. Curtis (OS3.0) for Fabio Cherici  
**Status:** ✅ APPROVATO PER IMPLEMENTAZIONE  

**Derivato da:**
- `/home/fabio/EGI/docs/ai/marketing/PA_ENTERPRISE_BRAND_GUIDELINES.md`
- `/home/fabio/EGI/docs/ai/marketing/marketing_strategy_unified.md`
- `/home/fabio/EGI/docs/NATAN_LOC/natan_os_3_docs_batch_1_multimodel_gateway_white_paper_local_mode_marketing.md`

---

## 📍 **PREMESSA: Identità NATAN_LOC**

NATAN_LOC è il **Cognitive Trust Layer** di FlorenceEGI, progettato per **PA & Enterprises**. Il design deve trasmettere:

1. **Inviolabilità dei dati** - Zero-leak, isolamento totale, sicurezza perimetrale
2. **Certezza delle informazioni** - Fonti verificate, URS certificato, blockchain notarizzato
3. **Prestigio istituzionale** - Eleganza rinascimentale + rigore enterprise

**Filosofia design:**

> "Affidabile come un notaio, sicura come un caveau, intelligente come un esperto."

**Claim ufficiale:**

> "Non immagina. **Dimostra**."

---

## 🎯 **1. PRINCIPI FONDAMENTALI**

### 1.1 Mobile-First
- Design base = mobile (< 768px)
- Progressive enhancement per tablet/desktop
- Touch targets minimo 44px
- Layout pulito: solo essenziale su mobile

### 1.2 Chat = Dashboard Centrale
- La chat è sempre visibile, è il **perno** attorno cui gira tutto
- Feature supporto = modals/panels/drawer che aprono sopra/accanto
- Niente sidebar pesante su mobile

### 1.3 Trust by Design
- Ogni elemento visivo deve comunicare sicurezza e affidabilità
- Colori semantici per stati (URS, sicurezza, certificazione)
- Iconografia chiara e riconoscibile
- Nessun elemento "playful" o decorativo non funzionale

### 1.4 Silent Growth Aesthetic
- Design sobrio, istituzionale, anti-hype
- Qualità > quantità visiva
- Prestigio attraverso eleganza, non colori sgargianti
- Conformità WCAG 2.1 AA obbligatoria

---

## 🎨 **2. PALETTE COLORI NATAN_LOC**

### 2.1 Palette Primaria (Istituzionale)

**Blu Trust** `#1B365D` (Blu Istituzionale PA)
- **Ruolo:** Colore DOMINANTE (60% superficie)
- **Uso:** Header, sidebar, CTA primarie, stati attivi, elementi principali
- **Rationale:** Trust, affidabilità, serietà istituzionale
- **Varianti:**
  - Light: `#2C4A7C` (hover, backgrounds chiari)
  - Dark: `#0F1E36` (testi su backgrounds chiari)
  - Extra Light: `#E8EDF4` (backgrounds sezioni)

**Grigio Strutturale** `#4A5568`
- **Ruolo:** Testi, bordi, elementi secondari (30% superficie)
- **Varianti:**
  - `#2D3748` - Headings secondari
  - `#718096` - Testi secondari
  - `#CBD5E0` - Bordi, separatori
  - `#EDF2F7` - Backgrounds chiari
  - `#F7FAFC` - Backgrounds sezioni alternate

### 2.2 Colori Certificazione & Trust (NATAN-Specific)

**Verde Certificazione** `#2D5016`
- **Ruolo:** Certificazione, validazione, EPP, sostenibilità
- **Uso:** Badge "Verificato", status "Valido", certificazioni blockchain
- **Varianti:**
  - Light: `#3D6B22` (hover)
  - Extra Light: `#E8F4E3` (backgrounds success)

**Oro Sobrio** `#B89968` (accenti MISURATI - 10%)
- **Ruolo:** Accenti puntuali, badge "Certificato", highlights selezionati
- **Regola:** MAI in grandi superfici, SOLO accenti
- **Uso:** Badge premium, status "Approved", elementi premium

**Blu Sicurezza** `#0F4C75` (Nuovo - NATAN-specific)
- **Ruolo:** Indicatori sicurezza, inviolabilità, isolamento dati
- **Uso:** Badge "Zero-leak", indicatori multi-tenant isolato, lock icons

### 2.3 Colori URS (Ultra Reliability Score)

**URS A (Alta Affidabilità)** `#10B981` (Emerald-500)
- Score: 0.85 - 1.0
- **Significato:** Informazione altamente affidabile, fonti multiple verificate

**URS B (Media-Alta)** `#3B82F6` (Blue-500)
- Score: 0.70 - 0.84
- **Significato:** Informazione affidabile, fonti verificate

**URS C (Media)** `#F59E0B` (Amber-500)
- Score: 0.50 - 0.69
- **Significato:** Informazione moderatamente affidabile, fonti limitate

**URS X (Bassa/Bloccato)** `#EF4444` (Red-500)
- Score: 0.0 - 0.49
- **Significato:** Informazione non affidabile, claim bloccato

### 2.4 Colori Funzionali

**Rosso Urgenza** `#C13120`
- Uso: Errori bloccanti, claim revocati, alert critici

**Arancio Attenzione** `#E67E22`
- Uso: Warning, stati in revisione, notifiche non critiche

**Blu Info** `#3B82F6`
- Uso: Info boxes, helper text, link ipertestuali

**Grigio Disabilitato** `#A0AEC0`
- Uso: Elementi disabilitati, stati inattivi

### 2.5 Regole Applicazione Palette

**PRINCIPIO GUIDA: 60-30-10 Istituzionale + Trust**

- 60% Blu Trust + Grigi Strutturali (dominanti)
- 30% Bianchi/Backgrounds chiari (respiro)
- 10% Verde Certificazione + Oro Sobrio + URS colors (accenti)

**VIETATO:**
- ❌ Gradienti colorati - solo grigi o mono-tonali blu
- ❌ Colori vivaci/playful (rosa, viola, arcobaleno)
- ❌ Oro Fiorentino originale (`#D4A574`) in grandi superfici
- ❌ Colori che distraggono dalla chat

**OBBLIGATORIO:**
- ✅ Contrasti WCAG 2.1 AA minimi (4.5:1 per testi)
- ✅ Backgrounds prevalentemente chiari (#F7FAFC, #FFFFFF)
- ✅ Blu Trust per tutti gli header/sidebar
- ✅ URS colors solo per badge/indicatori specifici

---

## 🔤 **3. TIPOGRAFIA ISTITUZIONALE**

### 3.1 Font Families

**Primaria - Headings:** **IBM Plex Sans** (o Inter come fallback)
- Carattere: Sans-serif professionale, leggibilità massima
- Uso: H1, H2, H3, titoli dashboard, menu items, header chat

**Secondaria - Body:** **Source Sans Pro**
- Carattere: Leggibile, accessibile, professionale
- Uso: Paragrafi, form labels, descrizioni, messaggi chat

**Tecnica - Dati:** **JetBrains Mono**
- Uso: Hash blockchain, seriali documenti, codici, timestamp, URS scores

### 3.2 Scale Tipografica

```css
/* Headings - IBM Plex Sans */
H1: 32px / 2rem - font-weight: 700 - line-height: 1.2 - color: #0F1E36
H2: 24px / 1.5rem - font-weight: 600 - line-height: 1.3 - color: #2D3748
H3: 20px / 1.25rem - font-weight: 600 - line-height: 1.4 - color: #4A5568
H4: 18px / 1.125rem - font-weight: 500 - line-height: 1.5 - color: #4A5568

/* Body - Source Sans Pro */
Body Large: 16px / 1rem - font-weight: 400 - line-height: 1.6 - color: #4A5568
Body Regular: 14px / 0.875rem - font-weight: 400 - line-height: 1.6 - color: #4A5568
Body Small: 12px / 0.75rem - font-weight: 400 - line-height: 1.5 - color: #718096

/* Technical - JetBrains Mono */
Code: 14px / 0.875rem - font-weight: 400 - line-height: 1.4 - color: #2D3748
```

### 3.3 Regole Tipografiche

**OBBLIGATORIO:**
- ✅ Contrasto minimo 4.5:1
- ✅ Line-height minimo 1.5 per body text
- ✅ Nessun italic per headings
- ✅ Bold solo per enfasi

**VIETATO:**
- ❌ All-caps su più di 3 parole
- ❌ Underline tranne link
- ❌ Font size sotto 12px

---

## 🏗️ **4. LAYOUT & ARCHITETTURA UI**

### 4.1 Layout Mobile (< 768px)

```
┌─────────────────────────────────────┐
│ [☰] NATAN        [⚙️] [💬] [🧠]  │ ← Header minimale (72px)
├─────────────────────────────────────┤
│                                     │
│         CHAT CENTRALE               │
│      (Full width, flexible height)  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Messaggi chat               │   │
│  │ Claims con URS              │   │
│  │ Fonti verificate            │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ [💡 Suggerimenti]     [▼]   │   │ ← Collassabile (default: chiuso)
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ [👥 Consulenti]       [▼]   │   │ ← Collassabile (default: chiuso)
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ [Textarea input]      [➤]   │   │ ← Fixed bottom
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

**Caratteristiche:**
- Header minimale: hamburger menu, titolo NATAN, icone essenziali
- Chat full-width, full-height (flexible)
- Suggerimenti: barra collassabile sopra input (default: chiuso)
- Consulenti: bottom sheet/drawer collassabile (default: chiuso)
- Sidebar feature: drawer laterale slide da sinistra (si apre da hamburger)

### 4.2 Layout Desktop (≥ 1024px)

```
┌──────────┬──────────────────────────────────┬────────────┐
│          │                                  │            │
│ SIDEBAR  │        CHAT CENTRALE             │  PANEL     │
│ (280px)  │        (Flexible width)          │  RIGHT     │
│          │                                  │  (320px)   │
│ ──────── │ ─────────────────────────────── │ ─────────  │
│          │                                  │            │
│ CRONOLOGIA│  ┌──────────────────────────┐   │  Domande   │
│ CHAT     │  │ Messaggi chat            │   │  Strategiche│
│ ──────── │  │ Claims URS               │   │  ─────────  │
│          │  │ Fonti                    │   │            │
│ DOCUMENTI│  └──────────────────────────┘   │  Spiegazioni│
│ ──────── │                                  │            │
│ PROGETTI │  [Input chat]              [➤]  │  Consulenti │
│ ──────── │                                  │            │
│ SCRAPERS │                                  │            │
│ ──────── │                                  │            │
│ EMBEDDING│                                  │            │
│ ──────── │                                  │            │
│ COSTI AI │                                  │            │
│ ──────── │                                  │            │
│ STATS    │                                  │            │
│ ──────── │                                  │            │
│ BATCH    │                                  │            │
│          │                                  │            │
└──────────┴──────────────────────────────────┴────────────┘
```

**Caratteristiche:**
- Sidebar sinistra: cronologia chat (top) + feature menu (bottom) con separatori
- Chat centrale: flexible width (fino a 1440px max)
- Panel destro: domande strategiche + spiegazioni + consulenti (collassabile, default: aperto)

### 4.3 Grid System

- 12 colonne con gutter 24px (desktop)
- Container max-width: 1440px
- Breakpoints:
  - Mobile: 320px - 767px
  - Tablet: 768px - 1023px
  - Desktop: 1024px - 1439px
  - Wide: 1440px+

**Spaziature (multipli di 8px):**
```css
--space-xs: 8px;
--space-sm: 16px;
--space-md: 24px;
--space-lg: 32px;
--space-xl: 48px;
--space-2xl: 64px;
```

---

## 🎯 **5. ELEMENTI VISIVI TRUST & CERTEZZA**

### 5.1 Badge "Zero-Leak" (Inviolabilità Dati)

**Design:**
```css
Background: linear-gradient(135deg, #0F4C75 0%, #1B365D 100%)
Color: #FFFFFF
Border: 2px solid #2D5016 (Verde Certificazione)
Border-radius: 8px
Padding: 8px 16px
Font-weight: 600
Font-size: 12px
Icon: 🔒 lock (20px)
```

**Posizionamento:**
- Header chat (sempre visibile)
- Footer sidebar (desktop)
- Settings modal

**Varianti:**
- "Zero-Leak Perimetrale" (testo completo)
- "ZL" (abbreviazione su mobile)
- Badge con tooltip esplicativo su hover

### 5.2 Badge "Multi-Tenant Isolato"

**Design:**
```css
Background: #E8EDF4 (Blu Extra Light)
Color: #0F1E36 (Blu Dark)
Border: 1px solid #1B365D
Border-radius: 6px
Padding: 6px 12px
Font-size: 11px
Font-weight: 500
Icon: 🛡️ shield (16px)
```

**Posizionamento:**
- Info bar sotto header
- Tenant selector dropdown

### 5.3 URS Badge (Certezza Informazioni)

**Design Base:**
```css
Display: inline-flex
Align-items: center
Gap: 8px
Padding: 6px 12px
Border-radius: 12px (pill)
Font-weight: 700
Font-size: 14px
Font-family: JetBrains Mono (per score numerico)
```

**Varianti per Score:**

**URS A:**
```css
Background: #10B981 (Emerald-500)
Color: #FFFFFF
Border: 2px solid #059669
Shadow: 0 2px 4px rgba(16, 185, 129, 0.3)
```

**URS B:**
```css
Background: #3B82F6 (Blue-500)
Color: #FFFFFF
Border: 2px solid #2563EB
Shadow: 0 2px 4px rgba(59, 130, 246, 0.3)
```

**URS C:**
```css
Background: #F59E0B (Amber-500)
Color: #FFFFFF
Border: 2px solid #D97706
Shadow: 0 2px 4px rgba(245, 158, 11, 0.3)
```

**URS X:**
```css
Background: #EF4444 (Red-500)
Color: #FFFFFF
Border: 2px solid #DC2626
Shadow: 0 2px 4px rgba(239, 68, 68, 0.3)
```

**Componente HTML:**
```html
<div class="urs-badge urs-badge-a">
  <span class="urs-label">A</span>
  <span class="urs-score">0.93</span>
</div>
```

### 5.4 Badge "Fonte Verificata"

**Design:**
```css
Background: #E8F4E3 (Verde Extra Light)
Color: #2D5016 (Verde Certificazione)
Border: 1px solid #2D5016
Border-radius: 6px
Padding: 4px 10px
Font-size: 11px
Font-weight: 600
Icon: ✓ check-circle (14px)
```

**Posizionamento:**
- Accanto a ogni fonte citata nel claim
- Lista fonti sotto ogni risposta

### 5.5 Badge "Blockchain Notarizzato"

**Design:**
```css
Background: linear-gradient(135deg, #2D5016 0%, #3D6B22 100%)
Color: #FFFFFF
Border: 2px solid #B89968 (Oro Sobrio)
Border-radius: 8px
Padding: 6px 14px
Font-weight: 600
Font-size: 12px
Icon: ⛓️ link (18px) + 🔗 (blockchain icon)
```

**Posizionamento:**
- Badge documento quando notarizzato
- Footer claim quando blockchain-anchored
- Tooltip con TXID cliccabile

**Componente HTML:**
```html
<div class="blockchain-badge">
  <span class="icon">⛓️</span>
  <span class="text">Blockchain Notarizzato</span>
  <span class="txid" title="Algorand TXID: ABC123...">🔗</span>
</div>
```

### 5.6 Indicatore "Claim Bloccato"

**Design:**
```css
Background: #FEE2E2 (Red Light)
Color: #C13120 (Rosso Urgenza)
Border: 2px solid #EF4444
Border-radius: 8px
Padding: 12px 16px
Font-size: 13px
Font-weight: 500
Icon: 🚫 blocked (20px)
```

**Posizionamento:**
- Sezione dedicata sotto risposta
- Visibile solo se ci sono claim bloccati
- Spiega motivo blocco (URS < 0.5)

---

## 📋 **6. COMPONENTI UI NATAN-SPECIFICI**

### 6.1 Chat Message Component

**Struttura:**
```html
<div class="chat-message message-assistant">
  <!-- Header messaggio -->
  <div class="message-header">
    <span class="message-role">NATAN</span>
    <span class="trust-indicators">
      <span class="zero-leak-badge-mini">🔒 ZL</span>
      <span class="multi-tenant-badge-mini">🛡️ Isolato</span>
    </span>
  </div>
  
  <!-- Contenuto (markdown renderizzato) -->
  <div class="prose message-content">
    <!-- Risposta semantica formattata -->
  </div>
  
  <!-- Claims verificati -->
  <div class="claims-section">
    <h4>Affermazioni verificate con fonti accreditate:</h4>
    <!-- Claim cards con URS -->
  </div>
  
  <!-- Fonti -->
  <div class="sources-section">
    <!-- Lista fonti con badge "Verificata" -->
  </div>
</div>
```

### 6.2 Claim Card Component

**Design:**
```css
Background: #FFFFFF
Border: 2px solid [URS_COLOR]
Border-radius: 8px
Padding: 16px
Margin-bottom: 12px
Shadow: 0 2px 4px rgba(0,0,0,0.08)
```

**Struttura:**
```html
<div class="claim-card" data-urs="A" data-urs-score="0.93">
  <div class="claim-header">
    <div class="urs-badge urs-badge-a">
      <span>URS A (0.93)</span>
    </div>
    <span class="inference-badge">[Deduzione]</span> <!-- Opzionale -->
  </div>
  
  <p class="claim-text">Testo del claim...</p>
  
  <div class="claim-sources">
    <span class="sources-label">Fonti:</span>
    <a href="#" class="source-link verified">
      ✓ Documento X (p. 15)
    </a>
    <a href="#" class="source-link verified">
      ✓ Documento Y (p. 23)
    </a>
  </div>
  
  <!-- URS Breakdown (collapsible) -->
  <details class="urs-breakdown">
    <summary>Dettagli URS</summary>
    <!-- Breakdown metrics -->
  </details>
</div>
```

### 6.3 Sidebar Enterprise (Desktop)

**Struttura:**
```html
<aside class="sidebar-enterprise">
  <!-- Top: Cronologia Chat -->
  <div class="sidebar-section chat-history-section">
    <h3 class="section-title">CRONOLOGIA CHAT</h3>
    <div class="chat-history-list">
      <!-- Ultime 3 chat: sempre espanse -->
      <div class="chat-history-item active expanded">
        <span class="chat-title">Tokenomics e Equilibrium</span>
        <span class="chat-date">Oggi, 14:30</span>
      </div>
      <div class="chat-history-item expanded">
        <span class="chat-title">Analisi documenti PA</span>
        <span class="chat-date">Oggi, 10:15</span>
      </div>
      <div class="chat-history-item expanded">
        <span class="chat-title">Richiesta informazioni</span>
        <span class="chat-date">Ieri, 16:45</span>
      </div>
      
      <!-- Chat precedenti: collassabili -->
      <details class="chat-history-collapsible">
        <summary>Chat precedenti (12)</summary>
        <!-- Lista chat più vecchie -->
      </details>
    </div>
  </div>
  
  <!-- Separatore -->
  <hr class="sidebar-separator" />
  
  <!-- Bottom: Feature Menu -->
  <div class="sidebar-section feature-menu-section">
    <h3 class="section-title">DOCUMENTI</h3>
    <hr class="section-separator" />
    <nav class="feature-menu">
      <a href="#" class="menu-item">
        <span class="icon">📄</span>
        <span class="label">Lista documenti</span>
      </a>
      <a href="#" class="menu-item">
        <span class="icon">📤</span>
        <span class="label">Upload nuovo</span>
      </a>
    </nav>
    
    <h3 class="section-title">PROGETTI</h3>
    <hr class="section-separator" />
    <!-- ... -->
    
    <h3 class="section-title">RACCOLTA</h3>
    <hr class="section-separator" />
    <!-- ... -->
    
    <h3 class="section-title">INTELLIGENCE</h3>
    <hr class="section-separator" />
    <!-- ... -->
    
    <h3 class="section-title">COSTI</h3>
    <hr class="section-separator" />
    <!-- ... -->
  </div>
  
  <!-- Footer: Trust Badge -->
  <div class="sidebar-footer">
    <div class="zero-leak-badge-full">
      🔒 Zero-Leak Perimetrale
    </div>
  </div>
</aside>
```

**Styling:**
```css
.sidebar-enterprise {
  width: 280px;
  background: #1B365D; /* Blu Trust */
  color: #FFFFFF;
  height: 100vh;
  overflow-y: auto;
  position: fixed;
  left: 0;
  top: 0;
}

.section-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #E8EDF4; /* Blu Extra Light */
  margin: 24px 16px 12px 16px;
}

.section-separator {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin: 0 16px 12px 16px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #FFFFFF;
  text-decoration: none;
  transition: background 0.2s;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.menu-item.active {
  background: #2C4A7C; /* Blu Light */
  border-left: 3px solid #B89968; /* Oro Sobrio */
}
```

### 6.4 Panel Destro (Domande Strategiche)

**Design:**
```css
Width: 320px (fisso)
Background: #F7FAFC
Border-left: 1px solid #E2E8F0
Height: 100vh
Overflow-y: auto
Position: sticky
Top: 0
```

**Struttura:**
```html
<aside class="panel-right">
  <!-- Header collassabile -->
  <div class="panel-header">
    <h3>Domande Strategiche</h3>
    <button class="collapse-btn" aria-label="Collassa pannello">▼</button>
  </div>
  
  <!-- Tabs: Domande / Spiegazioni / Consulenti -->
  <div class="panel-tabs">
    <button class="tab active">Domande</button>
    <button class="tab">Spiegazioni</button>
    <button class="tab">Consulenti</button>
  </div>
  
  <!-- Contenuto scrollabile -->
  <div class="panel-content">
    <!-- Domande strategiche categorizzate -->
    <!-- Spiegazioni e guide -->
    <!-- Selettore consulenti (personas) -->
  </div>
</aside>
```

**Mobile:** Panel destro diventa bottom sheet o drawer laterale

### 6.5 Mobile Drawer (Sidebar Feature)

**Design:**
```css
Position: fixed
Top: 0
Left: -280px (hidden)
Width: 280px
Height: 100vh
Background: #1B365D
Z-index: 1000
Transition: transform 0.3s ease

/* Quando aperto */
.drawer-open {
  transform: translateX(280px);
}

/* Overlay scuro dietro */
.drawer-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}
```

---

## 📱 **7. RESPONSIVE DESIGN MOBILE-FIRST**

### 7.1 Breakpoints

```css
/* Mobile First */
/* Base: 320px - 767px (mobile) */

/* Tablet */
@media (min-width: 768px) {
  /* 2 colonne dove possibile */
  /* Sidebar collapsabile */
}

/* Desktop */
@media (min-width: 1024px) {
  /* Layout 3 colonne completo */
  /* Sidebar sempre visibile */
  /* Panel destro collassabile */
}
```

### 7.2 Mobile Adaptations

**Header:**
- Altezza: 64px (ridotta da 72px)
- Font-size: -1px rispetto desktop
- Solo icone essenziali visibili

**Chat:**
- Padding: 12px (ridotto da 24px)
- Font-size: 14px (body), 16px (headings)
- Claims: stack verticale (no grid)

**Input:**
- Altezza: 44px (touch target)
- Button send: solo icona (no testo)

**Suggerimenti/Consulenti:**
- Default: chiusi (solo header visibile)
- Aperti: max-height 200px (scroll interno)

### 7.3 Touch Targets

**Minimo 44x44px per:**
- Bottoni
- Link
- Icone interattive
- Menu items
- Checkbox/radio

---

## 🎭 **8. TONE OF VOICE MARKETING**

### 8.1 Principi Fondamentali

**Claim ufficiale:**
> "Non immagina. **Dimostra**."

**Payoff:**
> "Affidabile come un notaio, sicura come un caveau, intelligente come un esperto."

### 8.2 Messaggi Chiave

**Per PA:**
- "NATAN: Cognitive Trust Layer per PA & Enterprises"
- "Zero-leak perimetrale: dati segregati e mai inviati in chiaro"
- "Citazioni visibili in ogni risposta, fonti verificate sempre"

**Per Enterprises:**
- "Sicurezza totale: multi-tenant isolato, embeddings non reversibili"
- "Verifica pubblica: hash notarizzati su blockchain quando richiesto"
- "Local Mode: on-prem/air-gap per siti ad alta riservatezza"

### 8.3 Tone Caratteristiche

- **Istituzionale:** Chiaro, autorevole, sobrio
- **Anti-hype:** Mostra, non promette
- **Trasparente:** Citazioni sempre visibili
- **Professionale:** Terminologia precisa, no gergo marketing

### 8.4 Frasi Approvate

**Sicurezza:**
- "Zero accessi ai dati: solo embeddings anonimi"
- "Multi-tenant isolato: ogni ente ha il proprio spazio segregato"
- "GDPR compliant by design: audit trail completo"

**Affidabilità:**
- "URS certificato: ogni claim ha un score di affidabilità verificabile"
- "Fonti sempre citate: ogni risposta dimostra la propria origine"
- "Blockchain notarizzato: hash pubblici per verifica indipendente"

**Intelligenza:**
- "AI-agnostic: gateway multimodello, nessun vendor lock-in"
- "RAG certificato: risposte basate solo su documenti verificati"
- "Personas specializzate: risposte con expertise specifica"

---

## 🎨 **9. ANIMAZIONI E TRANSIZIONI**

### 9.1 Principio: Eleganza Sobria

**Durata Standard:**
- Micro: 150ms (hover, focus)
- Breve: 250ms (dropdown, modal, drawer)
- Media: 350ms (page transition)
- Lunga: 500ms (skeleton loading)

**Easing:**
- Default: `cubic-bezier(0.4, 0, 0.2, 1)`
- Entrata: `cubic-bezier(0, 0, 0.2, 1)`
- Uscita: `cubic-bezier(0.4, 0, 1, 1)`

### 9.2 Animazioni Consentite

**YES:**
- ✅ Fade in/out (opacity)
- ✅ Slide up/down (transform translateY)
- ✅ Slide left/right (drawer)
- ✅ Scale (1 → 1.02 per hover)
- ✅ Smooth scroll (scroll-behavior: smooth)

### 9.3 Animazioni Vietate

**NO:**
- ❌ Bounce effects (poco professionale)
- ❌ Rotate/spin oltre loading spinners
- ❌ Parallax effects (distrazione)
- ❌ Anything "playful" o "creative"
- ❌ Blur transitions (pesanti su mobile)

---

## ♿ **10. ACCESSIBILITÀ OBBLIGATORIA**

### 10.1 Standard WCAG 2.1 AA

**Contrasti Minimi:**
- Testi normali: 4.5:1 ✅
- Testi grandi (18px+): 3:1 ✅
- Elementi UI: 3:1 ✅

**Validazione:**
- Tool: axe DevTools, Lighthouse
- Test su ogni componente prima deploy

### 10.2 Navigazione Tastiera

**Tab Order:**
- Logico e sequenziale
- Skip link per saltare nav
- Focus indicators visibili (outline 2px solid #1B365D)

**ARIA Labels:**
- Obbligatori su tutti gli icon buttons
- Form labels espliciti (no placeholder-only)
- Role attributes per custom components

### 10.3 Screen Reader Optimization

**Regole:**
- Heading hierarchy corretta (H1 → H2 → H3)
- Alt text descrittivi su immagini
- Aria-live regions per updates dinamici
- No content in ::before/::after essenziali

---

## 📊 **11. COMPONENTI PRIORITARI DA IMPLEMENTARE**

### Alta Priorità

1. **ChatInterface** - Componente chat principale (mobile-first)
2. **ChatMessage** - Rendering messaggi con markdown
3. **ClaimCard** - Card claim con URS badge
4. **UrsBadge** - Badge URS con colori semantici
5. **SourceLink** - Link fonte con badge "Verificata"
6. **SidebarEnterprise** - Sidebar desktop con cronologia + menu
7. **MobileDrawer** - Drawer mobile per feature sidebar
8. **TrustBadges** - Badge Zero-Leak, Multi-Tenant, Blockchain

### Media Priorità

9. **SuggestionPanel** - Panel suggerimenti (collassabile)
10. **ConsultantPanel** - Panel consulenti/personas
11. **QuestionPanel** - Panel domande strategiche (destro)
12. **ChatHistory** - Lista cronologia chat (stile ChatGPT)
13. **BlockchainBadge** - Badge notarizzazione blockchain

---

## ✅ **12. CHECKLIST CONFORMITÀ**

**Prima di ogni deploy componente NATAN, verificare:**

- [ ] Usa palette colori NATAN (Blu Trust dominante)
- [ ] Tipografia IBM Plex Sans per headings
- [ ] Contrasti WCAG 2.1 AA rispettati
- [ ] Touch targets minimo 44px (mobile)
- [ ] ARIA labels presenti
- [ ] Tab navigation funzionante
- [ ] Responsive testato su 3 breakpoints (mobile/tablet/desktop)
- [ ] Animazioni sobrie (no bounce/spin)
- [ ] URS colors solo per badge/indicatori specifici
- [ ] Trust badges visibili (Zero-Leak, Multi-Tenant)
- [ ] Fonti sempre citate con badge "Verificata"
- [ ] Mobile-first: funziona su 320px
- [ ] Chat sempre accessibile (dashboard centrale)

---

## 📚 **13. RISORSE E ASSET**

### 13.1 CSS Variables (Tailwind Config)

```javascript
// tailwind.config.js - NATAN theme
module.exports = {
  theme: {
    extend: {
      colors: {
        // Blu Trust (Istituzionale)
        "natan-blue": {
          DEFAULT: "#1B365D",
          light: "#2C4A7C",
          dark: "#0F1E36",
          "extra-light": "#E8EDF4",
        },
        // Grigi Strutturali
        "natan-gray": {
          900: "#2D3748",
          700: "#4A5568",
          500: "#718096",
          300: "#CBD5E0",
          100: "#EDF2F7",
          50: "#F7FAFC",
        },
        // Trust & Certificazione
        "natan-trust": "#0F4C75", // Blu Sicurezza
        "natan-green": "#2D5016", // Verde Certificazione
        "natan-gold": "#B89968", // Oro Sobrio
        // URS Colors
        "urs-a": "#10B981", // Emerald
        "urs-b": "#3B82F6", // Blue
        "urs-c": "#F59E0B", // Amber
        "urs-x": "#EF4444", // Red
        // Funzionali
        "natan-red": "#C13120",
        "natan-orange": "#E67E22",
      },
      fontFamily: {
        institutional: ["IBM Plex Sans", "Inter", "sans-serif"],
        body: ["Source Sans Pro", "sans-serif"],
        mono: ["JetBrains Mono", "monospace"],
      },
    },
  },
};
```

### 13.2 Iconografia

**Style:** Outline style, 24px default, stroke-width 2px  
**Color:** Inherited (cambierà per contesto)  
**Library:** Heroicons (MIT license) + custom icons NATAN

**Custom icons NATAN:**
- `lock-secure` - Zero-leak
- `shield-isolated` - Multi-tenant
- `certificate-badge` - Certificazione
- `blockchain-link` - Blockchain notarizzato
- `urs-badge` - URS indicator
- `source-verified` - Fonte verificata

---

## 🎯 **14. ROADMAP IMPLEMENTAZIONE**

### Fase 1: Foundation (Settimana 1-2)
- [ ] Setup CSS variables palette NATAN
- [ ] Import IBM Plex Sans + Source Sans Pro fonts
- [ ] Tailwind config NATAN theme
- [ ] Base layout mobile-first
- [ ] Header minimale mobile

### Fase 2: Chat Core (Settimana 3-4)
- [ ] ChatInterface component (mobile-first)
- [ ] ChatMessage component (markdown rendering)
- [ ] Input area (mobile-optimized)
- [ ] Mobile drawer sidebar

### Fase 3: Trust Components (Settimana 5-6)
- [ ] UrsBadge component (A/B/C/X)
- [ ] ClaimCard component
- [ ] SourceLink component (badge "Verificata")
- [ ] Trust badges (Zero-Leak, Multi-Tenant)
- [ ] BlockchainBadge component

### Fase 4: Sidebar & Panels (Settimana 7-8)
- [ ] SidebarEnterprise component (desktop)
- [ ] ChatHistory component (stile ChatGPT)
- [ ] Feature menu con separatori
- [ ] Panel destro (domande/spiegazioni/consulenti)
- [ ] Mobile drawer feature menu

### Fase 5: Polish & Accessibility (Settimana 9-10)
- [ ] WCAG 2.1 AA compliance completo
- [ ] Keyboard navigation
- [ ] Screen reader optimization
- [ ] Responsive testing completo
- [ ] Animazioni sobrie

---

## 📞 **15. RIFERIMENTI**

**Documenti Source:**
- `/home/fabio/EGI/docs/ai/marketing/PA_ENTERPRISE_BRAND_GUIDELINES.md`
- `/home/fabio/EGI/docs/ai/marketing/marketing_strategy_unified.md`
- `/home/fabio/EGI/docs/NATAN_LOC/natan_os_3_docs_batch_1_multimodel_gateway_white_paper_local_mode_marketing.md`

**Standard Compliance:**
- WCAG 2.1 AA: https://www.w3.org/WAI/WCAG21/quickref/
- PA Design System Italia: https://designers.italia.it/

**Testing Tools:**
- axe DevTools (accessibility)
- Lighthouse (performance + a11y)
- BrowserStack (cross-browser)

---

**Questo documento è la source of truth per ogni sviluppo UI/UX di NATAN_LOC.**

**Ogni deviazione deve essere discussa e approvata esplicitamente.**

_NATAN_LOC - Cognitive Trust Layer per PA & Enterprises_  
_"Non immagina. Dimostra."_

---

**#natan-design #trust-layer #mobile-first #institutional #ux-ui #brand-guidelines**

