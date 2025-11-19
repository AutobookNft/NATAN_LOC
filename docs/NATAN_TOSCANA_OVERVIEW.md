# NATAN -- Rete Cognitiva dei Comuni Toscani

## Documento Sintetico -- Versione 0.1 (Nov 2025)

------------------------------------------------------------------------

## 1. Scopo del Progetto

Creare un'infrastruttura cognitiva unificata per **tutti i Comuni della
Toscana**, basata su: - scraping degli Albi Pretori pubblici, -
indicizzazione semantica centralizzata (MongoDB), - tenant dedicato per
ogni Comune, - motore AI locale (NATAN) conforme a OS3/OS4, -
integrazione EcoDebt per misurare il debito ecologico digitale, -
dashboard di trasparenza locale + regionale.

Il sistema è progettato come servizio civico gratuito, estendibile a
modello nazionale.

------------------------------------------------------------------------

## 2. Architettura in Breve

-   **Raccolta Dati**: scraping quotidiano dei documenti degli Albi
    Pretori dei Comuni toscani.\
-   **Canonicalizzazione**: trasformazione in formato standardizzato
    OS3.\
-   **Storage Centrale**: database MongoDB con collections:
    -   `acts_raw`
    -   `acts_canonical`
    -   `tenants`
    -   `eco_debt_logs`
-   **Layer AI**: NATAN per ricerca, analisi, supporto procedurale.
-   **EcoDebt Core**: misurazione dell'impatto energetico/ambientale di
    ogni operazione.
-   **Tenant PA**: uno per ogni Comune, isolato e configurato con NATAN
    interno.\
-   **Dashboard Toscana Cognitiva**: vista globale dei Comuni,
    trasparenza atti, eco-debt.

------------------------------------------------------------------------

## 3. Funzioni Chiave

### 3.1. Per i Comuni

-   Ricerca intelligente atti locali.\
-   Analisi automatica dei documenti.\
-   Memoria e progetti intra-ufficio.\
-   Chat interna (uffici → Comune → Regione).\
-   Dashboard EcoDebt + compensazione via EPP.\
-   Opzione LLaMA locale completamente privata.

### 3.2. Per i Cittadini

-   Portale pubblico con:
    -   ricerca atti regionali,
    -   filtri per categoria e Comune,
    -   riepiloghi eco-debt,
    -   evidenza Comuni più virtuosi.

------------------------------------------------------------------------

## 4. Multi‑Tenant dei Comuni

Ogni Comune ottiene: - tenant dedicato,\
- utenza admin,\
- NATAN interno pronto all'uso,\
- periodo di prova gratuito di 90 giorni,\
- isolamento completo di dati e memoria,\
- possibilità di estendere funzioni (ingest storico, LLaMA, EPP).

Non viene venduto alcun prodotto:\
il modello è **accesso etico** con abbonamento volontario per sostenere
i costi.

------------------------------------------------------------------------

## 5. Toscana Cognitiva -- Livello Regionale

Il sistema centrale consente: - ricerca trasversale tra tutti i Comuni,\
- mappa dei flussi amministrativi,\
- eco-debt complessivo della Regione,\
- comparazione tra Comuni e settori,\
- evidenza delle buone pratiche.

------------------------------------------------------------------------

## 6. EcoDebt Core -- Integrazione

Ogni atto, ogni ingest, ogni query produce un log di: - energia
consumata (kWh), - emissioni stimate (CO₂e), - stato del debito (open /
neutralized / regenerative).

Gli enti possono compensare tramite **EPP FlorenceEGI** o progetti
equivalenti.

------------------------------------------------------------------------

## 7. Roadmap 2025--2026

1.  **Online Mongo + scraper**\
2.  **10 Comuni pilota**\
3.  **Tenant PA attivi**\
4.  **Dashboard Toscana**\
5.  **Integrazione EPP**\
6.  **Estensione a tutta la regione**\
7.  **Portale pubblico**\
8.  **Expansion modello nazionale**

------------------------------------------------------------------------

## 8. Etica, Sicurezza, Legalità

-   Nessuna raccolta di PII non necessaria.\
-   Dati pubblici trattati nel rispetto del DL 33/2013.\
-   AuditOS4 su tutte le operazioni.\
-   On‑premise possibile per massima privacy.\
-   Zero lock‑in: il Comune può esportare ogni dato.

------------------------------------------------------------------------

## 9. Visione Finale

Una piattaforma etica, trasparente, replicabile, che rende la Toscana la
prima regione d'Europa con: - trasparenza semantica totale, -
responsabilità ecologica digitale misurabile, - AI certificata e
verificabile, - infrastruttura civica condivisa.

Il tutto sviluppato come progetto indipendente, aperto, riproducibile
per ogni Regione italiana.

------------------------------------------------------------------------

FlorenceEGI / NATAN -- 2025
