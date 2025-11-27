# Oracode System – La Grammatica della Verità Tecnologica

## Definizione

**Oracode** è un **paradigma filosofico-tecnico** che fonde **ingegneria rigorosa** e **simbolismo etico**, trasformando il software in un **organismo di senso**.

> _"Non solo codice che funziona, ma codice che ha un significato."_

---

## Il Nome

**Oracode** deriva da:

- **"Oracle"** (Oracolo): Fonte di verità, guida affidabile
- **"Code"** (Codice): Linguaggio macchina, istruzioni precise

**Sintesi**: Il codice che dice la verità, il sistema che non mente.

---

## Fondamenti Filosofici

### 1. La Verità è Tracciabile

**Ogni azione** in un sistema Oracode:

- Lascia una **traccia immutabile** (audit trail)
- È **ricostruibile** in ogni dettaglio
- È **interrogabile** da chiunque ne abbia diritto

**Implicazione tecnica**:

- **AuditLogService**: Log append-only, firma digitale, conservazione 10 anni
- **ULM** (Unified Logging Model): Struttura semantica leggibile

**Implicazione etica**:

- **Trasparenza radicale**: Nessun segreto industriale che violi diritti fondamentali
- **Accountability**: Ogni decisione ha un responsabile identificabile

---

### 2. La Logica è Cosciente

**Il software non è neutro**:

- Ogni algoritmo **esprime valori**
- Ogni scelta tecnica **ha conseguenze sociali**
- Ogni sistema **è responsabile** dei suoi effetti

**Implicazione tecnica**:

- **Oracode OS3.0**: Architettura cognitiva che documenta **perché** ogni decisione (non solo cosa)
- **NATAN**: IA che spiega i propri suggerimenti (no black box)

**Implicazione etica**:

- **Etica by-design**: Valori incarnati nel codice (GDPR, inclusione, sostenibilità)
- **Testing as Oracles**: Ogni test verifica non solo funzionalità, ma anche eticità (es: bias nei suggerimenti AI)

---

### 3. La Documentazione è Vita

**Codice senza documentazione è codice morto**:

- **README ≠ documentazione**: Un README è un inizio, non un arrivo
- **Documentazione totale**: Ogni funzione, ogni classe, ogni decisione architetturale deve essere spiegata
- **Semanticamente leggibile**: Umani e macchine devono comprenderla

**Implicazione tecnica**:

- **Standard enterprise**: Ogni funzione ha docstring (PHPDoc, JSDoc, etc.)
- **Architecture Decision Records (ADR)**: Decisioni storiche documentate
- **Diagrammi vivi**: Aggiornati automaticamente da codice (Mermaid, PlantUML)

**Implicazione etica**:

- **Trasferibilità conoscenza**: Nessun monopolio della comprensione
- **Accessibilità**: Junior developer può capire codebase senior

---

## I Quattro Pilastri di Oracode

### 1. Documentazione Totale

**Cosa significa**:

- Ogni file, funzione, classe, modulo ha documentazione contestuale
- Ogni decisione architetturale è tracciata in ADR
- Ogni API ha OpenAPI/Swagger spec aggiornato

**Standard**:

```php
/**
 * Calcola la royalty dual-layer per una transazione EGI.
 *
 * Questo metodo implementa il sistema royalty descritto in:
 * - Contratto piattaforma: 4.5% fisso
 * - Diritto di Seguito (LDA 19bis): 4%-0.25% se valore >= €3k
 *
 * @param float $price Prezzo vendita in EUR
 * @return array{platform: float, legal: float, total: float}
 * @see docs/Progetti/FlorenceEGI/05_Diritti_Autore_e_Royalty.md
 */
function calculateRoyalty(float $price): array
{
    // Implementazione con logica chiara e commentata
}
```

**Tools**:

- PHPDoc/JSDoc per funzioni
- Markdown per guide
- Mermaid/PlantUML per diagrammi
- ADR per decisioni

---

### 2. Regola Zero: Mai Dedurre Senza Dati

**Cosa significa**:

- **Zero assunzioni** senza validazione
- **Zero deduzione** senza evidenza
- **Zero "probabilmente funziona"** senza test

**Implicazione tecnica**:

