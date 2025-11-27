<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlorenceEGI - Fonte di Verità</title>
    <meta name="description"
        content="Documentazione tecnica completa FlorenceEGI. Architettura sistema, specifiche tecniche, standard e best practices. Single Source of Truth per sviluppatori.">
    <meta name="keywords"
        content="FlorenceEGI,Documentation,Source of Truth,Technical,Architecture,Blockchain,API,Standards">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="author" content="FlorenceEGI">
    <meta name="language" content="it">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph Protocol -->
    <meta property="og:title" content="FlorenceEGI - Fonte di Verità Tecnica">
    <meta property="og:description" content="Documentazione completa architettura e specifiche tecniche.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="FlorenceEGI">
    <meta property="og:locale" content="it_IT">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="FlorenceEGI - Fonte di Verità">
    <meta name="twitter:description" content="Documentazione tecnica completa sistema.">
    <meta name="twitter:site" content="@FlorenceEGI">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "FlorenceEGI - Fonte di Verità Tecnica",
        "description": "Single Source of Truth per architettura e specifiche tecniche",
        "author": {
            "@type": "Organization",
            "name": "FlorenceEGI",
            "url": "https://florence-egi.com"
        },
        "url": "{{ url()->current() }}",
        "inLanguage": "it",
        "isPartOf": {
            "@type": "WebSite",
            "@id": "https://florence-egi.com/#website"
        }
    }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 140px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdfcfb;
            color: #383838;
        }

        .active-nav {
            background-color: #047857;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .nav-item {
            transition: all 0.2s ease-in-out;
        }

        .content-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: auto;
            height: 350px;
        }

        .glossary-link {
            color: #047857;
            font-weight: 600;
            text-decoration: underline;
            text-decoration-color: #10b981;
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .glossary-link:hover {
            color: #065f46;
            text-decoration-thickness: 2.5px;
            text-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
        }

        dt:target {
            animation: highlightTerm 2s ease-in-out;
            scroll-margin-top: 140px;
        }

        @keyframes highlightTerm {
            0% {
                background-color: rgba(16, 185, 129, 0.3);
                transform: scale(1.02);
            }

            50% {
                background-color: rgba(16, 185, 129, 0.2);
            }

            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }

        #backToTextButton {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, rgba(4, 120, 87, 0.95) 0%, rgba(6, 95, 70, 0.95) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(4, 120, 87, 0.3);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border: 2px solid rgba(16, 185, 129, 0.8);
        }

        #backToTextButton:hover {
            transform: translateX(-50%) translateY(-2px) scale(1.05);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.5);
            border-color: #10b981;
        }

        #backToTextButton.show {
            display: flex;
            animation: slideInUp 0.4s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        .menu-parent {
            position: relative;
        }

        .submenu-trigger {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 8px;
            min-width: 220px;
            z-index: 100;
            margin-top: 8px;
        }

        .submenu.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .submenu-item {
            display: block;
            padding: 10px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            color: #374151;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .submenu-item:hover {
            background-color: #f3f4f6;
            color: #047857;
            transform: translateX(4px);
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen">
        <header class="sticky top-0 z-50 bg-white shadow-sm" role="banner">
            <div class="mx-auto max-w-7xl px-4 py-6 text-center sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-emerald-800">🌍 FlorenceEGI - Fonte di Verità</h1>
                <p class="text-md mt-2 text-gray-600">Sistema Unico e Integrale: dove arte, tecnologia e rigenerazione
                    convergono</p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" role="main">
            <div class="mb-8 rounded-2xl bg-white p-6 shadow-lg">
                <h2 class="mb-4 text-center text-xl font-bold text-gray-800">Esplora il Sistema FlorenceEGI</h2>
                <nav id="main-nav" class="flex flex-wrap justify-center gap-3 sm:gap-4" role="navigation"
                    aria-label="Navigazione sezioni">
                </nav>
            </div>

            <div id="content-container"></div>

            <section id="glossario" class="fade-in mt-16">
                <h2 class="mb-10 border-t border-gray-200 pt-12 text-center text-4xl font-bold text-emerald-800">
                    Glossario</h2>
                <div class="rounded-2xl bg-white p-8 shadow-lg">
                    <dl class="space-y-8">
                        <div>
                            <dt id="glossary-egi" class="text-xl font-bold text-emerald-700">EGI (Eco Goods Invent)</dt>
                            <dd class="mt-1 text-gray-700">Il certificato digitale che unisce valore estetico, traccia
                                <a href="#glossary-blockchain" class="glossary-link">blockchain</a> immutabile e
                                contributo ambientale tramite <a href="#glossary-epp" class="glossary-link">EPP</a>.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-epp" class="text-xl font-bold text-emerald-700">EPP (Environmental
                                Protection Projects)</dt>
                            <dd class="mt-1 text-gray-700">Progetti ambientali verificati integrati nativamente. Ogni
                                transazione destina automaticamente una quota a questi progetti.</dd>
                        </div>
                        <div>
                            <dt id="glossary-co-creatore" class="text-xl font-bold text-emerald-700">Co-Creatore</dt>
                            <dd class="mt-1 text-gray-700">Chi contribuisce alla nascita di un'opera tramite il <a
                                    href="#glossary-minting" class="glossary-link">minting</a>. Diventa parte indelebile
                                dell'identità dell'<a href="#glossary-egi" class="glossary-link">EGI</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-creator" class="text-xl font-bold text-emerald-700">Creator
                                (Artista/Autore)</dt>
                            <dd class="mt-1 text-gray-700">L'autore originale dell'opera. Riceve proventi vendite
                                primarie e <a href="#glossary-royalty-piattaforma" class="glossary-link">royalty</a>
                                automatiche sulle rivendite (4.5% + eventuale <a href="#glossary-diritto-seguito"
                                    class="glossary-link">Diritto di Seguito</a> legale >€3k). <strong>Conserva
                                    sempre</strong> i <a href="#glossary-diritti-morali" class="glossary-link">Diritti
                                    Morali</a> e <a href="#glossary-diritti-patrimoniali" class="glossary-link">Diritti
                                    Patrimoniali</a>, anche dopo la vendita.</dd>
                        </div>
                        <div>
                            <dt id="glossary-collector" class="text-xl font-bold text-emerald-700">Collector</dt>
                            <dd class="mt-1 text-gray-700">Il custode del valore che tramanda l'opera, acquisendo e
                                preservando <a href="#glossary-egi" class="glossary-link">EGI</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-royalty-piattaforma" class="text-xl font-bold text-emerald-700">Royalty
                                Piattaforma</dt>
                            <dd class="mt-1 text-gray-700">Percentuale (4.5%) garantita al Creator su
                                <strong>ogni</strong> rivendita, anche sotto €3,000. Automatica via smart contract,
                                <strong>separata</strong> dal <a href="#glossary-diritto-seguito"
                                    class="glossary-link">Diritto di Seguito</a> legale.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-diritto-seguito" class="text-xl font-bold text-emerald-700">Diritto di
                                Seguito</dt>
                            <dd class="mt-1 text-gray-700">Diritto <strong>legale</strong> (L. 633/1941 Art. 19bis) del
                                Creator: 4%-0.25% sulle rivendite ≥€3,000 tramite professionisti. Gestito SIAE.
                                <strong>Cumulabile</strong> con royalty piattaforma.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-morali" class="text-xl font-bold text-emerald-700">Diritti Morali
                                d'Autore</dt>
                            <dd class="mt-1 text-gray-700">Diritti <strong>inalienabili e perpetui</strong> (L.
                                633/1941 Art. 20): paternità (riconoscimento autore) e integrità (no modifiche). Il
                                Creator li <strong>conserva sempre</strong>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-patrimoniali" class="text-xl font-bold text-emerald-700">Diritti
                                Patrimoniali</dt>
                            <dd class="mt-1 text-gray-700">Diritti economici (L. 633/1941 Art. 12-19): riproduzione,
                                comunicazione pubblica, distribuzione. L'Owner <strong>NON acquisisce</strong> il
                                copyright comprando l'opera fisica o NFT.</dd>
                        </div>
                        <div>
                            <dt id="glossary-algorand" class="text-xl font-bold text-emerald-700">Algorand</dt>
                            <dd class="mt-1 text-gray-700"><a href="#glossary-blockchain"
                                    class="glossary-link">Blockchain</a> sostenibile e <a
                                    href="#glossary-carbon-negative" class="glossary-link">carbon-negative</a> basata
                                su
                                <a href="#glossary-proof-of-stake" class="glossary-link">Proof-of-Stake</a>.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-coa" class="text-xl font-bold text-emerald-700">CoA (Certificate of
                                Authenticity)</dt>
                            <dd class="mt-1 text-gray-700">Certificato di autenticità verificato collegato a ogni <a
                                    href="#glossary-egi" class="glossary-link">EGI</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-qr-nfc" class="text-xl font-bold text-emerald-700">QR/NFC</dt>
                            <dd class="mt-1 text-gray-700">Tecnologie unidirezionali che collegano il bene fisico al
                                certificato digitale per verifica pubblica immediata.</dd>
                        </div>
                        <div>
                            <dt id="glossary-gdpr" class="text-xl font-bold text-emerald-700">GDPR</dt>
                            <dd class="mt-1 text-gray-700">Regolamento Generale sulla Protezione dei Dati europeo.
                                FlorenceEGI è GDPR-by-design tramite <a href="#glossary-ulm"
                                    class="glossary-link">ULM</a>, <a href="#glossary-auditlogservice"
                                    class="glossary-link">AuditLogService</a> e <a href="#glossary-consentservice"
                                    class="glossary-link">ConsentService</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-mica" class="text-xl font-bold text-emerald-700">MiCA (Markets in
                                Crypto-Assets)</dt>
                            <dd class="mt-1 text-gray-700">Regolamento europeo sui mercati di cripto-attività.
                                FlorenceEGI è <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ulm" class="text-xl font-bold text-emerald-700">ULM (UltraLogManager)
                            </dt>
                            <dd class="mt-1 text-gray-700">Sistema di registrazione eventi per garantire auditabilità
                                completa e conformità <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-auditlogservice" class="text-xl font-bold text-emerald-700">
                                AuditLogService
                            </dt>
                            <dd class="mt-1 text-gray-700">Servizio per la creazione di <a
                                    href="#glossary-audit-trail" class="glossary-link">audit trail</a> verificabili.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-consentservice" class="text-xl font-bold text-emerald-700">ConsentService
                            </dt>
                            <dd class="mt-1 text-gray-700">Servizio per la gestione e il versioning dei consensi utente
                                secondo <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-psp" class="text-xl font-bold text-emerald-700">PSP (Payment Service
                                Provider)</dt>
                            <dd class="mt-1 text-gray-700">Fornitore di servizi di pagamento autorizzato (es. Stripe,
                                Adyen) che gestisce pagamenti FIAT.</dd>
                        </div>
                        <div>
                            <dt id="glossary-governance-duale" class="text-xl font-bold text-emerald-700">Governance
                                Duale</dt>
                            <dd class="mt-1 text-gray-700">Struttura basata su FlorenceEGI SRL (motore operativo) e
                                Associazione Frangette APS (custode dei valori).</dd>
                        </div>
                        <div>
                            <dt id="glossary-fee-dinamiche" class="text-xl font-bold text-emerald-700">Fee Dinamiche
                            </dt>
                            <dd class="mt-1 text-gray-700">Sistema di commissioni che si riducono al crescere della
                                community, creando economia rigenerativa.</dd>
                        </div>
                        <div>
                            <dt id="glossary-equilibrium" class="text-xl font-bold text-emerald-700">Equilibrium</dt>
                            <dd class="mt-1 text-gray-700">Token che alimenta il sistema di premi, ranking e
                                interazioni etiche.</dd>
                        </div>
                        <div>
                            <dt id="glossary-egili" class="text-xl font-bold text-emerald-700">Egili</dt>
                            <dd class="mt-1 text-gray-700">Micro-unità del token <a href="#glossary-equilibrium"
                                    class="glossary-link">Equilibrium</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-royalty-piattaforma" class="text-xl font-bold text-emerald-700">Royalty
                                Piattaforma (Contrattuale)</dt>
                            <dd class="mt-1 text-gray-700">Percentuale (4.5%) che FlorenceEGI garantisce al Creator su
                                <strong>ogni</strong> rivendita secondaria, anche sotto €3,000. Gestita automaticamente
                                via smart contract. Questa è una royalty <strong>contrattuale</strong>, separata e
                                aggiuntiva al <a href="#glossary-diritto-seguito" class="glossary-link">Diritto di
                                    Seguito</a> legale.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-diritto-seguito" class="text-xl font-bold text-emerald-700">Diritto di
                                Seguito (Legale)</dt>
                            <dd class="mt-1 text-gray-700">Diritto previsto dalla <strong>Legge 633/1941 Art.
                                    19bis</strong> che garantisce al Creator una percentuale (4%-0.25%) sulle rivendite
                                <strong>solo se</strong>: (1) prezzo ≥ €3,000, (2) vendita tramite professionisti
                                (gallerie/aste), (3) nell'Unione Europea. Gestito da SIAE. È <strong>cumulabile</strong>
                                con la <a href="#glossary-royalty-piattaforma" class="glossary-link">Royalty
                                    Piattaforma</a>.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-morali" class="text-xl font-bold text-emerald-700">Diritti Morali
                                d'Autore</dt>
                            <dd class="mt-1 text-gray-700">Diritti <strong>inalienabili e perpetui</strong> del Creator
                                (Legge 633/1941 Art. 20): (1) <strong>Paternità</strong> - diritto di essere sempre
                                riconosciuto come autore, (2) <strong>Integrità</strong> - diritto di opporsi a
                                modifiche/alterazioni dell'opera. Il Creator <strong>conserva sempre</strong> questi
                                diritti, anche dopo la vendita dell'opera fisica.</dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-patrimoniali" class="text-xl font-bold text-emerald-700">Diritti
                                Patrimoniali d'Autore</dt>
                            <dd class="mt-1 text-gray-700">Diritti economici del Creator (Legge 633/1941 Art. 12-19):
                                <strong>riproduzione</strong> (fare copie/stampe), <strong>comunicazione al
                                    pubblico</strong> (pubblicare online/TV), <strong>distribuzione</strong> (vendere
                                copie). L'Owner che compra l'opera fisica <strong>NON acquisisce</strong> questi
                                diritti, che restano al Creator salvo cessione esplicita scritta.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-mecenate" class="text-xl font-bold text-emerald-700">Mecenate</dt>
                            <dd class="mt-1 text-gray-700">Chi sostiene un artista o collezione, diventando attivatore
                                di valore culturale con profilo verificato.</dd>
                        </div>
                        <div>
                            <dt id="glossary-curatore" class="text-xl font-bold text-emerald-700">Curatore</dt>
                            <dd class="mt-1 text-gray-700">Professionista che seleziona e valorizza opere. FlorenceEGI
                                permette una carriera come curatore digitale.</dd>
                        </div>
                        <div>
                            <dt id="glossary-natan" class="text-xl font-bold text-emerald-700">NATAN (Neural
                                Assistant)</dt>
                            <dd class="mt-1 text-gray-700">
                                Tenant funzionale di FlorenceEGI dedicato ad assistenza documentale, notarizzazione,
                                servizi RAG e automazioni AI per enti pubblici e privati. Alimenta il <strong>NATAN
                                    Market Engine</strong> (Valuation + Activation) che rende la piattaforma un vero
                                AMMk.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-oracode" class="text-xl font-bold text-emerald-700">Oracode System</dt>
                            <dd class="mt-1 text-gray-700">Paradigma che fonde ingegneria e simbolismo. La grammatica
                                della verità tecnologica di FlorenceEGI.</dd>
                        </div>
                        <div>
                            <dt id="glossary-blockchain" class="text-xl font-bold text-emerald-700">Blockchain</dt>
                            <dd class="mt-1 text-gray-700">Registro digitale distribuito, immutabile e trasparente che
                                garantisce autenticità degli <a href="#glossary-egi" class="glossary-link">EGI</a>.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-hash" class="text-xl font-bold text-emerald-700">Hash Crittografico</dt>
                            <dd class="mt-1 text-gray-700">Sequenza unica generata da algoritmo che garantisce
                                integrità di file o documento.</dd>
                        </div>
                        <div>
                            <dt id="glossary-proof-of-stake" class="text-xl font-bold text-emerald-700">Proof-of-Stake
                            </dt>
                            <dd class="mt-1 text-gray-700">Meccanismo di consenso <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a> usato da <a href="#glossary-algorand"
                                    class="glossary-link">Algorand</a>, più efficiente e sostenibile.</dd>
                        </div>
                        <div>
                            <dt id="glossary-carbon-negative" class="text-xl font-bold text-emerald-700">
                                Carbon-Negative</dt>
                            <dd class="mt-1 text-gray-700">Tecnologia che assorbe più CO2 di quanto ne produce. <a
                                    href="#glossary-algorand" class="glossary-link">Algorand</a> è carbon-negative.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-minting" class="text-xl font-bold text-emerald-700">Minting</dt>
                            <dd class="mt-1 text-gray-700">Atto di creare un nuovo <a href="#glossary-egi"
                                    class="glossary-link">EGI</a> sulla <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a>, registrandolo per la prima volta.</dd>
                        </div>
                        <div>
                            <dt id="glossary-seo-oriented" class="text-xl font-bold text-emerald-700">SEO-Oriented
                            </dt>
                            <dd class="mt-1 text-gray-700">Ottimizzato per motori di ricerca. <a
                                    href="#glossary-natan" class="glossary-link">N.A.T.A.N.</a> suggerisce descrizioni
                                SEO-oriented.</dd>
                        </div>
                        <div>
                            <dt id="glossary-mica-safe" class="text-xl font-bold text-emerald-700">MiCA-safe</dt>
                            <dd class="mt-1 text-gray-700">Conforme al regolamento <a href="#glossary-mica"
                                    class="glossary-link">MiCA</a>. FlorenceEGI non gestisce fondi crypto per terzi.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-audit-trail" class="text-xl font-bold text-emerald-700">Audit Trail</dt>
                            <dd class="mt-1 text-gray-700">Registrazione cronologica completa e immutabile di tutte le
                                operazioni per verifiche e tracciabilità.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ammk" class="text-xl font-bold text-emerald-700">AMMk (Asset Market
                                Maker)</dt>
                            <dd class="mt-1 text-gray-700">
                                Termine coniato da FlorenceEGI per descrivere piattaforme che trasformano opere o
                                contenuti in <a href="#glossary-egi" class="glossary-link">EGI</a> (asset digitali) e
                                ne governano l’intero ciclo di valore. FlorenceEGI è il primo AMMk al mondo: il <a
                                    href="#glossary-florenceegi-core" class="glossary-link">FlorenceEGI Core</a>
                                coordina
                                i cinque engine (NATAN Market, Asset, Distribution, Co-Creation, Compliance).
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-florenceegi-core" class="text-xl font-bold text-emerald-700">FlorenceEGI
                                Core (SaaS)</dt>
                            <dd class="mt-1 text-gray-700">Nodo centrale dell’ecosistema: onboarding, autenticazione,
                                billing, ULM/UEM, audit, registro tenant e policy condivise.</dd>
                        </div>
                        <div>
                            <dt id="glossary-tenant-specializzato" class="text-xl font-bold text-emerald-700">Tenant
                                specializzato</dt>
                            <dd class="mt-1 text-gray-700">
                                Istanza verticale collegata al core con servizi dedicati: es. <strong>Natan</strong>
                                (AI,
                                notarizzazione, RAG) e <strong>FlorenceArtEGI</strong> (arte e marketplace). Ogni tenant
                                eredita sicurezza e compliance dal core, ma governa processi e UI dedicati.
                                FlorenceArtEGI
                                espone <em>collection workspace</em> multi-utente con wallet personali e wallet di
                                collection; per rispettare il limite Algorand di 16 account per gruppo atomico
                                (<a href="https://developer.algorand.org/docs/get-details/parameter_tables/#others"
                                    class="glossary-link" target="_blank"
                                    rel="noopener noreferrer">MaxTxGroupSize</a>)
                                la piattaforma riserva 4 slot al core lasciandone 12 alla collection (wallet utenti +
                                wallet tecnici).
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-event-bus" class="text-xl font-bold text-emerald-700">Event Bus</dt>
                            <dd class="mt-1 text-gray-700">Sistema che riceve trigger on-chain e off-chain e attiva
                                azioni <a href="#glossary-natan" class="glossary-link">NATAN</a>: campagne, notifiche,
                                suggerimenti prezzo. Collega smart contract intelligenti alle azioni della piattaforma.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-drops" class="text-xl font-bold text-emerald-700">Drops (Trimestrali)
                            </dt>
                            <dd class="mt-1 text-gray-700">Eventi trimestrali che selezionano opere eccellenti e
                                culminano in una <a href="#glossary-serata-memorabile" class="glossary-link">Serata
                                    Memorabile</a>, concentrando attenzione, incentivi e liquidità.</dd>
                        </div>
                        <div>
                            <dt id="glossary-serata-memorabile" class="text-xl font-bold text-emerald-700">Serata
                                Memorabile</dt>
                            <dd class="mt-1 text-gray-700">Evento finale di ogni <a href="#glossary-drops"
                                    class="glossary-link">Drop</a> trimestrale. Celebra Creator e valorizza le opere
                                più significative con esposizione, liquidità e premi.</dd>
                        </div>
                        <div>
                            <dt id="glossary-smart-contract-intelligenti" class="text-xl font-bold text-emerald-700">
                                Smart Contract Intelligenti</dt>
                            <dd class="mt-1 text-gray-700">Smart contract che emettono hook/trigger verso <a
                                    href="#glossary-event-bus" class="glossary-link">Event Bus</a> per attivare <a
                                    href="#glossary-natan" class="glossary-link">NATAN</a>. Non solo eseguono logica
                                on-chain, ma dialogano con layer applicativo.</dd>
                        </div>
                        <div>
                            <dt id="glossary-rbac" class="text-xl font-bold text-emerald-700">RBAC (Role-Based Access
                                Control)</dt>
                            <dd class="mt-1 text-gray-700">Sistema di controllo accessi basato su ruoli. FlorenceEGI ha
                                ruoli <strong>globali</strong> (account: User, Creator, Collector, Admin) e ruoli
                                <strong>locali</strong> (collection: Owner, Admin, Editor, Viewer).
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
        </main>

        <footer class="mt-16 bg-gray-900 text-white">
            <div class="container mx-auto max-w-7xl px-6 py-12">
                <p class="text-center text-gray-500">&copy; 2025 Florence EGI | Tutti i diritti riservati.</p>
            </div>
        </footer>

        <button id="backToTextButton" aria-label="Torna al testo">
            <span class="material-icons" style="font-size: 20px;">arrow_back</span>
            <span id="backToTextLabel">Torna al testo</span>
        </button>
    </div>

    <script>
        const contentData = {
            premessa: {
                title: 'Premessa',
                nav: 'Premessa',
                parent: 'fondamenti',
                intro: 'Il contesto che ha dato origine a FlorenceEGI: un\'epoca di connessione senza autenticità.',
                content: `<blockquote class="p-6 my-6 border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="text-xl italic text-gray-700">"Viviamo in un'epoca in cui tutto è connesso, ma poco è autentico. L'arte si è smaterializzata, la fiducia si è dispersa, la visibilità è diventata moneta fragile."</p>
                </blockquote>
                <p class="text-lg text-gray-700">In questo contesto nasce <strong>FlorenceEGI</strong>, un sistema che restituisce <strong>verità, riconoscimento e valore reale</strong> al mondo digitale.</p>
                <div class="grid gap-6 mt-8 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h3 class="mb-3 text-xl font-bold text-gray-800">Cosa NON È</h3>
                        <p class="text-gray-700">Non è un marketplace tradizionale, ma un <strong>protocollo di equilibrio</strong> tra materiale e immateriale, arte e tecnologia, individuo e collettività.</p>
                    </div>
                    <div class="p-6 rounded-lg bg-emerald-50">
                        <h3 class="mb-3 text-xl font-bold text-emerald-800">Cosa È</h3>
                        <p class="text-gray-700">Il luogo dove <strong>un'opera diventa vera</strong> perché qualcuno la crea, la riconosce e la completa tramite <a href="#glossary-egi" class="glossary-link">EGI</a> su <a href="#glossary-blockchain" class="glossary-link">blockchain</a>.</p>
                    </div>
                </div>`
            },
            principio: {
                title: 'Il Principio di Fondo',
                nav: 'Principio di Fondo',
                parent: 'fondamenti',
                intro: 'La visione fondamentale: l\'arte non è completa fino all\'incontro con il destinatario.',
                content: `<blockquote class="p-6 my-6 text-center border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="text-2xl italic font-bold text-emerald-800">"Non basta creare. Occorre che qualcuno accolga."</p>
                </blockquote>
                <p class="text-lg text-gray-700">L'artista genera la forma, ma è l'incontro con il <a href="#glossary-mecenate" class="glossary-link">mecenate</a> che <strong>attiva la sua piena esistenza</strong>.</p>
                <div class="p-6 mt-8 rounded-lg bg-gray-50">
                    <h3 class="mb-4 text-2xl font-bold text-emerald-700">La Trasformazione in <a href="#glossary-egi" class="glossary-link">EGI</a></h3>
                    <p class="mb-4 text-gray-700">L'opera diventa <strong>EGI – Eco Goods Invent</strong>, unendo:</p>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="p-4 text-center rounded-lg bg-emerald-50">
                            <div class="mb-2 text-3xl">🎨</div>
                            <h4 class="mb-2 font-semibold text-emerald-800">Valore Estetico</h4>
                            <p class="text-sm text-gray-700">Bellezza e significato culturale</p>
                        </div>
                        <div class="p-4 text-center rounded-lg bg-emerald-50">
                            <div class="mb-2 text-3xl">⛓️</div>
                            <h4 class="mb-2 font-semibold text-emerald-800">Traccia Digitale</h4>
                            <p class="text-sm text-gray-700">Immutabile su <a href="#glossary-algorand" class="glossary-link">Algorand</a></p>
                        </div>
                        <div class="p-4 text-center rounded-lg bg-emerald-50">
                            <div class="mb-2 text-3xl">🌱</div>
                            <h4 class="mb-2 font-semibold text-emerald-800">Contributo Ambientale</h4>
                            <p class="text-sm text-gray-700">Tramite <a href="#glossary-epp" class="glossary-link">EPP</a> verificati</p>
                        </div>
                    </div>
                </div>`
            },
            cocreazione: {
                title: 'Il Principio di Co-Creazione',
                nav: 'Co-Creazione',
                parent: 'fondamenti',
                intro: 'Il cuore del sistema: guarigione digitale attraverso visibilità permanente e riconoscimento.',
                content: `<div class="p-6 mb-6 border-l-4 border-blue-500 rounded-r-lg bg-blue-50">
                    <h3 class="mb-3 text-xl font-bold text-blue-800">Il Bisogno Universale</h3>
                    <p class="text-gray-700">Analisi su oltre 1000 fonti evidenzia: <strong>la forma di disagio più diffusa sul web è il bisogno di essere visibili e riconosciuti</strong>.</p>
                </div>
                <p class="text-lg text-gray-700">La rete concede visibilità effimera, ma non memoria. FlorenceEGI trasforma questa fragilità in <strong>struttura di guarigione digitale</strong>.</p>
                <div class="p-6 mt-8 rounded-lg bg-emerald-50">
                    <h3 class="mb-4 text-2xl font-bold text-emerald-700">I Tre Ruoli</h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-white rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-emerald-800">🎨 Artista</h4>
                            <p class="text-gray-700">La <strong>sorgente creativa</strong> dell'opera</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-emerald-800">✨ <a href="#glossary-co-creatore" class="glossary-link">Co-Creatore</a></h4>
                            <p class="text-gray-700">La <strong>causa efficiente</strong> che rende reale l'opera tramite <a href="#glossary-minting" class="glossary-link">minting</a></p>
                        </div>
                        <div class="p-4 bg-white rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-emerald-800">💎 <a href="#glossary-collector" class="glossary-link">Collector</a></h4>
                            <p class="text-gray-700">Il <strong>custode del valore</strong> che la tramanda</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <h4 class="mb-4 text-xl font-bold text-center text-emerald-700">Flusso di Co-Creazione</h4>
                    <div class="chart-container"><canvas id="coCreationChart"></canvas></div>
                </div>
                <blockquote class="p-6 mt-8 border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="italic text-gray-700">"Il <a href="#glossary-co-creatore" class="glossary-link">Co-Creatore</a> rimane per sempre legato all'opera. Anche se venduta, la sua firma resta visibile nella <a href="#glossary-blockchain" class="glossary-link">blockchain</a>. La visibilità non è più sintomo di bisogno, ma conseguenza naturale della partecipazione autentica."</p>
                </blockquote>`
            },
            architettura: {
                title: 'Architettura Tecnica',
                nav: 'Architettura',
                parent: 'sistema',
                intro: 'La tecnologia che garantisce immutabilità, sicurezza e sostenibilità.',
                content: `<div class="p-6 mb-6 rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-2xl font-bold text-emerald-700">Stack Tecnologico FlorenceEGI</h3>
                    <p class="text-gray-700">SaaS multi-tenant con <a href="#glossary-florenceegi-core" class="glossary-link">FlorenceEGI Core</a> (governance centrale) e tenant specializzati come <a href="#glossary-natan" class="glossary-link">Natan</a> e FlorenceArtEGI. Marketplace pubblico e protocol layer su <a href="#glossary-algorand" class="glossary-link">Algorand</a>.</p>
                </div>

                <div class="mb-8">
                    <h3 class="mb-4 text-xl font-bold text-emerald-700">🏗️ Componenti Principali</h3>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="p-4 rounded-lg bg-blue-50 border-l-4 border-blue-500">
                            <h4 class="font-bold text-blue-800 mb-2">App Web</h4>
                            <p class="text-sm text-gray-700">Laravel + TypeScript + Tailwind CSS</p>
                        </div>
                        <div class="p-4 rounded-lg bg-purple-50 border-l-4 border-purple-500">
                            <h4 class="font-bold text-purple-800 mb-2">AMMk Core</h4>
                            <p class="text-sm text-gray-700">Coordina i cinque engine: NATAN Market (Valuation + Activation), Asset, Distribution, Co-Creation, Compliance.</p>
                        </div>
                        <div class="p-4 rounded-lg bg-green-50 border-l-4 border-green-500">
                            <h4 class="font-bold text-green-800 mb-2">Marketplace Pubblico</h4>
                            <p class="text-sm text-gray-700">Discovery, listing, transazioni P2P</p>
                        </div>
                        <div class="p-4 rounded-lg bg-orange-50 border-l-4 border-orange-500">
                            <h4 class="font-bold text-orange-800 mb-2">Protocol Layer</h4>
                            <p class="text-sm text-gray-700">Algorand: ASA/SC, Proof, Fee Routing</p>
                        </div>
                        <div class="p-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-500">
                            <h4 class="font-bold text-yellow-800 mb-2">Event Bus</h4>
                            <p class="text-sm text-gray-700">Trigger on/off-chain → azioni <a href="#glossary-natan" class="glossary-link">NATAN</a></p>
                        </div>
                        <div class="p-4 rounded-lg bg-red-50 border-l-4 border-red-500">
                            <h4 class="font-bold text-red-800 mb-2">Observability</h4>
                            <p class="text-sm text-gray-700"><a href="#glossary-ulm" class="glossary-link">ULM</a>, UEM, AuditTrail, GDPR</p>
                        </div>
                    </div>
                </div>

                <div class="mb-8 p-6 rounded-lg bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-purple-300">
                    <h3 class="mb-4 text-2xl font-bold text-purple-800">🎯 Asset Market Maker (AMMk) Core</h3>
                    <p class="mb-4 text-gray-700">Il cuore di FlorenceEGI: un motore che origina, certifica, valuta e rende liquidi gli <a href="#glossary-egi" class="glossary-link">EGI</a>.</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <h4 class="font-bold text-purple-700 mb-2">🧠 NATAN Market Engine</h4>
                            <p class="text-sm text-gray-700">
                                Intelligenza del <a href="#glossary-natan" class="glossary-link">tenant NATAN</a> che rende la piattaforma un market maker:
                                <span class="block mt-1">• <strong>Valuation</strong> – definisce valore, floor price e traiettoria analizzando qualità, storico e domanda.</span>
                                <span class="block">• <strong>Activation</strong> – orchestration di campagne, alert e suggerimenti attivati da trigger on/off-chain.</span>
                            </p>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-700 mb-2">🧱 Asset Engine</h4>
                            <p class="text-sm text-gray-700">Gestisce listing, aste, vendite secondarie e liquidità degli EGI con regole trasparenti e marketplace integrato.</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-700 mb-2">🔄 Distribution Engine</h4>
                            <p class="text-sm text-gray-700">Automatizza royalty, fee piattaforma e quota <a href="#glossary-epp" class="glossary-link">EPP</a>, garantendo tracciabilità fiscale end-to-end.</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-700 mb-2">🤝 Co-Creation Engine</h4>
                            <p class="text-sm text-gray-700">Orchestra il flusso <a href="#glossary-co-creatore" class="glossary-link">Creator</a> / <a href="#glossary-co-creatore" class="glossary-link">Co-Creator</a> / <a href="#glossary-collector" class="glossary-link">Collector</a>: minting, notarizzazione, firme e catena di custodia dell’EGI.</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-700 mb-2">🛡️ Compliance Engine</h4>
                            <p class="text-sm text-gray-700">GDPR by design, audit trail completo, MiCA-safe e policy condivise per ogni tenant.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="mb-4 text-xl font-bold text-emerald-700">👥 Tenancy & RBAC (Role-Based Access Control)</h3>
                    <div class="p-6 rounded-lg bg-blue-50">
                        <p class="mb-4 text-gray-700">
                            <strong>FlorenceEGI è multi-tenant</strong>: il core SaaS governa identità e permessi,
                            mentre i tenant verticali applicano policy specifiche mantenendo sicurezza condivisa.
                        </p>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 class="font-bold text-blue-800 mb-2">Ruoli Globali (Core)</h4>
                                <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                                    <li>User / Creator / Collector (identità principali)</li>
                                    <li>Tenant Admin (gestione verticale)</li>
                                    <li>Platform Admin (governance core)</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-bold text-blue-800 mb-2">Ruoli Locali (Tenant)</h4>
                                <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                                    <li>Natan: operatori RAG, notarizzazione, auditor</li>
                                    <li>FlorenceArtEGI: curator, inspector, marketplace manager</li>
                                    <li>Collection workspace: owner, editor, viewer (quando rilevante)</li>
                                </ul>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-blue-700"><strong>Collection workspace (FlorenceArtEGI)</strong>: ogni collection può invitare collaboratori (fino a 8 wallet personali) e associare wallet “di collection” (fino a 4) per escrow, tesoreria e automazioni. In totale <strong>12 wallet</strong> disponibili, così da lasciare spazio ai 4 wallet core (fee payer, notarizzazione, compliance, treasury) e rispettare il limite Algorand di <a href="https://developer.algorand.org/docs/get-details/parameter_tables/#others" class="text-oro-fiorentino underline decoration-2 decoration-oro-fiorentino/60 hover:text-blu-algoritmo" target="_blank" rel="noopener noreferrer">16 account per gruppo atomico (MaxTxGroupSize)</a>.</p>
                        <p class="mt-2 text-sm text-blue-700"><strong>Enforcement</strong>: TenantResolver + Policy Laravel, scope su <code>tenant_id</code>, e gestione wallet tramite registro sicuro (AES-256). ULM/UEM e consensi GDPR assicurano audit e protezione dati.</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="mb-4 text-xl font-bold text-emerald-700">🔗 On-chain & Smart Contract Intelligenti</h3>
                    <div class="p-6 rounded-lg bg-green-50">
                        <ul class="space-y-3 text-gray-700">
                            <li><strong>Mint ASA</strong>: Creazione token <a href="#glossary-egi" class="glossary-link">EGI</a> su Algorand</li>
                            <li><strong>Smart Contract per CoA</strong>: Certificato di autenticità immutabile</li>
                            <li><strong>Escrow</strong>: Gestione sicura fondi durante transazioni</li>
                            <li><strong>Smart Contract "Intelligenti"</strong>: Emettono <strong>hook/trigger</strong> → Event Bus → <a href="#glossary-natan" class="glossary-link">NATAN</a></li>
                            <li><strong>Attestazioni</strong>: Provenance, ownership, EPP allocation on-chain</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-8 p-6 rounded-lg bg-gray-50 border-2 border-gray-300">
                    <h3 class="mb-4 text-xl font-bold text-gray-800">📊 Diagramma Architetturale</h3>
                    <pre class="p-4 bg-white rounded text-xs overflow-auto border border-gray-300"><code>┌─────────────────────────────────────────────────────────────────┐
│                    Users / Companies                            │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────────┐
│              Users / Organizations / PA                      │
└───────────────┬──────────────────────────────────────────────┘
                │
┌──────────────────────────────────────────────────────────────┐
│     FlorenceEGI Core (SaaS Hub Centrale)                     │
│ Governance · Auth · Billing · ULM/UEM · Audit · Tenant Ops   │
└───────┬───────────────────────┬──────────────────────────────┘
        │                       │
        │                       ▼
        │        ┌────────────────────────────────────────┐
        │        │ Tenant: Natan (AI, notarizzazione, RAG) │
        │        │ → NATAN Market Engine                   │
        │        └────────────────────────────────────────┘
        │
        ▼
┌──────────────────────────────────────────────────────────────┐
│ Tenant: FlorenceArtEGI (Arte & Marketplace)                  │
└───────┬──────────────────────────────────────────────────────┘
        │
        ▼
┌──────────────────────────────────────────────────────────────┐
│ Collection Workspace                                         │
│ - Collaboratori (max 8 wallet utente)                        │
│ - Wallet di collection (max 4 escrow/tesoreria)              │
│ - Limite complessivo 12 slot (4 riservati al core → 16 tot.) │
└───────┬──────────────────────────────────────────────────────┘
        │
        ▼
┌──────────────────────────────────────────────────────────────┐
│ AMMk Engines coordinati dal Core                             │
│ NATAN Market | Asset | Distribution | Co-Creation | Compliance│
└───────┬──────────────┬──────────────┬────────────────────────┘
        │              │              │
        ▼              ▼              ▼
┌────────────┐   ┌────────────┐   ┌──────────────────────────┐
│Marketplace │   │ Event Bus   │   │ Algorand Layer          │
│ Pubblico   │   │ Trigger IA  │   │ ASA · Atomic Transfers  │
└────────────┘   └─────┬──────┘   └────────┬─────────────────┘
                        │                   │
                        ▼                   ▼
               NATAN Actions        Observability & Compliance
</code></pre>
                    <p class="mt-4 text-sm text-gray-600 italic">Per visualizzazione interattiva: integra Mermaid.js o esporta come SVG</p>
                </div>

                <div class="p-6 mb-6 rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-2xl font-bold text-emerald-700">Perché <a href="#glossary-algorand" class="glossary-link">Algorand</a>?</h3>
                    <p class="text-gray-700">Blockchain sostenibile e <a href="#glossary-carbon-negative" class="glossary-link">carbon-negative</a> basata su <a href="#glossary-proof-of-stake" class="glossary-link">Proof-of-Stake</a> pura.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Garanzie Tecniche</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li><strong>Immutabilità</strong> e autenticità di ogni opera</li>
                            <li><strong>Assenza di volatilità</strong> nei flussi economici</li>
                            <li><strong>Scalabilità</strong> e sicurezza superiori</li>
                            <li><strong>Trasparenza assoluta</strong> nei registri</li>
                        </ul>
                    </div>
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Collegamento Fisico-Digitale</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li><a href="#glossary-coa" class="glossary-link">CoA</a> verificato per ogni <a href="#glossary-egi" class="glossary-link">EGI</a></li>
                            <li><a href="#glossary-qr-nfc" class="glossary-link">QR/NFC</a> unidirezionali</li>
                            <li><a href="#glossary-hash" class="glossary-link">Hash crittografici</a> unici</li>
                            <li>Verifica pubblica immediata</li>
                        </ul>
                    </div>
                </div>
                <div class="p-6 mt-6 border-l-4 border-blue-500 rounded-r-lg bg-blue-50">
                    <h4 class="mb-2 font-bold text-blue-800">Principio Fondamentale</h4>
                    <p class="text-blue-700"><strong>FlorenceEGI certifica, non custodisce.</strong> Il merchant/creator resta proprietario del bene; la piattaforma garantisce solo la verità della proprietà e dell'autenticità.</p>
                </div>`
            },
            compliance: {
                title: 'Compliance e Sicurezza',
                nav: 'Compliance',
                parent: 'sistema',
                intro: 'Conformità totale a GDPR e MiCA attraverso architettura by-design.',
                content: `<div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-emerald-50">
                        <h3 class="mb-4 text-2xl font-bold text-emerald-700">🔒 <a href="#glossary-gdpr" class="glossary-link">GDPR</a>-by-design</h3>
                        <p class="mb-4 text-gray-700">Ogni azione utente è tracciata e documentata tramite:</p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li><a href="#glossary-ulm" class="glossary-link">UltraLogManager (ULM)</a> per registrazione eventi</li>
                            <li><a href="#glossary-auditlogservice" class="glossary-link">AuditLogService</a> per <a href="#glossary-audit-trail" class="glossary-link">audit trail</a> verificabili</li>
                            <li><a href="#glossary-consentservice" class="glossary-link">ConsentService</a> per gestione consensi</li>
                        </ul>
                        <p class="mt-4 font-semibold text-emerald-800">Risultato: Auditabilità completa, protezione dati, responsabilità verificabile</p>
                    </div>
                    <div class="p-6 rounded-lg bg-blue-50">
                        <h3 class="mb-4 text-2xl font-bold text-blue-700">🛡️ <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a></h3>
                        <p class="mb-4 text-gray-700">FlorenceEGI è progettata per conformità totale:</p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li><strong>Non gestisce</strong> fondi o crypto per conto terzi</li>
                            <li>Pagamenti tramite <a href="#glossary-psp" class="glossary-link">PSP autorizzati</a></li>
                            <li>Fondi fluiscono <strong>direttamente</strong> tra le parti</li>
                            <li>Piattaforma incassa solo propria fee (fatturata)</li>
                        </ul>
                        <p class="mt-4 font-semibold text-blue-800">Risultato: Piena legalità, fiscalità lineare, zero rischio normativo</p>
                    </div>
                </div>`
            },
            governance: {
                title: 'Governance Duale',
                nav: 'Governance',
                parent: 'sistema',
                intro: 'Equilibrio tra impresa e missione attraverso due entità complementari.',
                content: `<div class="mb-8">
                    <h3 class="mb-4 text-2xl font-bold text-center text-emerald-700">Struttura di <a href="#glossary-governance-duale" class="glossary-link">Governance Duale</a></h3>
                    <div class="chart-container"><canvas id="governanceChart"></canvas></div>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 border-2 rounded-lg border-emerald-500 bg-emerald-50">
                        <h4 class="mb-3 text-xl font-bold text-emerald-800">FlorenceEGI SRL</h4>
                        <p class="mb-3 text-gray-700"><strong>Motore Operativo e Commerciale</strong></p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Sviluppo tecnologico</li>
                            <li>Partnership strategiche</li>
                            <li>Marketing e revenue</li>
                            <li>Crescita scalabile</li>
                        </ul>
                    </div>
                    <div class="p-6 border-2 border-blue-500 rounded-lg bg-blue-50">
                        <h4 class="mb-3 text-xl font-bold text-blue-800">Associazione Frangette APS</h4>
                        <p class="mb-3 text-gray-700"><strong>Custode dei Valori e dell'Etica</strong></p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Vigila sui principi fondativi</li>
                            <li>Tutela destinazione 20% <a href="#glossary-epp" class="glossary-link">EPP</a></li>
                            <li>Garantisce coerenza artistico-sociale</li>
                            <li>Protegge la missione</li>
                        </ul>
                    </div>
                </div>
                <div class="p-6 mt-6 border-l-4 border-gray-400 rounded-r-lg bg-gray-50">
                    <p class="text-lg text-gray-700"><strong>Questo modello assicura equilibrio tra impresa e missione, tra profitto e scopo.</strong></p>
                </div>`
            },
            'diritti-legali': {
                title: 'Diritti d\'Autore & Diritto di Seguito',
                nav: 'Diritti Legali',
                parent: 'sistema',
                intro: 'Normativa italiana ed europea: cosa spetta al Creator, cosa acquisisce l\'Owner.',
                content: `<div class="mb-8 p-6 rounded-lg bg-amber-50 border-2 border-amber-300">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-2xl text-amber-600 mt-1"></i>
                        <div>
                            <h4 class="text-lg font-bold text-amber-900 mb-2">Premessa Importante</h4>
                            <p class="text-gray-700">Le informazioni seguenti sono fornite a scopo <strong>informativo e divulgativo</strong>. Non costituiscono consulenza legale. Per questioni specifiche, consultare un avvocato specializzato in diritto d'autore.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="mb-6 text-2xl font-bold text-center text-emerald-700">🎨 Diritti del Creator (Sempre e Comunque)</h3>

                    <div class="grid gap-6 md:grid-cols-2 mb-8">
                        <div class="p-6 border-l-4 border-emerald-600 rounded-r-lg bg-emerald-50">
                            <h4 class="text-xl font-bold text-emerald-800 mb-3">
                                <i class="fas fa-signature mr-2"></i>Diritti Morali (Inalienabili)
                            </h4>
                            <p class="text-sm text-gray-600 mb-3"><strong>Legge 633/1941 Art. 20 - Mai cedibili, anche dopo la vendita</strong></p>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-emerald-600 mr-2 mt-1"></i>
                                    <span><strong>Paternità</strong>: Diritto di essere sempre riconosciuto come autore dell'opera</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-emerald-600 mr-2 mt-1"></i>
                                    <span><strong>Integrità</strong>: Diritto di opporsi a modifiche, deformazioni o alterazioni che danneggino la reputazione</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-emerald-600 mr-2 mt-1"></i>
                                    <span><strong>Attribuzione</strong>: L'Owner deve sempre citare correttamente l'artista</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-ban text-red-600 mr-2 mt-1"></i>
                                    <span class="text-red-700"><strong>L'Owner NON può</strong>: rimuovere firma, alterare l'opera, attribuirla ad altri</span>
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 border-l-4 border-blue-600 rounded-r-lg bg-blue-50">
                            <h4 class="text-xl font-bold text-blue-800 mb-3">
                                <i class="fas fa-copyright mr-2"></i>Diritti Patrimoniali (Copyright)
                            </h4>
                            <p class="text-sm text-gray-600 mb-3"><strong>Legge 633/1941 Art. 12-19 - Sfruttamento economico</strong></p>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                                    <span><strong>Riproduzione</strong>: Solo il Creator può fare copie/stampe dell'opera</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                                    <span><strong>Comunicazione pubblica</strong>: Uso in pubblicità/TV/online richiede licenza Creator</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                                    <span><strong>Distribuzione</strong>: Vendere copie/merchandise richiede autorizzazione</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-amber-600 mr-2 mt-1"></i>
                                    <span class="text-amber-700"><strong>IMPORTANTE</strong>: Comprare NFT ≠ Comprare copyright</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="p-6 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-300">
                        <h4 class="text-xl font-bold text-purple-800 mb-4 text-center">
                            <i class="fas fa-balance-scale mr-2"></i>Diritto di Seguito vs Royalty Piattaforma
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-purple-100">
                                        <th class="p-3 text-left font-bold text-purple-900">Aspetto</th>
                                        <th class="p-3 text-left font-bold text-purple-900">Royalty Piattaforma (FlorenceEGI)</th>
                                        <th class="p-3 text-left font-bold text-purple-900">Diritto di Seguito (Legge)</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    <tr class="border-b">
                                        <td class="p-3 font-semibold">Base giuridica</td>
                                        <td class="p-3">Contratto smart contract</td>
                                        <td class="p-3">L. 633/1941 Art. 19bis</td>
                                    </tr>
                                    <tr class="border-b bg-gray-50">
                                        <td class="p-3 font-semibold">Soglia minima</td>
                                        <td class="p-3"><span class="text-emerald-700 font-bold">€0</span> (tutte le vendite)</td>
                                        <td class="p-3"><span class="text-blue-700 font-bold">€3,000</span></td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-3 font-semibold">Percentuale</td>
                                        <td class="p-3"><span class="text-emerald-700 font-bold">4.5%</span> fisso</td>
                                        <td class="p-3"><span class="text-blue-700 font-bold">4% → 0.25%</span> (decrescente)</td>
                                    </tr>
                                    <tr class="border-b bg-gray-50">
                                        <td class="p-3 font-semibold">Tipo vendite</td>
                                        <td class="p-3">P2P dirette (piattaforma)</td>
                                        <td class="p-3">Tramite professionisti (gallerie/aste)</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-3 font-semibold">Chi gestisce</td>
                                        <td class="p-3">Smart contract automatico</td>
                                        <td class="p-3">SIAE (manuale)</td>
                                    </tr>
                                    <tr class="border-b bg-gray-50">
                                        <td class="p-3 font-semibold">Cumulabile</td>
                                        <td class="p-3 text-center" colspan="2"><span class="text-green-700 font-bold">✅ SÌ!</span> Il Creator può ricevere ENTRAMBI</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 p-4 bg-white rounded-lg">
                            <p class="text-gray-700"><i class="fas fa-lightbulb text-yellow-500 mr-2"></i><strong>Esempio</strong>: Vendita €50,000 tramite galleria → Creator riceve <span class="text-emerald-700 font-bold">€2,250 (4.5% piattaforma)</span> + <span class="text-blue-700 font-bold">€2,000 (4% diritto seguito)</span> = <span class="text-purple-700 font-bold">€4,250 totali (8.5%)</span></p>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="mb-6 text-2xl font-bold text-center text-blue-700">🏠 Diritti dell'Owner (Acquirente)</h3>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="p-6 border-l-4 border-green-600 rounded-r-lg bg-green-50">
                            <h4 class="text-lg font-bold text-green-800 mb-3">
                                <i class="fas fa-check mr-2"></i>Cosa PUÒ Fare l'Owner
                            </h4>
                            <ul class="space-y-2 text-gray-700 list-disc list-inside">
                                <li>Possedere fisicamente l'opera</li>
                                <li>Esporre privatamente (casa/ufficio)</li>
                                <li>Rivendere l'opera (con royalty Creator)</li>
                                <li>Donare o lasciare in eredità</li>
                                <li>Fotografare per documentazione personale</li>
                                <li>Esporre pubblicamente senza scopo di lucro (con attribuzione Creator)</li>
                                <li>Restauro conservativo (senza alterare)</li>
                            </ul>
                        </div>

                        <div class="p-6 border-l-4 border-red-600 rounded-r-lg bg-red-50">
                            <h4 class="text-lg font-bold text-red-800 mb-3">
                                <i class="fas fa-ban mr-2"></i>Cosa NON PUÒ Fare (Senza Consenso Creator)
                            </h4>
                            <ul class="space-y-2 text-gray-700 list-disc list-inside">
                                <li><strong>Riprodurre commercialmente</strong> (stampe, poster, merchandise)</li>
                                <li><strong>Modificare/alterare</strong> l'opera originale</li>
                                <li><strong>Usare in pubblicità/marketing</strong> senza licenza</li>
                                <li><strong>Pubblicare online</strong> per scopi commerciali</li>
                                <li><strong>Creare opere derivative</strong> (remix, versioni)</li>
                                <li><strong>Rimuovere firma/crediti</strong> dell'artista</li>
                                <li><strong>Emettere NFT aggiuntivi</strong> della stessa opera</li>
                            </ul>
                            <p class="mt-3 text-sm text-red-800"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Violazione = Art. 171 LDA</strong>: Multe fino €15,493 + sequestro + risarcimento danni</p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="mb-6 text-2xl font-bold text-center text-amber-700">⚖️ Normativa di Riferimento</h3>

                    <div class="space-y-4">
                        <div class="p-5 rounded-lg bg-white border-2 border-gray-200">
                            <h4 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-book-open text-emerald-600 mr-2"></i>Legge 633/1941 (Legge sul Diritto d'Autore - LDA)
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700 ml-6 list-disc">
                                <li><strong>Art. 12-19</strong>: Diritti patrimoniali (riproduzione, comunicazione, distribuzione)</li>
                                <li><strong>Art. 20</strong>: Diritti morali (paternità, integrità dell'opera)</li>
                                <li><strong>Art. 19bis</strong>: Diritto di seguito sulle rivendite</li>
                                <li><strong>Art. 25</strong>: Durata protezione (vita autore + 70 anni)</li>
                                <li><strong>Art. 171</strong>: Sanzioni per violazioni (multa €51-€15,493)</li>
                            </ul>
                        </div>

                        <div class="p-5 rounded-lg bg-white border-2 border-gray-200">
                            <h4 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-globe-europe text-blue-600 mr-2"></i>D.Lgs. 118/2006 (Recepimento Direttiva UE 2001/84/CE)
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700 ml-6 list-disc">
                                <li><strong>Art. 3</strong>: Aliquote diritto di seguito (4% fino €50k, poi decrescente)</li>
                                <li><strong>Art. 4</strong>: Soglia minima €3,000 per applicazione</li>
                                <li><strong>Art. 5</strong>: Massimo €12,500 per singola vendita</li>
                                <li><strong>Art. 8</strong>: Gestione tramite SIAE (Società Italiana Autori ed Editori)</li>
                            </ul>
                        </div>

                        <div class="p-5 rounded-lg bg-white border-2 border-gray-200">
                            <h4 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-scale-balanced text-purple-600 mr-2"></i>Codice Civile - Art. 2575-2583
                            </h4>
                            <p class="text-sm text-gray-700 ml-6">Distinzione tra <strong>proprietà dell'oggetto fisico</strong> (Owner) e <strong>diritti sull'opera dell'ingegno</strong> (Creator). L'acquisto di un'opera d'arte trasferisce solo il possesso materiale, non il copyright.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="mb-6 text-2xl font-bold text-center text-indigo-700">💰 Come Funziona su FlorenceEGI</h3>

                    <div class="space-y-6">
                        <div class="p-6 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-emerald-300">
                            <h4 class="text-lg font-bold text-emerald-800 mb-3">
                                <i class="fas fa-shopping-cart mr-2"></i>Vendita Primaria (Mint) - EGI €1,000
                            </h4>
                            <div class="grid gap-3 md:grid-cols-2 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-800 mb-2">Distribuzione ricavi:</p>
                                    <ul class="space-y-1 text-gray-700">
                                        <li>💚 Creator: €650-680 (65-68%)</li>
                                        <li>🌱 EPP: €200 (20%)</li>
                                        <li>⚙️ Piattaforma: €100 (10%)</li>
                                        <li>🏛️ Associazione: €20 (2%)</li>
                                    </ul>
                                </div>
                                <div class="p-3 bg-white rounded-lg">
                                    <p class="font-semibold text-red-700 mb-1"><i class="fas fa-times-circle mr-1"></i>Diritto di seguito NON applicabile</p>
                                    <p class="text-xs text-gray-600">È la prima vendita, non una rivendita</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300">
                            <h4 class="text-lg font-bold text-blue-800 mb-3">
                                <i class="fas fa-sync-alt mr-2"></i>Rivendita Secondaria - EGI €1,000 (P2P su FlorenceEGI)
                            </h4>
                            <div class="grid gap-3 md:grid-cols-2 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-800 mb-2">Distribuzione:</p>
                                    <ul class="space-y-1 text-gray-700">
                                        <li>💼 Seller riceve: €930 (93%)</li>
                                        <li>🎨 Creator royalty: €45 (4.5%)</li>
                                        <li>🌱 EPP: €10 (1%)</li>
                                        <li>⚙️ Piattaforma: €10 (1%)</li>
                                        <li>🏛️ Associazione: €5 (0.5%)</li>
                                    </ul>
                                </div>
                                <div class="p-3 bg-white rounded-lg">
                                    <p class="font-semibold text-red-700 mb-1"><i class="fas fa-times-circle mr-1"></i>Diritto seguito legale NON applicabile</p>
                                    <p class="text-xs text-gray-600">Sotto soglia €3,000</p>
                                    <p class="text-xs text-emerald-600 mt-2"><i class="fas fa-check mr-1"></i>Ma Creator riceve comunque 4.5% (nostro contratto)</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 rounded-lg bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-300">
                            <h4 class="text-lg font-bold text-purple-800 mb-3">
                                <i class="fas fa-gavel mr-2"></i>Rivendita Secondaria - EGI €50,000 (tramite Galleria/Asta)
                            </h4>
                            <div class="grid gap-3 md:grid-cols-2 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-800 mb-2">Fee FlorenceEGI:</p>
                                    <ul class="space-y-1 text-gray-700">
                                        <li>💼 Seller: €46,500 (93%)</li>
                                        <li>🎨 Creator: €2,250 (4.5%)</li>
                                        <li>🌱 EPP: €500 (1%)</li>
                                        <li>⚙️ Platform: €500 (1%)</li>
                                        <li>🏛️ Assoc: €250 (0.5%)</li>
                                    </ul>
                                </div>
                                <div class="p-3 bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg border-2 border-yellow-400">
                                    <p class="font-semibold text-amber-800 mb-1"><i class="fas fa-plus-circle mr-1"></i>Diritto seguito legale APPLICABILE</p>
                                    <ul class="space-y-1 text-xs text-gray-700">
                                        <li>📊 Aliquota: 4% (fascia 0-€50k)</li>
                                        <li>💰 Importo: <span class="font-bold text-blue-700">€2,000</span></li>
                                        <li>👤 Ricevuto: Creator (via SIAE)</li>
                                        <li>📋 Separato dalle fee piattaforma</li>
                                    </ul>
                                    <p class="mt-3 font-bold text-purple-700 text-center border-t border-yellow-300 pt-2">
                                        TOTALE Creator: €4,250 (8.5%)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg bg-gray-100 border-2 border-gray-300">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 text-center">
                        <i class="fas fa-file-contract mr-2"></i>Cosa Include il Contratto di Vendita EGI
                    </h4>
                    <div class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <p class="font-semibold text-emerald-700 mb-2">✅ L'Owner ACQUISISCE:</p>
                            <ul class="space-y-1 text-gray-700 ml-4 list-disc">
                                <li>Proprietà fisica dell'opera (oggetto materiale)</li>
                                <li>NFT digitale (certificato blockchain)</li>
                                <li>Diritto di godimento privato</li>
                                <li>Diritto di rivendita (con royalty Creator)</li>
                                <li>Possesso esclusivo dell'originale</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-red-700 mb-2">❌ Il Creator CONSERVA:</p>
                            <ul class="space-y-1 text-gray-700 ml-4 list-disc">
                                <li>Tutti i diritti morali (paternità, integrità)</li>
                                <li>Diritto di seguito (4%-0.25% su rivendite >€3k)</li>
                                <li>Royalty piattaforma (4.5% sempre)</li>
                                <li>Diritti di riproduzione (stampe, copie)</li>
                                <li>Copyright sull'immagine dell'opera</li>
                                <li>Diritti digitali (uso online commerciale)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-6 rounded-lg bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300">
                    <h4 class="text-lg font-bold text-red-800 mb-3 text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Disclaimer Legale
                    </h4>
                    <p class="text-sm text-gray-700 text-center">Le informazioni sopra riportate sono fornite <strong>a scopo informativo generale</strong> e non costituiscono consulenza legale professionale. La normativa sul diritto d'autore è complessa e soggetta a interpretazioni. Per questioni legali specifiche, si raccomanda di consultare un avvocato specializzato in proprietà intellettuale e diritto dell'arte. FlorenceEGI non assume responsabilità per decisioni prese sulla base di queste informazioni.</p>
                </div>

                <div class="p-6 rounded-lg bg-emerald-50 border-2 border-emerald-300">
                    <h4 class="text-lg font-bold text-emerald-800 mb-3 text-center">
                        <i class="fas fa-balance-scale mr-2"></i>Impegno FlorenceEGI
                    </h4>
                    <p class="text-gray-700 text-center mb-4">FlorenceEGI si impegna a <strong>rispettare e tutelare</strong> i diritti degli artisti previsti dalla legge italiana ed europea:</p>
                    <ul class="space-y-2 text-gray-700 max-w-3xl mx-auto">
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-emerald-600 mr-3 mt-1"></i>
                            <span>Garantiamo attribuzione corretta in tutti gli EGI (paternità)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-emerald-600 mr-3 mt-1"></i>
                            <span>Blocchiamo modifiche post-mint (integrità blockchain)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-emerald-600 mr-3 mt-1"></i>
                            <span>Royalty automatiche 4.5% su tutte le rivendite (anche sotto €3k)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-emerald-600 mr-3 mt-1"></i>
                            <span>Collaboriamo con SIAE per gestione diritto di seguito su vendite >€3k tramite professionisti</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-emerald-600 mr-3 mt-1"></i>
                            <span>Smart contract impedisce elusione royalty (trustless enforcement)</span>
                        </li>
                    </ul>
                </div>`
            },
            economia: {
                title: 'Economia del Valore',
                nav: 'Economia',
                parent: 'economia',
                intro: 'Un\'economia rigenerativa basata su equilibrio, automazione e reciprocità.',
                content: `<div class="mb-8">
                    <h3 class="mb-4 text-2xl font-bold text-center text-emerald-700">Le Tre Linee di Valore</h3>
                    <div class="chart-container"><canvas id="economyChart"></canvas></div>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="p-6 text-center rounded-lg bg-emerald-50">
                        <div class="mb-3 text-4xl">💰</div>
                        <h4 class="mb-2 text-lg font-bold text-emerald-800">Economico</h4>
                        <p class="text-sm text-gray-700"><a href="#glossary-fee-dinamiche" class="glossary-link">Fee</a> e royalties automatiche</p>
                    </div>
                    <div class="p-6 text-center rounded-lg bg-blue-50">
                        <div class="mb-3 text-4xl">⭐</div>
                        <h4 class="mb-2 text-lg font-bold text-blue-800">Reputazionale</h4>
                        <p class="text-sm text-gray-700">Tracciabilità pubblica e riconoscimento</p>
                    </div>
                    <div class="p-6 text-center rounded-lg bg-green-50">
                        <div class="mb-3 text-4xl">🌱</div>
                        <h4 class="mb-2 text-lg font-bold text-green-800">Ambientale</h4>
                        <p class="text-sm text-gray-700">Donazioni <a href="#glossary-epp" class="glossary-link">EPP</a> automatiche e certificate</p>
                    </div>
                </div>
                <div class="p-6 mt-8 rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-xl font-bold text-emerald-700"><a href="#glossary-fee-dinamiche" class="glossary-link">Fee Dinamiche</a></h3>
                    <p class="mb-3 text-gray-700">Le commissioni si riducono al crescere della community:</p>
                    <p class="text-lg font-semibold text-emerald-800">Più utenti partecipano → Meno ciascuno paga</p>
                </div>
                <div class="p-6 mt-6 rounded-lg bg-blue-50">
                    <h3 class="mb-3 text-xl font-bold text-blue-700">Token Ecosystem</h3>
                    <ul class="space-y-2 text-gray-700 list-disc list-inside">
                        <li><a href="#glossary-equilibrium" class="glossary-link">Equilibrium</a>: token per premi e ranking</li>
                        <li><a href="#glossary-egili" class="glossary-link">Egili</a>: micro-unità per interazioni</li>
                        <li>Economia meritocratica, non speculativa</li>
                    </ul>
                </div>`
            },
            mecenatismo: {
                title: 'Mecenatismo e Nuove Professioni',
                nav: 'Mecenatismo',
                parent: 'economia',
                intro: 'Il ritorno del mecenate e del curatore in chiave digitale con carriere verificabili.',
                content: `<div class="p-6 mb-6 rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-2xl font-bold text-emerald-700">Il Ruolo del <a href="#glossary-mecenate" class="glossary-link">Mecenate</a> Digitale</h3>
                    <p class="text-gray-700">Chi sostiene un artista o collezione diventa <strong>attivatore di valore culturale</strong>. FlorenceEGI permette di costruire una vera carriera professionale.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Profilo Pubblico Verificato</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Storico completo delle co-creazioni</li>
                            <li>Portfolio opere sostenute</li>
                            <li>Ranking basato su impatto reale</li>
                            <li>Reputazione tracciata on-chain</li>
                        </ul>
                    </div>
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800"><a href="#glossary-curatore" class="glossary-link">Curatore</a> Digitale</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Selezione e valorizzazione opere</li>
                            <li>Organizzazione collezioni tematiche</li>
                            <li>Advisory per collector</li>
                            <li>Carriera professionale verificabile</li>
                        </ul>
                    </div>
                </div>
                <blockquote class="p-6 mt-6 border-l-4 border-blue-500 rounded-r-lg bg-blue-50">
                    <p class="italic text-gray-700">"In FlorenceEGI, il riconoscimento non è casuale: è <strong>meritato e tracciato</strong>. La visibilità diventa una forma di giustizia."</p>
                </blockquote>`
            },
            impatto: {
                title: 'Impatto e Sostenibilità',
                nav: 'Impatto ESG',
                parent: 'economia',
                intro: 'Ogni atto economico diventa atto rigenerativo attraverso gli EPP integrati.',
                content: `<div class="p-6 mb-6 text-center rounded-lg bg-green-50">
                    <h3 class="mb-3 text-2xl font-bold text-green-700"><a href="#glossary-epp" class="glossary-link">EPP</a> - Environmental Protection Projects</h3>
                    <p class="text-lg text-gray-700">Progetti ambientali verificati integrati nativamente nel sistema</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Meccanismo Automatico</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Ogni vendita destina quota a <a href="#glossary-epp" class="glossary-link">EPP</a></li>
                            <li>Trasferimento automatico on-chain</li>
                            <li>Tracciabilità completa e verificabile</li>
                            <li>Zero intermediazione umana</li>
                        </ul>
                    </div>
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Progetti Verificati</h4>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Riforestazione e habitat restoration</li>
                            <li>Rimozione plastica dagli oceani</li>
                            <li>Protezione biodiversità</li>
                            <li>Certificazione impatto reale</li>
                        </ul>
                    </div>
                </div>
                <div class="p-6 mt-6 border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="text-xl font-bold text-emerald-800">Non è un'opzione etica: è una legge di equilibrio.</p>
                    <p class="mt-2 text-gray-700">Ogni atto economico genera un atto rigenerativo. L'arte non rappresenta solo la vita: su FlorenceEGI <strong>la sostiene</strong>.</p>
                </div>`
            },
            oracode: {
                title: 'Oracode System',
                nav: 'Oracode',
                parent: 'intelligenza',
                intro: 'Il paradigma filosofico-tecnico che fonde ingegneria e simbolismo.',
                content: `<div class="p-6 mb-6 rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-2xl font-bold text-emerald-700"><a href="#glossary-oracode" class="glossary-link">Oracode System</a></h3>
                    <p class="text-lg text-gray-700">Architettura cognitiva che fonde <strong>ingegneria e simbolismo, logica e coscienza</strong>.</p>
                </div>
                <div class="space-y-6">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-gray-800">Principi Fondamentali</h4>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 text-xl">📚</span>
                                <span><strong>Documentazione Totale:</strong> Tutto è semanticamente leggibile e testabile</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 text-xl">🎯</span>
                                <span><strong>Regola Zero:</strong> Mai dedurre in assenza di dati</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 text-xl">🔍</span>
                                <span><strong>Trasparenza Etica:</strong> Ogni decisione è interrogabile</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 text-xl">⚙️</span>
                                <span><strong>Funzionalità Verificabile:</strong> Ogni processo è tracciato</span>
                            </li>
                        </ul>
                    </div>
                    <blockquote class="p-6 border-l-4 border-blue-500 rounded-r-lg bg-blue-50">
                        <p class="text-lg italic text-gray-700">"<a href="#glossary-oracode" class="glossary-link">Oracode</a> è la grammatica della verità tecnologica di FlorenceEGI. È ciò che trasforma un software in un <strong>organismo di senso</strong>."</p>
                    </blockquote>
                </div>`
            },
            natan: {
                title: 'N.A.T.A.N. - Intelligenza Artificiale',
                nav: 'N.A.T.A.N.',
                parent: 'intelligenza',
                intro: 'Neural Assistant for Technical Art Navigation: IA etica che espande gli artisti.',
                content: `<blockquote class="p-6 mb-6 text-center border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="text-xl italic text-gray-700">"Ogni Rinascimento nasce da un dialogo fra mente umana e mente artificiale."</p>
                </blockquote>
                <div class="p-6 mb-6 rounded-lg bg-blue-50">
                    <h3 class="mb-3 text-2xl font-bold text-blue-700"><a href="#glossary-natan" class="glossary-link">N.A.T.A.N.</a> - Neural Assistant for Technical Art Navigation</h3>
                    <p class="text-gray-700">Intelligenza artificiale etica integrata che agisce come <strong>consulente personale</strong> per Creator e Collector.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-emerald-800">🎨 Per i Creator</h4>
                        <p class="mb-3 text-sm text-gray-600">Servizio opzionale e premium</p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Analizza collezioni, <a href="#glossary-egi" class="glossary-link">EGI</a> e traits estetici</li>
                            <li>Suggerisce descrizioni <a href="#glossary-seo-oriented" class="glossary-link">SEO-oriented</a></li>
                            <li>Strategie marketing e partnership</li>
                            <li>Piani editoriali e storytelling</li>
                            <li>Può diventare <strong>agente autonomo</strong></li>
                        </ul>
                        <p class="mt-4 font-semibold text-emerald-700">Prima IA che non sostituisce l'artista, ma lo espande</p>
                    </div>
                    <div class="p-6 rounded-lg bg-gray-50">
                        <h4 class="mb-3 text-lg font-semibold text-blue-800">💎 Per i Collector</h4>
                        <p class="mb-3 text-sm text-gray-600">Curatore digitale personale</p>
                        <ul class="space-y-2 text-gray-700 list-disc list-inside">
                            <li>Apprende dai dati artistici personali</li>
                            <li>Suggerisce opere coerenti col gusto</li>
                            <li>Identifica artisti emergenti</li>
                            <li>Momenti ideali per scambio</li>
                            <li>Sempre <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a> (no profitto speculativo)</li>
                        </ul>
                        <p class="mt-4 font-semibold text-blue-700">Curatore che rispetta e amplifica il tuo gusto</p>
                    </div>
                </div>
                <div class="p-6 mt-6 rounded-lg bg-emerald-50">
                    <h4 class="mb-3 text-lg font-bold text-emerald-700">🧠 Etica e Trasparenza</h4>
                    <p class="mb-3 text-gray-700">Conforme a <a href="#glossary-oracode" class="glossary-link">Oracode OS3</a>, integrato con <a href="#glossary-ulm" class="glossary-link">ULM</a> e <a href="#glossary-consentservice" class="glossary-link">ConsentService</a>:</p>
                    <ul class="space-y-2 text-gray-700 list-disc list-inside">
                        <li>Ogni interazione è tracciata</li>
                        <li>Ogni suggerimento è auditabile</li>
                        <li>Ogni uso dati è autorizzato e reversibile</li>
                    </ul>
                    <p class="mt-4 text-lg font-semibold text-emerald-800">In FlorenceEGI, anche l'IA diventa arte partecipata</p>
                </div>`
            },
            unicum: {
                title: 'L\'Unicum',
                nav: 'L\'Unicum',
                parent: 'visione',
                intro: 'L\'unico sistema al mondo che unisce tutti i paradigmi in un\'architettura coerente.',
                content: `<div class="p-6 mb-6 text-center rounded-lg bg-emerald-50">
                    <h3 class="mb-3 text-3xl font-bold text-emerald-700">FlorenceEGI è Unico</h3>
                    <p class="text-lg text-gray-700">L'unico sistema che unisce <strong>tutti</strong> questi paradigmi</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">⛓️</div>
                        <p class="font-semibold text-emerald-800">Certificazione <a href="#glossary-blockchain" class="glossary-link">Blockchain</a></p>
                        <p class="text-sm text-gray-600">Beni fisici e digitali</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">✨</div>
                        <p class="font-semibold text-emerald-800"><a href="#glossary-co-creatore" class="glossary-link">Co-Creazione</a> Permanente</p>
                        <p class="text-sm text-gray-600">Visibilità perpetua</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">🔒</div>
                        <p class="font-semibold text-emerald-800">Compliance Totale</p>
                        <p class="text-sm text-gray-600"><a href="#glossary-gdpr" class="glossary-link">GDPR</a> + <a href="#glossary-mica" class="glossary-link">MiCA</a></p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">⚖️</div>
                        <p class="font-semibold text-emerald-800"><a href="#glossary-governance-duale" class="glossary-link">Governance Duale</a></p>
                        <p class="text-sm text-gray-600">Equilibrio valori-impresa</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">💰</div>
                        <p class="font-semibold text-emerald-800"><a href="#glossary-fee-dinamiche" class="glossary-link">Fee Dinamiche</a></p>
                        <p class="text-sm text-gray-600">Economia rigenerativa</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">🌱</div>
                        <p class="font-semibold text-emerald-800">Integrazione <a href="#glossary-epp" class="glossary-link">EPP</a></p>
                        <p class="text-sm text-gray-600">Impatto ambientale nativo</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">💎</div>
                        <p class="font-semibold text-emerald-800">Mecenatismo Pro</p>
                        <p class="text-sm text-gray-600">Carriere verificabili</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">🤖</div>
                        <p class="font-semibold text-emerald-800"><a href="#glossary-natan" class="glossary-link">N.A.T.A.N.</a> AI</p>
                        <p class="text-sm text-gray-600">Intelligenza etica integrata</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-emerald-50">
                        <div class="mb-2 text-3xl">📚</div>
                        <p class="font-semibold text-emerald-800"><a href="#glossary-oracode" class="glossary-link">Oracode System</a></p>
                        <p class="text-sm text-gray-600">Paradigma filosofico-tecnico</p>
                    </div>
                </div>
                <div class="p-6 mt-8 text-center border-4 rounded-lg border-emerald-500 bg-emerald-50">
                    <p class="text-2xl font-bold text-emerald-800">L'unione di tutti i paradigmi mancanti in un'unica architettura coerente e verificabile</p>
                </div>`
            },
            visione: {
                title: 'Visione - Il Rinascimento Digitale',
                nav: 'Visione',
                parent: 'visione',
                intro: 'FlorenceEGI è il Rinascimento Digitale: bellezza come valore misurabile, fiducia come infrastruttura.',
                content: `<div class="p-8 mb-8 text-center rounded-lg bg-gradient-to-br from-emerald-50 to-blue-50">
                    <h3 class="mb-4 text-3xl font-bold text-emerald-800">🎨 Il Rinascimento Digitale</h3>
                    <p class="text-xl text-gray-700">Un ritorno alla <strong>bellezza come valore misurabile</strong>, alla <strong>fiducia come infrastruttura</strong>, alla <strong>partecipazione come forma d'arte</strong>.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="p-6 text-center rounded-lg bg-emerald-50">
                        <div class="mb-3 text-4xl">🏛️</div>
                        <h4 class="mb-2 text-lg font-bold text-emerald-800">Memoria</h4>
                        <p class="text-gray-700">Traccia permanente su <a href="#glossary-blockchain" class="glossary-link">blockchain</a> di ogni contributo culturale</p>
                    </div>
                    <div class="p-6 text-center rounded-lg bg-blue-50">
                        <div class="mb-3 text-4xl">⚖️</div>
                        <h4 class="mb-2 text-lg font-bold text-blue-800">Equilibrio</h4>
                        <p class="text-gray-700"><a href="#glossary-governance-duale" class="glossary-link">Governance</a> che bilancia profitto e missione</p>
                    </div>
                    <div class="p-6 text-center rounded-lg bg-green-50">
                        <div class="mb-3 text-4xl">🌍</div>
                        <h4 class="mb-2 text-lg font-bold text-green-800">Impatto Reale</h4>
                        <p class="text-gray-700">Rigenerazione ambientale tramite <a href="#glossary-epp" class="glossary-link">EPP</a> integrati</p>
                    </div>
                </div>
                <blockquote class="p-8 mt-8 text-center border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500">
                    <p class="text-2xl italic font-bold text-emerald-800">"In un mondo che consuma attenzione e brucia significato, noi costruiamo memoria, equilibrio e impatto reale."</p>
                </blockquote>
                <div class="p-8 mt-8 text-center rounded-lg bg-gradient-to-r from-emerald-500 to-blue-500">
                    <p class="text-2xl font-bold text-white">FlorenceEGI: il sistema che certifica la verità del valore.</p>
                    <p class="mt-4 text-lg text-white">Dove chi crea, chi sostiene e chi colleziona diventano un'unica, eterna opera.</p>
                </div>`
            }
        };

        const menuStructure = {
            fondamenti: {
                title: 'Fondamenti',
                icon: '🏛️',
                sections: ['premessa', 'principio', 'cocreazione']
            },
            sistema: {
                title: 'Sistema',
                icon: '⚙️',
                sections: ['architettura', 'compliance', 'governance', 'diritti-legali']
            },
            economia: {
                title: 'Economia',
                icon: '💰',
                sections: ['economia', 'mecenatismo', 'impatto']
            },
            intelligenza: {
                title: 'Intelligenza',
                icon: '🧠',
                sections: ['oracode', 'natan']
            },
            visione: {
                title: 'Visione',
                icon: '🌟',
                sections: ['unicum', 'visione']
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const mainNav = document.getElementById('main-nav');
            const contentContainer = document.getElementById('content-container');
            let chartInstances = {};

            // Create Home Button
            const homeButton = document.createElement('a');
            homeButton.href = '/';
            homeButton.className =
                'inline-flex items-center gap-2 px-5 py-3 font-medium text-white transition-all duration-200 ease-in-out transform bg-emerald-600 rounded-xl hover:bg-emerald-700 hover:scale-105 hover:shadow-lg nav-item';
            homeButton.innerHTML = '<span class="text-xl material-icons">home</span><span>Home</span>';
            mainNav.appendChild(homeButton);

            // Create Menu with Submenus
            Object.keys(menuStructure).forEach(menuKey => {
                const menu = menuStructure[menuKey];
                const menuParent = document.createElement('div');
                menuParent.className = 'menu-parent';

                const menuButton = document.createElement('button');
                menuButton.className =
                    'nav-item px-4 py-2 sm:px-5 sm:py-2.5 text-sm sm:text-base font-semibold text-gray-700 bg-gray-100 rounded-full hover:bg-emerald-600 hover:text-white hover:shadow-md';
                menuButton.innerHTML =
                    `<span class="submenu-trigger">${menu.icon} ${menu.title} <span class="text-sm material-icons">expand_more</span></span>`;

                const submenu = document.createElement('div');
                submenu.className = 'submenu';

                menu.sections.forEach(sectionKey => {
                    const section = contentData[sectionKey];
                    const submenuItem = document.createElement('div');
                    submenuItem.className = 'submenu-item';
                    submenuItem.textContent = section.nav;
                    submenuItem.onclick = () => {
                        showSection(sectionKey);
                        submenu.classList.remove('active');
                    };
                    submenu.appendChild(submenuItem);
                });

                menuButton.onclick = (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.submenu').forEach(s => {
                        if (s !== submenu) s.classList.remove('active');
                    });
                    submenu.classList.toggle('active');
                };

                menuParent.appendChild(menuButton);
                menuParent.appendChild(submenu);
                mainNav.appendChild(menuParent);
            });

            // Close submenus on outside click
            document.addEventListener('click', () => {
                document.querySelectorAll('.submenu').forEach(s => s.classList.remove('active'));
            });

            // Create Content Sections
            Object.keys(contentData).forEach(key => {
                const sectionData = contentData[key];
                const section = document.createElement('section');
                section.id = `section-${key}`;
                section.className = 'content-section';
                section.innerHTML = `
                    <div class="p-6 bg-white shadow-lg sm:p-8 rounded-2xl">
                        <h2 class="mb-2 text-3xl font-bold text-gray-800">${sectionData.title}</h2>
                        <p class="mb-6 text-gray-600">${sectionData.intro}</p>
                        <div class="pt-6 border-t border-gray-200">${sectionData.content}</div>
                    </div>`;
                contentContainer.appendChild(section);
            });

            const showSection = (sectionId) => {
                document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
                document.getElementById(`section-${sectionId}`).classList.add('active');
                window.scrollTo(0, 0);

                // Render charts if needed
                if (sectionId === 'cocreazione') renderCoCreationChart();
                if (sectionId === 'governance') renderGovernanceChart();
                if (sectionId === 'economia') renderEconomyChart();
            };

            const renderCoCreationChart = () => {
                const ctx = document.getElementById('coCreationChart');
                if (!ctx) return;
                if (chartInstances.coCreation) chartInstances.coCreation.destroy();
                chartInstances.coCreation = new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Artista (Sorgente)', 'Co-Creatore (Causa Efficiente)',
                            'Collector (Custode)'
                        ],
                        datasets: [{
                            data: [33, 34, 33],
                            backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6'],
                            borderColor: '#fdfcfb',
                            borderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            };

            const renderGovernanceChart = () => {
                const ctx = document.getElementById('governanceChart');
                if (!ctx) return;
                if (chartInstances.governance) chartInstances.governance.destroy();
                chartInstances.governance = new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['FlorenceEGI SRL', 'Associazione Frangette'],
                        datasets: [{
                            label: 'Ambiti di Azione',
                            data: [50, 50],
                            backgroundColor: ['#10b981', '#3b82f6'],
                            borderColor: '#fdfcfb',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                display: false
                            }
                        }
                    }
                });
            };

            const renderEconomyChart = () => {
                const ctx = document.getElementById('economyChart');
                if (!ctx) return;
                if (chartInstances.economy) chartInstances.economy.destroy();
                chartInstances.economy = new Chart(ctx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: ['Economico (Fee/Royalties)', 'Reputazionale (Tracciabilità)',
                            'Ambientale (EPP)'
                        ],
                        datasets: [{
                            data: [33, 34, 33],
                            backgroundColor: ['#10b981', '#3b82f6', '#22c55e'],
                            borderColor: '#fdfcfb',
                            borderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            };

            showSection('premessa');

            // Back to Text Button Logic
            let previousSection = null;
            let previousScrollPosition = 0;
            const backButton = document.getElementById('backToTextButton');
            const backButtonLabel = document.getElementById('backToTextLabel');

            document.addEventListener('click', (e) => {
                const glossaryLink = e.target.closest('.glossary-link');
                if (glossaryLink && glossaryLink.getAttribute('href')?.startsWith('#glossary-')) {
                    const activeSection = document.querySelector('.content-section.active');
                    if (activeSection) {
                        previousSection = activeSection.id.replace('section-', '');
                        previousScrollPosition = window.scrollY;
                        const sectionData = contentData[previousSection];
                        if (sectionData) {
                            backButtonLabel.textContent = `Torna a ${sectionData.nav}`;
                        }
                    }
                }
            });

            window.addEventListener('hashchange', () => {
                const hash = window.location.hash;
                if (hash.startsWith('#glossary-') && previousSection) {
                    backButton.classList.add('show');
                } else {
                    backButton.classList.remove('show');
                    previousSection = null;
                }
            });

            backButton.addEventListener('click', () => {
                if (previousSection) {
                    showSection(previousSection);
                    setTimeout(() => {
                        window.scrollTo({
                            top: previousScrollPosition,
                            behavior: 'smooth'
                        });
                    }, 100);
                    backButton.classList.remove('show');
                    history.pushState('', document.title, window.location.pathname + window.location
                        .search);
                }
            });

            if (window.location.hash.startsWith('#glossary-')) {
                previousSection = 'premessa';
                backButtonLabel.textContent = 'Torna a Premessa';
                backButton.classList.add('show');
            }
        });
    </script>
</body>

</html>