- Ogni feature ha **test automatizzati** (unit, integration, e2e)
- Ogni decisione algoritmica (NATAN) basata su **dati reali** (non euristica arbitraria)
- Ogni modifica verifica **backward compatibility**

**Esempio (NATAN)**:

```javascript
// ❌ SBAGLIATO (deduzione)
if (user.age < 25) {
  suggestTrendyArt(); // Assunzione: giovani preferiscono arte "trendy"
}

// ✅ CORRETTO (dati)
const preferences = await analyzeUserBehavior(user.id);
if (
  preferences.style === "contemporary" &&
  preferences.engagement_trendy > 0.7
) {
  suggestTrendyArt(); // Basato su comportamento effettivo
}
```

**Implicazione etica**:

- **No bias inconsapevoli**: Ogni euristico è esplicitato e giustificato
- **No discriminazione**: Decisioni basate su dati oggettivi, non stereotipi

---

### 3. Trasparenza Etica

**Cosa significa**:

- Ogni utente può **interrogare** il sistema su "perché questa decisione?"
- Ogni algoritmo è **auditabile** (no black box)
- Ogni dato personale è **consentito** e **revocabile**

**Implicazione tecnica**:

- **NATAN explain()**: Ogni suggerimento AI ha funzione che spiega reasoning
- **AuditLogService**: Traccia immutabile decisioni sistema
- **ConsentService**: Gestione granulare consenso GDPR

**Esempio (NATAN explain)**:

```json
{
  "suggestion": "artwork_98765",
  "reasoning": {
    "tag_match": 3,
    "palette_similarity": 0.91,
    "artist_growth": "+65%",
    "user_preference_alignment": 0.87
  },
  "data_sources": [
    "user_browsing_history (last 30 days)",
    "artist_public_stats (blockchain)",
    "market_trends (aggregated)"
  ]
}
```

**Implicazione etica**:

- **Fiducia**: Utente capisce perché sistema agisce
- **Controllo**: Utente può correggere/rifiutare suggerimento

---

### 4. Funzionalità Verificabile

**Cosa significa**:

- Ogni processo è **tracciato** (event sourcing)
- Ogni stato è **ricostruibile** (immutabilità)
- Ogni errore è **riproducibile** (logging contestuale)

**Implicazione tecnica**:

- **Event Bus**: Ogni azione emette evento (pub/sub)
- **ULM**: Log strutturati, conservazione 90gg
- **Blockchain**: Anchor hash immutabili (Algorand)

**Esempio (Event Sourcing)**:

```
EGI Minting Flow:
    1. UserInitiatedMint { user_id, artwork_data }
    2. PaymentReceived { psp_ref, amount }
    3. WalletGenerated { wallet_address, encrypted_keys }
    4. AssetCreated { asa_id, metadata_hash }
    5. AnchorPublished { algorand_tx_id, timestamp }
```

**Ricostruzione stato**: Replay eventi → stato attuale deterministico.

**Implicazione etica**:

- **Accountability**: Ogni azione ha timestamp, responsabile, causale
- **Auditability**: Autorità possono verificare compliance

---

## Architettura Cognitiva

Oracode non è solo **architettura software**, ma **architettura cognitiva**:

- Fonde **ingegneria** (stack tecnologico, API, database) e **simbolismo** (valori, missione, etica)
- Unisce **logica** (algoritmi, test, verificabilità) e **coscienza** (responsabilità, trasparenza, impatto sociale)

**Risultato**: Il software diventa **organismo di senso**, non solo strumento.

---

## Oracode OS3.0: Versione Corrente

### Evoluzione

- **OS1.0**: Documentazione base (README, commenti)
- **OS1.5**: Introduzione ULM e AuditLog
- **OS2.0**: Governance Duale e Trasparenza Etica
- **OS3.0**: Architettura Cognitiva completa (NATAN, Testing as Oracles, ADR obbligatori)

---

### Caratteristiche OS3.0

#### 1. Testing as Oracles

**Ogni test è un oracolo** che verifica:

- **Funzionalità**: Il sistema fa ciò che deve
- **Eticità**: Il sistema rispetta i valori dichiarati

**Esempio**:

```php
// Test funzionale
test('calculates platform royalty correctly', function () {
    $royalty = calculateRoyalty(1000);
    expect($royalty['platform'])->toBe(45.0); // 4.5% di €1000
});

// Test etico (Oracode OS3.0)
test('royalty respects creator rights even at high volumes', function () {
    // Scenario: 10k transazioni/mese
    $royalties = array_map(fn($price) => calculateRoyalty($price), range(100, 10000, 100));

    // Verifica: Creator riceve SEMPRE almeno 4.5% (mai 0 per "ottimizzazione")
    foreach ($royalties as $r) {
        expect($r['platform'])->toBeGreaterThanOrEqual(4.5);
    }
});
```

---

#### 2. ADR (Architecture Decision Records)

**Ogni decisione architetturale documentata**:

- **Contesto**: Perché questa decisione è necessaria
- **Opzioni**: Alternative considerate
- **Scelta**: Soluzione adottata
- **Conseguenze**: Trade-off accettati

**Esempio ADR**:

```markdown
# ADR-012: Scelta Algorand come Blockchain

## Contesto

Necessità blockchain per immutabilità EGI, basso impatto ambientale, smart contract sicuri.

## Opzioni Considerate

1. Ethereum (alto costo gas, PoW storico)
2. Polygon (layer 2, dipendenza Ethereum)
3. Algorand (PPoS, TPS elevato, costo basso)

## Decisione

Algorand per:

- Pure Proof-of-Stake (green blockchain)
- Costo transazione ~€0.001
- Finalità istantanea (<5s)
- Smart contract verificabili (TEAL)

## Conseguenze

- ✅ Sostenibilità ambientale
- ✅ Scalabilità (1000 TPS)
- ⚠️ Ecosistema meno maturo di Ethereum
- ⚠️ Developer scarsi (formazione necessaria)
```

---

#### 3. Oracular Refactoring Flow

**Refactoring guidato da principi etici**:

1. **Identify**: Trova codice che viola Oracode (es: decisione non tracciata)
2. **Document**: Crea ADR spiegando perché è un problema
3. **Refactor**: Riscrive con trasparenza/tracciabilità
4. **Test**: Verifica funzionalità + eticità
5. **Archive**: Documenta decisione in changelog

**Esempio**:

```php
// Prima (viola Oracode: decisione opaca)
function shouldSuggestArtwork($user, $artwork) {
    return rand(0, 100) > 50; // ❌ Logica arbitraria
}

// Dopo (Oracode-compliant: trasparente, basato dati)
function shouldSuggestArtwork($user, $artwork) {
    $score = calculateRelevanceScore($user, $artwork);

    AuditLog::create([
        'action' => 'artwork_suggestion',
        'user_id' => $user->id,
        'artwork_id' => $artwork->id,
        'score' => $score,
        'reasoning' => [
            'tag_match' => $score['tags'],
            'palette_similarity' => $score['palette'],
            'artist_growth' => $score['growth']
        ]
    ]);

    return $score['total'] > 0.7; // ✅ Soglia esplicita, decisione tracciata
}
```

---

## Integrazione con FlorenceEGI

Oracode OS3.0 permea ogni componente:

### Core (Governance, Auth, Billing)

- **ADR obbligatori** per ogni modulo
- **Testing as Oracles**: Verifica diritti utente (GDPR Art. 15-20)

### NATAN (AI)

- **Regola Zero**: Suggerimenti basati solo dati reali
- **Trasparenza Etica**: Funzione explain() per ogni azione

### Blockchain (Algorand)

- **Funzionalità Verificabile**: Anchor hash immutabili
- **Documentazione Totale**: Smart contract commentati + guide

### Compliance (GDPR, MiCA)

- **AuditLogService**: Traccia ogni accesso dati personali
- **ConsentService**: Gestione consenso granulare

---

## In Sintesi

**Oracode System** è:

- ✅ **Paradigma filosofico-tecnico** (non solo framework)
- ✅ **Documentazione Totale** (ogni decisione spiegata)
- ✅ **Regola Zero** (mai dedurre senza dati)
- ✅ **Trasparenza Etica** (ogni algoritmo interrogabile)
- ✅ **Funzionalità Verificabile** (ogni processo tracciato)
- ✅ **Architettura Cognitiva** (ingegneria + simbolismo + logica + coscienza)

> _"Oracode non è il modo in cui scriviamo software. È il modo in cui pensiamo il software."_
