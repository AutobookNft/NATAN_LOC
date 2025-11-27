<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>White Paper Finanziario Interattivo - FlorenceEGI</title>
    <meta name="description"
        content="White Paper finanziario interattivo FlorenceEGI. Modello economico, tokenomics, revenue streams e sostenibilità del progetto blockchain.">
    <meta name="keywords"
        content="White Paper,Finanziario,FlorenceEGI,Tokenomics,Blockchain,Revenue,Sostenibilità,Investimenti">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="author" content="FlorenceEGI">
    <meta name="language" content="it">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph Protocol -->
    <meta property="og:title" content="White Paper Finanziario - FlorenceEGI">
    <meta property="og:description" content="Modello economico e tokenomics del progetto blockchain sostenibile.">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="FlorenceEGI">
    <meta property="og:locale" content="it_IT">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="White Paper Finanziario - FlorenceEGI">
    <meta name="twitter:description" content="Modello economico e tokenomics blockchain sostenibile.">
    <meta name="twitter:site" content="@FlorenceEGI">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "White Paper Finanziario Interattivo - FlorenceEGI",
        "description": "Documentazione completa del modello economico e tokenomics",
        "author": {
            "@type": "Organization",
            "name": "FlorenceEGI",
            "url": "https://florence-egi.com"
        },
        "datePublished": "2025",
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
            /* Offset per header sticky */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdfcfb;
            color: #383838;
        }

        .active-nav {
            background-color: #047857;
            /* emerald-700 */
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
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
            max-width: 400px;
            margin: auto;
            height: 300px;
        }

        .glossary-link {
            color: #1B365D;
            /* Blu Algoritmo - Brand FlorenceEGI */
            font-weight: 600;
            text-decoration: underline;
            text-decoration-color: #D4A574;
            /* Oro Fiorentino */
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .glossary-link:hover {
            color: #D4A574;
            /* Oro Fiorentino al hover */
            text-decoration-color: #1B365D;
            text-decoration-thickness: 2.5px;
            text-shadow: 0 0 8px rgba(212, 165, 116, 0.3);
        }

        /* Highlight temporaneo per voce di glossario target */
        dt:target {
            animation: highlightTerm 2s ease-in-out;
            scroll-margin-top: 140px;
        }

        @keyframes highlightTerm {
            0% {
                background-color: rgba(212, 165, 116, 0.3);
                /* Oro Fiorentino trasparente */
                transform: scale(1.02);
            }

            50% {
                background-color: rgba(212, 165, 116, 0.2);
            }

            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }

        /* Pulsante Torna al Testo */
        #backToTextButton {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, rgba(27, 54, 93, 0.85) 0%, rgba(45, 80, 22, 0.85) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(27, 54, 93, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border: 2px solid rgba(212, 165, 116, 0.8);
        }

        #backToTextButton:hover {
            transform: translateX(-50%) translateY(-2px) scale(1.05);
            box-shadow: 0 15px 35px rgba(212, 165, 116, 0.5);
            background: linear-gradient(135deg, rgba(45, 80, 22, 0.95) 0%, rgba(27, 54, 93, 0.95) 100%);
            border-color: #D4A574;
        }

        #backToTextButton.show {
            display: flex;
            animation: slideInUp 0.4s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Collapsible Section Styles */
        details.collapsible-section {
            margin-top: 1.5rem;
            border: 2px solid #D4A574;
            border-radius: 0.75rem;
            background-color: #fdfcfb;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
        }

        details.collapsible-section:hover {
            box-shadow: 0 4px 12px rgba(212, 165, 116, 0.2);
        }

        details.collapsible-section summary {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #1B365D 0%, #2D5016 100%);
            color: white;
            font-weight: 600;
            font-size: 1.125rem;
            cursor: pointer;
            user-select: none;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease-in-out;
        }

        details.collapsible-section summary::-webkit-details-marker {
            display: none;
        }

        details.collapsible-section summary::before {
            content: '▶';
            font-size: 0.875rem;
            transition: transform 0.3s ease-in-out;
        }

        details.collapsible-section[open] summary::before {
            transform: rotate(90deg);
        }

        details.collapsible-section summary:hover {
            background: linear-gradient(135deg, #2D5016 0%, #1B365D 100%);
        }

        details.collapsible-section .collapsible-content {
            padding: 1.5rem;
            animation: fadeInContent 0.3s ease-in-out;
        }

        @keyframes fadeInContent {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen">
        <header class="sticky top-0 z-50 bg-white shadow-sm" role="banner">
            <div class="px-4 py-6 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-emerald-800">White Paper Finanziario Interattivo</h1>
                <p class="mt-2 text-gray-600 text-md">Esplora la gestione finanziaria e fiscale di FlorenceEGI in modo
                    semplice e intuitivo.</p>
            </div>
        </header>

        <main class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8" role="main">
            <div class="p-6 mb-8 bg-white shadow-lg rounded-2xl">
                <h2 class="mb-4 text-xl font-bold text-center text-gray-800">Inizia da qui: seleziona un argomento o il
                    tuo ruolo.</h2>
                <nav id="role-nav" class="flex flex-wrap justify-center gap-3 sm:gap-4" role="navigation"
                    aria-label="Navigazione per ruolo">
                </nav>
            </div>

            <div id="content-container">
            </div>

            <!-- GLOSSARIO INSERITO QUI -->
            <section id="glossario" class="mt-16 fade-in">
                <h2 class="pt-12 mb-10 text-4xl font-bold text-center border-t border-gray-200 text-emerald-800">
                    Glossario</h2>
                <div class="p-8 bg-white shadow-lg rounded-2xl">
                    <dl class="space-y-8">
                        <div>
                            <dt id="glossary-anchor-hash" class="text-xl font-bold text-emerald-700">Anchor hash</dt>
                            <dd class="mt-1 text-gray-700">L'azione di scrivere su <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a> l'“impronta” digitale (hash) di un documento.
                                Questo non rivela il contenuto del documento ma crea una prova immutabile della sua
                                esistenza e integrità in un dato momento.</dd>
                        </div>
                        <div>
                            <dt id="glossary-asa" class="text-xl font-bold text-emerald-700">ASA (Algorand Standard
                                Asset)</dt>
                            <dd class="mt-1 text-gray-700">Uno standard per creare "token" (oggetti digitali) sulla <a
                                    href="#glossary-blockchain" class="glossary-link">blockchain</a> di Algorand. Nel
                                nostro caso, l'<a href="#glossary-egi" class="glossary-link">EGI</a> è un ASA.</dd>
                        </div>
                        <div>
                            <dt id="glossary-blockchain" class="text-xl font-bold text-emerald-700">Blockchain</dt>
                            <dd class="mt-1 text-gray-700">Un registro digitale distribuito, immutabile e trasparente
                                che rende verificabili la proprietà e la storia degli oggetti digitali registrati su di
                                essa.</dd>
                        </div>
                        <div>
                            <dt id="glossary-custodial" class="text-xl font-bold text-emerald-700">Custodial (Wallet)
                            </dt>
                            <dd class="mt-1 text-gray-700">Un tipo di <a href="#glossary-wallet"
                                    class="glossary-link">wallet</a> in cui un servizio di terze parti (il "custode")
                                gestisce le chiavi private per conto dell'utente. Offre maggiore comodità ma richiede
                                fiducia nel fornitore del servizio.</dd>
                        </div>
                        <div>
                            <dt id="glossary-egi" class="text-xl font-bold text-emerald-700">EGI</dt>
                            <dd class="mt-1 text-gray-700">Il certificato digitale che attesta l'autenticità e la
                                proprietà di un bene. Tecnicamente, è un token di tipo <a href="#glossary-asa"
                                    class="glossary-link">ASA</a> sulla <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a> di Algorand.</dd>
                        </div>
                        <div>
                            <dt id="glossary-fiat" class="text-xl font-bold text-emerald-700">FIAT</dt>
                            <dd class="mt-1 text-gray-700">Denaro tradizionale emesso da una banca centrale (es. Euro,
                                Dollaro).</dd>
                        </div>
                        <div>
                            <dt id="glossary-mica-safe" class="text-xl font-bold text-emerald-700">MiCA-safe</dt>
                            <dd class="mt-1 text-gray-700">Indica che la piattaforma è progettata per operare in
                                conformità con il regolamento europeo MiCA (Markets in Crypto-Assets), evitando di
                                svolgere attività che richiederebbero licenze specifiche (come la custodia o lo scambio
                                di criptovalute per conto terzi).</dd>
                        </div>
                        <div>
                            <dt id="glossary-mint" class="text-xl font-bold text-emerald-700">Mint (o Minting)</dt>
                            <dd class="mt-1 text-gray-700">L'atto di creare un nuovo token (come un <a
                                    href="#glossary-egi" class="glossary-link">EGI</a>) sulla <a
                                    href="#glossary-blockchain" class="glossary-link">blockchain</a>, registrandolo
                                per
                                la prima volta.</dd>
                        </div>
                        <div>
                            <dt id="glossary-non-custodial" class="text-xl font-bold text-emerald-700">Non-custodial
                                (Wallet)</dt>
                            <dd class="mt-1 text-gray-700">Un tipo di <a href="#glossary-wallet"
                                    class="glossary-link">wallet</a> in cui solo l'utente ha il pieno controllo delle
                                chiavi private e, di conseguenza, dei fondi. La responsabilità della sicurezza è
                                interamente dell'utente.</dd>
                        </div>
                        <div>
                            <dt id="glossary-off-chain" class="text-xl font-bold text-emerald-700">Off-chain</dt>
                            <dd class="mt-1 text-gray-700">Si riferisce a qualsiasi operazione o dato che avviene al di
                                fuori della <a href="#glossary-blockchain" class="glossary-link">blockchain</a>,
                                tramite
                                sistemi tradizionali (es. un bonifico gestito da un <a href="#glossary-psp"
                                    class="glossary-link">PSP</a>).</dd>
                        </div>
                        <div>
                            <dt id="glossary-on-chain" class="text-xl font-bold text-emerald-700">On-chain</dt>
                            <dd class="mt-1 text-gray-700">Si riferisce a qualsiasi operazione o dato registrato
                                direttamente sulla <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a>,
                                rendendolo pubblicamente verificabile e immutabile.</dd>
                        </div>
                        <div>
                            <dt id="glossary-opt-in" class="text-xl font-bold text-emerald-700">Opt-in</dt>
                            <dd class="mt-1 text-gray-700">Un'azione richiesta su alcune <a
                                    href="#glossary-blockchain" class="glossary-link">blockchain</a> (come Algorand)
                                in cui un <a href="#glossary-wallet" class="glossary-link">wallet</a> deve
                                esplicitamente
                                "accettare" di poter ricevere un determinato tipo di token (<a href="#glossary-asa"
                                    class="glossary-link">ASA</a>) prima che questo possa essergli trasferito.</dd>
                        </div>
                        <div>
                            <dt id="glossary-partner-autorizzato" class="text-xl font-bold text-emerald-700">Partner
                                autorizzato (CASP/EMI)</dt>
                            <dd class="mt-1 text-gray-700">Un soggetto terzo che possiede le licenze necessarie (es.
                                Crypto-Asset Service Provider o Electronic Money Institution) per gestire pagamenti e
                                servizi legati alle criptovalute per conto del merchant.</dd>
                        </div>
                        <div>
                            <dt id="glossary-payout" class="text-xl font-bold text-emerald-700">Payout</dt>
                            <dd class="mt-1 text-gray-700">Il processo con cui un <a href="#glossary-psp"
                                    class="glossary-link">PSP</a> trasferisce i fondi incassati dalle vendite sul conto
                                bancario del merchant.</dd>
                        </div>
                        <div>
                            <dt id="glossary-psp" class="text-xl font-bold text-emerald-700">PSP (Payment Service
                                Provider)</dt>
                            <dd class="mt-1 text-gray-700">Un fornitore di servizi di pagamento regolamentato che
                                incassa pagamenti in valuta <a href="#glossary-fiat" class="glossary-link">FIAT</a>
                                (es. tramite carta di credito), gestisce bonifici, rimborsi e ripartizioni per conto dei
                                commercianti (merchant).</dd>
                        </div>
                        <div>
                            <dt id="glossary-royalties" class="text-xl font-bold text-emerald-700">Royalties</dt>
                            <dd class="mt-1 text-gray-700">Termine generico per percentuali sul prezzo di vendita
                                pagate al creatore. Su FlorenceEGI esistono <strong>due tipi distinti</strong>: (1) <a
                                    href="#glossary-royalty-piattaforma" class="glossary-link">Royalty Piattaforma</a>
                                (contrattuale, 4.5%, sempre), (2) <a href="#glossary-diritto-seguito"
                                    class="glossary-link">Diritto di Seguito</a> (legale, 4%-0.25%, solo >€3k). Possono
                                essere cumulabili.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-royalty-piattaforma" class="text-xl font-bold text-emerald-700">Royalty
                                Piattaforma (Contrattuale)</dt>
                            <dd class="mt-1 text-gray-700">Percentuale (4.5%) che FlorenceEGI <strong>garantisce
                                    contrattualmente</strong> al Creator su ogni rivendita secondaria, <strong>anche
                                    sotto €3,000</strong>. Gestita automaticamente via smart contract e distribuita
                                istantaneamente. Questa royalty è <strong>separata e aggiuntiva</strong> al <a
                                    href="#glossary-diritto-seguito" class="glossary-link">Diritto di Seguito</a>
                                legale. Si applica su tutte le vendite P2P sulla piattaforma, indipendentemente dalla
                                normativa SIAE.</dd>
                        </div>
                        <div>
                            <dt id="glossary-diritto-seguito" class="text-xl font-bold text-emerald-700">Diritto di
                                Seguito (Droit de Suite)</dt>
                            <dd class="mt-1 text-gray-700">Diritto <strong>previsto dalla legge italiana</strong> (L.
                                633/1941 Art. 19bis, D.Lgs. 118/2006) che garantisce al Creator un compenso sulle
                                rivendite delle opere d'arte. Si applica <strong>solo se</strong>: (1) prezzo di vendita
                                ≥ €3,000, (2) vendita tramite professionisti del mercato dell'arte (gallerie, case
                                d'asta, dealer), (3) vendita nell'Unione Europea. Aliquote: 4% (0-€50k), 3%
                                (€50k-€200k), 1% (€200k-€350k), 0.5% (€350k-€500k), 0.25% (oltre €500k). Massimo:
                                €12,500 per vendita. Gestito da SIAE. Durata: vita dell'artista + 70 anni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-morali" class="text-xl font-bold text-emerald-700">Diritti Morali
                                d'Autore</dt>
                            <dd class="mt-1 text-gray-700">Diritti <strong>inalienabili, irrinunciabili e
                                    perpetui</strong> del Creator previsti dalla Legge 633/1941 Art. 20: (1)
                                <strong>Diritto di Paternità</strong> - rivendicare sempre la paternità dell'opera e
                                opporsi ad attribuzioni false, (2) <strong>Diritto all'Integrità</strong> - opporsi a
                                deformazioni, mutilazioni o modifiche che possano danneggiare l'onore o la reputazione
                                dell'artista. Questi diritti <strong>restano sempre al Creator</strong>, anche dopo la
                                vendita dell'opera. L'Owner NON può mai modificare, alterare o rimuovere la
                                firma/attribuzione.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-diritti-patrimoniali" class="text-xl font-bold text-emerald-700">Diritti
                                Patrimoniali d'Autore (Sfruttamento Economico)</dt>
                            <dd class="mt-1 text-gray-700">Diritti economici del Creator (Legge 633/1941 Art. 12-19)
                                che includono: <strong>riproduzione</strong> (realizzare copie fisiche/digitali,
                                stampe), <strong>comunicazione al pubblico</strong> (pubblicare online, esposizioni
                                commerciali, TV), <strong>distribuzione</strong> (vendere copie/riproduzioni),
                                <strong>elaborazione</strong> (opere derivative). <strong>IMPORTANTE</strong>:
                                l'acquisto dell'opera fisica (o del NFT) <strong>NON trasferisce</strong>
                                automaticamente questi diritti. L'Owner possiede l'oggetto ma il Creator conserva il
                                copyright sull'immagine. Qualsiasi uso commerciale richiede licenza scritta del Creator.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-settlement" class="text-xl font-bold text-emerald-700">Settlement</dt>
                            <dd class="mt-1 text-gray-700">Il processo finale di trasferimento di fondi al merchant
                                dopo che una transazione è stata completata e confermata.</dd>
                        </div>
                        <div>
                            <dt id="glossary-transfer" class="text-xl font-bold text-emerald-700">Transfer</dt>
                            <dd class="mt-1 text-gray-700">L'azione di trasferire la proprietà di un token da un <a
                                    href="#glossary-wallet" class="glossary-link">wallet</a> a un altro.</dd>
                        </div>
                        <div>
                            <dt id="glossary-wallet" class="text-xl font-bold text-emerald-700">Wallet</dt>
                            <dd class="mt-1 text-gray-700">Un portafoglio digitale utilizzato per custodire e gestire
                                oggetti digitali basati su <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a> (come <a href="#glossary-egi"
                                    class="glossary-link">EGI</a> o criptovalute).</dd>
                        </div>
                        <div>
                            <dt id="glossary-fee" class="text-xl font-bold text-emerald-700">Fee (Commissione)</dt>
                            <dd class="mt-1 text-gray-700">La commissione applicata dalla piattaforma FlorenceEGI per i
                                servizi forniti (minting, trading, trasferimenti). Viene fatturata separatamente e non
                                include i fondi destinati a Creator o EPP.</dd>
                        </div>
                        <div>
                            <dt id="glossary-compliance" class="text-xl font-bold text-emerald-700">Compliance</dt>
                            <dd class="mt-1 text-gray-700">L'insieme di attività necessarie per operare nel rispetto
                                delle normative fiscali, legali e regolamentari vigenti in un determinato contesto o
                                giurisdizione.</dd>
                        </div>
                        <div>
                            <dt id="glossary-fatturazione-elettronica" class="text-xl font-bold text-emerald-700">
                                Fatturazione Elettronica</dt>
                            <dd class="mt-1 text-gray-700">Sistema obbligatorio in Italia per l'emissione, trasmissione
                                e conservazione delle fatture in formato digitale tramite il Sistema di Interscambio
                                (SDI) dell'Agenzia delle Entrate.</dd>
                        </div>
                        <div>
                            <dt id="glossary-iva" class="text-xl font-bold text-emerald-700">IVA (Imposta sul Valore
                                Aggiunto)</dt>
                            <dd class="mt-1 text-gray-700">Imposta indiretta sui consumi applicata in Italia e
                                nell'Unione Europea. L'aliquota e le modalità di applicazione variano in base al tipo di
                                operazione e alla residenza fiscale delle parti coinvolte.</dd>
                        </div>
                        <div>
                            <dt id="glossary-oss" class="text-xl font-bold text-emerald-700">OSS (One Stop Shop)</dt>
                            <dd class="mt-1 text-gray-700">Sistema UE che consente alle aziende di dichiarare e versare
                                l'<a href="#glossary-iva" class="glossary-link">IVA</a> per servizi B2C cross-border
                                tramite un unico portale, semplificando la <a href="#glossary-compliance"
                                    class="glossary-link">compliance</a> internazionale.</dd>
                        </div>
                        <div>
                            <dt id="glossary-moss" class="text-xl font-bold text-emerald-700">MOSS (Mini One Stop
                                Shop)</dt>
                            <dd class="mt-1 text-gray-700">Precedente versione del regime <a href="#glossary-oss"
                                    class="glossary-link">OSS</a>, specifico per servizi di telecomunicazione,
                                broadcasting ed elettronici. Ora integrato nell'OSS.</dd>
                        </div>
                        <div>
                            <dt id="glossary-reverse-charge" class="text-xl font-bold text-emerald-700">Reverse Charge
                            </dt>
                            <dd class="mt-1 text-gray-700">Meccanismo <a href="#glossary-iva"
                                    class="glossary-link">IVA</a> in cui l'obbligo di versare l'imposta passa dal
                                fornitore al cliente (tipicamente applicato nelle transazioni B2B intra-UE con Partita
                                IVA).</dd>
                        </div>
                        <div>
                            <dt id="glossary-partita-iva" class="text-xl font-bold text-emerald-700">Partita IVA</dt>
                            <dd class="mt-1 text-gray-700">Numero identificativo attribuito a chi svolge attività
                                economica abituale in Italia. È obbligatoria per professionisti, imprese e attività
                                commerciali continuative.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ricevuta-prestazione-occasionale"
                                class="text-xl font-bold text-emerald-700">Ricevuta per Prestazione Occasionale</dt>
                            <dd class="mt-1 text-gray-700">Documento fiscale emesso da un privato (senza <a
                                    href="#glossary-partita-iva" class="glossary-link">Partita IVA</a>) per attività
                                non abituali. L'importo va dichiarato come "reddito diverso" nella dichiarazione dei
                                redditi.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ets" class="text-xl font-bold text-emerald-700">ETS (Ente del Terzo
                                Settore)</dt>
                            <dd class="mt-1 text-gray-700">Organizzazioni non profit riconosciute dal Codice del Terzo
                                Settore italiano (es. associazioni, fondazioni, ONLUS). Godono di agevolazioni fiscali e
                                non devono emettere fattura per donazioni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-onlus" class="text-xl font-bold text-emerald-700">ONLUS (Organizzazione
                                Non Lucrativa di Utilità Sociale)</dt>
                            <dd class="mt-1 text-gray-700">Ente senza scopo di lucro riconosciuto in Italia, ora
                                confluito nel regime <a href="#glossary-ets" class="glossary-link">ETS</a>. Le
                                donazioni ricevute beneficiano di agevolazioni fiscali per i donatori.</dd>
                        </div>
                        <div>
                            <dt id="glossary-epp" class="text-xl font-bold text-emerald-700">EPP (Environmental
                                Protection Project)</dt>
                            <dd class="mt-1 text-gray-700">Progetti di tutela ambientale collegati agli <a
                                    href="#glossary-egi" class="glossary-link">EGI</a> su FlorenceEGI. Gli EPP
                                ricevono donazioni dirette sul proprio <a href="#glossary-wallet"
                                    class="glossary-link">wallet</a> da ogni transazione di EGI associato.</dd>
                        </div>
                        <div>
                            <dt id="glossary-creator" class="text-xl font-bold text-emerald-700">Creator
                                (Artista/Autore)</dt>
                            <dd class="mt-1 text-gray-700">L'autore originale dell'opera che crea un <a
                                    href="#glossary-egi" class="glossary-link">EGI</a> sulla piattaforma. Il Creator
                                riceve i proventi delle vendite primarie e <a href="#glossary-royalty-piattaforma"
                                    class="glossary-link">royalty</a> automatiche sulle rivendite (4.5% sempre +
                                eventuale <a href="#glossary-diritto-seguito" class="glossary-link">Diritto di
                                    Seguito</a> legale se >€3k). <strong>IMPORTANTE</strong>: il Creator
                                <strong>conserva sempre</strong> i <a href="#glossary-diritti-morali"
                                    class="glossary-link">Diritti Morali</a> (paternità, integrità) e i <a
                                    href="#glossary-diritti-patrimoniali" class="glossary-link">Diritti
                                    Patrimoniali</a> (riproduzione, copyright immagine), anche dopo la vendita
                                dell'opera fisica o del NFT. L'Owner acquisisce solo il possesso materiale, non il
                                copyright.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-mecenate" class="text-xl font-bold text-emerald-700">Mecenate</dt>
                            <dd class="mt-1 text-gray-700">Soggetto che sostiene finanziariamente un <a
                                    href="#glossary-creator" class="glossary-link">Creator</a> o un <a
                                    href="#glossary-epp" class="glossary-link">EPP</a> attraverso acquisto di <a
                                    href="#glossary-egi" class="glossary-link">EGI</a> o donazioni dirette sulla
                                piattaforma FlorenceEGI.</dd>
                        </div>
                        <div>
                            <dt id="glossary-trader" class="text-xl font-bold text-emerald-700">Trader</dt>
                            <dd class="mt-1 text-gray-700">Utente che acquista e rivende <a href="#glossary-egi"
                                    class="glossary-link">EGI</a> sul mercato secondario della piattaforma con
                                l'obiettivo di realizzare plusvalenze. È responsabile della dichiarazione fiscale dei
                                propri guadagni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-plusvalenza" class="text-xl font-bold text-emerald-700">Plusvalenza</dt>
                            <dd class="mt-1 text-gray-700">Guadagno realizzato dalla vendita di un bene a un prezzo
                                superiore a quello di acquisto. Nel contesto fiscale italiano, le plusvalenze possono
                                essere soggette a tassazione come "redditi diversi" o "redditi da capitale".</dd>
                        </div>
                        <div>
                            <dt id="glossary-merchant" class="text-xl font-bold text-emerald-700">Merchant</dt>
                            <dd class="mt-1 text-gray-700">Termine generico per indicare il venditore o fornitore di
                                servizi che utilizza la piattaforma per vendere beni o emettere <a href="#glossary-egi"
                                    class="glossary-link">EGI</a>. Nel contesto FlorenceEGI, tipicamente un <a
                                    href="#glossary-creator" class="glossary-link">Creator</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-casp" class="text-xl font-bold text-emerald-700">CASP (Crypto-Asset
                                Service Provider)</dt>
                            <dd class="mt-1 text-gray-700">Fornitore di servizi su cripto-attività regolamentato dal
                                regolamento europeo MiCA. Include exchange, custodian e altri intermediari autorizzati a
                                gestire criptovalute per conto terzi.</dd>
                        </div>
                        <div>
                            <dt id="glossary-emi" class="text-xl font-bold text-emerald-700">EMI (Electronic Money
                                Institution)</dt>
                            <dd class="mt-1 text-gray-700">Istituto di moneta elettronica autorizzato a emettere e
                                gestire moneta elettronica e fornire servizi di pagamento nell'Unione Europea.</dd>
                        </div>
                        <div>
                            <dt id="glossary-gdpr" class="text-xl font-bold text-emerald-700">GDPR (General Data
                                Protection Regulation)</dt>
                            <dd class="mt-1 text-gray-700">Regolamento europeo (UE) 2016/679 sulla protezione dei dati
                                personali e sulla privacy. Stabilisce obblighi rigorosi per la raccolta, il trattamento,
                                la conservazione e la cancellazione dei dati personali, garantendo diritti fondamentali
                                agli utenti come il diritto all'oblio e la portabilità dei dati. FlorenceEGI è
                                pienamente conforme al GDPR nella gestione delle chiavi private e dei dati degli utenti.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-mica" class="text-xl font-bold text-emerald-700">MiCA (Markets in
                                Crypto-Assets Regulation)</dt>
                            <dd class="mt-1 text-gray-700">Regolamento europeo che disciplina i mercati di
                                cripto-attività, definendo regole chiare per l'emissione, l'offerta e l'ammissione al
                                trading di crypto-asset, nonché per i fornitori di servizi correlati.</dd>
                        </div>
                        <div>
                            <dt id="glossary-sostituto-imposta" class="text-xl font-bold text-emerald-700">Sostituto
                                d'Imposta</dt>
                            <dd class="mt-1 text-gray-700">Soggetto che, per legge, trattiene e versa le imposte dovute
                                da un altro contribuente (es. datore di lavoro che trattiene IRPEF sullo stipendio).
                                FlorenceEGI NON è sostituto d'imposta.</dd>
                        </div>
                        <div>
                            <dt id="glossary-fatturazione-batch" class="text-xl font-bold text-emerald-700">
                                Fatturazione Batch (Cumulativa)</dt>
                            <dd class="mt-1 text-gray-700">Sistema di fatturazione che raggruppa più operazioni in un
                                unico documento fiscale periodico (es. mensile), con allegato il dettaglio delle singole
                                transazioni. Utilizzato per gestire alto volume di micro-transazioni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-dashboard" class="text-xl font-bold text-emerald-700">Dashboard</dt>
                            <dd class="mt-1 text-gray-700">Pannello di controllo sulla piattaforma FlorenceEGI che
                                fornisce a ogni utente una visione d'insieme delle proprie attività, incassi, <a
                                    href="#glossary-egi" class="glossary-link">EGI</a> posseduti, report fiscali e
                                strumenti di gestione.</dd>
                        </div>
                        <div>
                            <dt id="glossary-qr-code" class="text-xl font-bold text-emerald-700">QR Code (Verifica
                                Pubblica)</dt>
                            <dd class="mt-1 text-gray-700">Codice QR associato a ogni <a href="#glossary-egi"
                                    class="glossary-link">EGI</a> che permette la verifica pubblica e immediata
                                dell'autenticità del certificato digitale tramite scansione con smartphone, senza
                                necessità di autenticazione.</dd>
                        </div>
                        <div>
                            <dt id="glossary-erp" class="text-xl font-bold text-emerald-700">ERP (Enterprise Resource
                                Planning)</dt>
                            <dd class="mt-1 text-gray-700">Sistema software gestionale integrato utilizzato da aziende
                                ed enti per gestire processi aziendali, contabilità, risorse umane, supply chain e altre
                                attività operative.</dd>
                        </div>
                        <div>
                            <dt id="glossary-crm" class="text-xl font-bold text-emerald-700">CRM (Customer
                                Relationship Management)</dt>
                            <dd class="mt-1 text-gray-700">Sistema software per la gestione delle relazioni con i
                                clienti, utilizzato per tracciare interazioni, vendite, supporto e marketing. Utilizzato
                                da grandi enti per integrare dati da FlorenceEGI.</dd>
                        </div>
                        <div>
                            <dt id="glossary-sdi" class="text-xl font-bold text-emerald-700">SDI (Sistema di
                                Interscambio)</dt>
                            <dd class="mt-1 text-gray-700">Sistema dell'Agenzia delle Entrate italiana che gestisce la
                                ricezione, controllo e trasmissione delle fatture elettroniche tra emittenti e
                                destinatari, garantendo la <a href="#glossary-compliance"
                                    class="glossary-link">compliance</a> fiscale.</dd>
                        </div>
                        <div>
                            <dt id="glossary-export-csv" class="text-xl font-bold text-emerald-700">Export CSV/XML
                            </dt>
                            <dd class="mt-1 text-gray-700">Funzionalità della piattaforma che permette di scaricare i
                                dati delle proprie transazioni in formato CSV (tabella) o XML (strutturato), per
                                l'integrazione con software di contabilità esterni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-alert-fiscale" class="text-xl font-bold text-emerald-700">Alert Fiscale
                            </dt>
                            <dd class="mt-1 text-gray-700">Notifica automatica inviata dalla piattaforma quando si
                                raggiungono soglie fiscali rilevanti (es. limite per prestazione occasionale, volume di
                                trading significativo) per ricordare all'utente i propri obblighi di <a
                                    href="#glossary-compliance" class="glossary-link">compliance</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ricevuta-donazione" class="text-xl font-bold text-emerald-700">Ricevuta
                                di Donazione</dt>
                            <dd class="mt-1 text-gray-700">Documento rilasciato da un ente (<a href="#glossary-ets"
                                    class="glossary-link">ETS</a>, <a href="#glossary-onlus"
                                    class="glossary-link">ONLUS</a>) che attesta la donazione ricevuta, permettendo al
                                donatore di usufruire di detrazioni o deduzioni fiscali secondo la normativa vigente.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-audit-trail" class="text-xl font-bold text-emerald-700">Audit Trail</dt>
                            <dd class="mt-1 text-gray-700">Registrazione cronologica completa e immutabile di tutte le
                                operazioni effettuate, utilizzata per verifiche fiscali, controlli di <a
                                    href="#glossary-compliance" class="glossary-link">compliance</a> e tracciabilità.
                                Su FlorenceEGI garantita dalla <a href="#glossary-blockchain"
                                    class="glossary-link">blockchain</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-tracciabilita" class="text-xl font-bold text-emerald-700">Tracciabilità
                            </dt>
                            <dd class="mt-1 text-gray-700">Capacità di ricostruire la storia completa di un <a
                                    href="#glossary-egi" class="glossary-link">EGI</a> (proprietari, transazioni,
                                donazioni) grazie alla registrazione <a href="#glossary-on-chain"
                                    class="glossary-link">on-chain</a> e agli <a href="#glossary-audit-trail"
                                    class="glossary-link">audit trail</a> della piattaforma.</dd>
                        </div>
                        <div>
                            <dt id="glossary-algoritmo" class="text-xl font-bold text-emerald-700">Algorand</dt>
                            <dd class="mt-1 text-gray-700"><a href="#glossary-blockchain"
                                    class="glossary-link">Blockchain</a> di tipo "proof-of-stake pura" ad alte
                                prestazioni, basso costo e impatto ambientale ridotto, utilizzata da FlorenceEGI per la
                                creazione e gestione degli <a href="#glossary-egi" class="glossary-link">EGI</a> come
                                <a href="#glossary-asa" class="glossary-link">ASA</a>.
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-hash" class="text-xl font-bold text-emerald-700">Hash (Impronta Digitale)
                            </dt>
                            <dd class="mt-1 text-gray-700">Sequenza alfanumerica unica generata da un algoritmo
                                crittografico a partire da un file o documento. Qualsiasi modifica al contenuto produce
                                un hash completamente diverso, garantendo integrità e verificabilità.</dd>
                        </div>
                        <div>
                            <dt id="glossary-chiave-privata" class="text-xl font-bold text-emerald-700">Chiave Privata
                            </dt>
                            <dd class="mt-1 text-gray-700">Codice segreto crittografico che permette di controllare e
                                autorizzare operazioni su un <a href="#glossary-wallet"
                                    class="glossary-link">wallet</a> blockchain. Chi possiede la chiave privata ha
                                pieno controllo dei fondi. Non va MAI condivisa.</dd>
                        </div>
                        <div>
                            <dt id="glossary-token" class="text-xl font-bold text-emerald-700">Token</dt>
                            <dd class="mt-1 text-gray-700">Unità digitale rappresentante un asset, un diritto o un
                                valore su una <a href="#glossary-blockchain" class="glossary-link">blockchain</a>. Nel
                                contesto FlorenceEGI, l'<a href="#glossary-egi" class="glossary-link">EGI</a> è un
                                token di tipo <a href="#glossary-asa" class="glossary-link">ASA</a>.</dd>
                        </div>
                        <div>
                            <dt id="glossary-immutabilita" class="text-xl font-bold text-emerald-700">Immutabilità
                            </dt>
                            <dd class="mt-1 text-gray-700">Caratteristica fondamentale della <a
                                    href="#glossary-blockchain" class="glossary-link">blockchain</a>: una volta
                                registrata, un'informazione non può essere modificata o cancellata, garantendo la
                                veridicità storica dei dati.</dd>
                        </div>
                        <div>
                            <dt id="glossary-report" class="text-xl font-bold text-emerald-700">Report Fiscale</dt>
                            <dd class="mt-1 text-gray-700">Documento riepilogativo generato automaticamente dalla
                                piattaforma che sintetizza tutte le transazioni, incassi, <a href="#glossary-fee"
                                    class="glossary-link">fee</a> e donazioni di un utente in un determinato periodo,
                                facilitando la dichiarazione fiscale.</dd>
                        </div>
                        <div>
                            <dt id="glossary-ammk" class="text-xl font-bold text-emerald-700">AMMk (Asset Market
                                Maker)</dt>
                            <dd class="mt-1 text-gray-700">
                                <p>
                                    Termine coniato da FlorenceEGI per descrivere piattaforme che trasformano opere o
                                    contenuti in <a href="#glossary-egi" class="glossary-link">EGI</a> (asset
                                    digitali)
                                    e ne governano l’intero ciclo di valore.
                                </p>
                                <p class="mt-2">
                                    FlorenceEGI è il primo AMMk al mondo: il <a href="#glossary-florenceegi-core"
                                        class="glossary-link">FlorenceEGI Core</a> coordina cinque blocchi
                                    specializzati, ognuno con responsabilità precise:
                                </p>
                                <ul class="mt-3 space-y-3 text-gray-700 list-disc list-inside">
                                    <li>
                                        <strong>NATAN Market Engine</strong> – l’intelligenza del <a
                                            href="#glossary-natan" class="glossary-link">tenant NATAN</a> che rende la
                                        piattaforma un vero market maker. Comprende due componenti inscindibili:
                                        <ul class="mt-2 ml-5 space-y-1 text-gray-700 list-disc list-inside">
                                            <li><strong>Valuation</strong> – definisce valore, floor price e traiettoria
                                                economica analizzando qualità, storico e domanda.</li>
                                            <li><strong>Activation</strong> – orchestra campagne, alert e suggerimenti
                                                operativi basati su trigger on/off-chain.</li>
                                        </ul>
                                    </li>
                                    <li><strong>Asset Engine</strong> – gestisce listing, aste, vendite secondarie e
                                        liquidità dell’asset digitale.</li>
                                    <li><strong>Distribution Engine</strong> – automatizza royalty, fee piattaforma e
                                        quota <a href="#glossary-epp" class="glossary-link">EPP</a>.</li>
                                    <li><strong>Co-Creation Engine</strong> – governa il flusso <a
                                            href="#glossary-co-creatore" class="glossary-link">Creator</a> / <a
                                            href="#glossary-co-creatore" class="glossary-link">Co-Creator</a> / <a
                                            href="#glossary-collector" class="glossary-link">Collector</a> e la
                                        generazione dell’EGI (minting, notarizzazione, firme digitali).</li>
                                    <li><strong>Compliance Engine</strong> – integra GDPR, audit trail e tutela <a
                                            href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a>.</li>
                                </ul>
                            </dd>
                        </div>
                        <div>
                            <dt id="glossary-natan" class="text-xl font-bold text-emerald-700">NATAN (Neural
                                Assistant)</dt>
                            <dd class="mt-1 text-gray-700">Tenant funzionale di FlorenceEGI dedicato ad assistenza
                                documentale, notarizzazione e automazioni AI. Opera come layer cognitivo per enti
                                pubblici e privati, con servizi RAG, verifica prove e suggerimenti operativi attivati da
                                trigger
                                intelligenti.</dd>
                        </div>
                        <div>
                            <dt id="glossary-drops" class="text-xl font-bold text-emerald-700">Drops (Trimestrali)
                            </dt>
                            <dd class="mt-1 text-gray-700">Eventi trimestrali che selezionano opere eccellenti e
                                culminano in una Serata Memorabile, concentrando attenzione e liquidità.</dd>
                        </div>
                        <div>
                            <dt id="glossary-florenceegi-core" class="text-xl font-bold text-emerald-700">FlorenceEGI
                                Core (SaaS)</dt>
                            <dd class="mt-1 text-gray-700">Nodo centrale dell'ecosistema: gestisce onboarding,
                                autenticazione, billing, governance, logging ULM/UEM e registro tenant. Garantisce che i
                                tenant condividano policy, sicurezza e compliance comuni.</dd>
                        </div>
                        <div>
                            <dt id="glossary-tenant-specializzato" class="text-xl font-bold text-emerald-700">Tenant
                                specializzato</dt>
                            <dd class="mt-1 text-gray-700">Istanza verticale collegata a FlorenceEGI Core con funzioni
                                dedicate: es. <strong>Natan</strong> (assistente documentale/AI) o
                                <strong>FlorenceArtEGI</strong> (arte e marketplace). Ogni tenant eredita sicurezza e
                                fiscalità dal core, mantenendo processi e interfacce proprie.
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
        </main>

        <footer class="mt-16 text-white bg-gray-900">
            <div class="container px-6 py-12 mx-auto max-w-7xl">
                <p class="text-center text-gray-500">&copy; 2025 Florence EGI | Tutti i diritti riservati.</p>
            </div>
        </footer>

        <!-- Pulsante Torna al Testo -->
        <button id="backToTextButton" aria-label="Torna al testo">
            <span class="material-icons" style="font-size: 20px;">arrow_back</span>
            <span id="backToTextLabel">Torna al testo</span>
        </button>

    </div>

    <script>
        const contentData = {
            panoramica: {
                title: 'Panoramica Generale',
                nav: 'Panoramica',
                intro: 'Questa sezione offre una visione d\'insieme dei principi fondamentali che guidano la compliance fiscale sulla piattaforma FlorenceEGI. Qui puoi comprendere la filosofia di trasparenza, la divisione delle responsabilità e il funzionamento generale del sistema.',
                content: '<div class="grid items-center gap-8 md:grid-cols-2">' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-emerald-700">Principi Guida</h3>' +
                    '<div class="space-y-3">' +
                    '<div class="p-4 rounded-lg bg-emerald-50"><h4 class="font-semibold text-emerald-800">Trasparenza Radicale</h4><p class="text-sm text-gray-600">Ogni flusso economico è <a href="#glossary-tracciabilita" class="glossary-link">tracciabile</a>, ricostruibile e accessibile per garantire massima chiarezza grazie alla <a href="#glossary-blockchain" class="glossary-link">blockchain</a>.</p></div>' +
                    '<div class="p-4 rounded-lg bg-emerald-50"><h4 class="font-semibold text-emerald-800">Automazione Intelligente</h4><p class="text-sm text-gray-600">Generazione automatica di <a href="#glossary-report" class="glossary-link">report</a>, ricevute e <a href="#glossary-fatturazione-elettronica" class="glossary-link">fatture elettroniche</a> per ridurre il rischio di errore umano.</p></div>' +
                    '<div class="p-4 rounded-lg bg-emerald-50"><h4 class="font-semibold text-emerald-800">Responsabilità Chiara</h4><p class="text-sm text-gray-600">Ogni attore conosce esattamente i propri obblighi. La piattaforma non agisce mai da <a href="#glossary-sostituto-imposta" class="glossary-link">sostituto d\'imposta</a>.</p></div>' +
                    '</div>' +
                    '</div>' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-center text-emerald-700">Ripartizione delle Responsabilità</h3>' +
                    '<p class="mb-4 text-center text-gray-600">Il grafico illustra come la responsabilità fiscale sia principalmente in capo all\'utente, mentre la piattaforma fornisce gli strumenti per la <a href="#glossary-compliance" class="glossary-link">compliance</a>.</p>' +
                    '<div class="chart-container"><canvas id="responsibilityChart"></canvas></div>' +
                    '</div>' +
                    '</div>'
            },
            pagamenti: {
                title: 'Gestione Pagamenti & Blockchain',
                nav: 'Gestione Pagamenti',
                intro: 'Un approccio inclusivo in tre livelli per unire pagamenti tradizionali e certificazione digitale, in totale sicurezza e conformità normativa.',
                content: '<div class="p-6 mb-8 border-l-4 rounded-r-lg bg-emerald-50 border-emerald-500 text-emerald-800">' +
                    '<h4 class="mb-2 text-xl font-bold">Filosofia: Inclusione Progressiva</h4>' +
                    '<p class="text-gray-700">Il nostro sistema è progettato per tutti. Chi non conosce il mondo crypto usa la moneta <a href="#glossary-fiat" class="glossary-link">FIAT</a> e i metodi di pagamento tradizionali. Chi possiede un <a href="#glossary-wallet" class="glossary-link">wallet</a> può ricevere il certificato digitale <a href="#glossary-egi" class="glossary-link">EGI</a> direttamente lì. Chi vuole accettare pagamenti in criptovalute può farlo tramite <a href="#glossary-partner-autorizzato" class="glossary-link">partner esterni autorizzati</a>, senza imporre alcuna complessità agli altri utenti.</p>' +
                    '</div>' +
                    '<div class="space-y-8">' +
                    '<div class="p-6 border rounded-lg bg-gray-50"><h3 class="mb-4 text-2xl font-bold text-gray-800">Livello 1 — Nessun wallet (100% tradizionale)</h3><p class="mb-6 text-gray-600">L\'esperienza d\'uso è identica a un normale e-commerce. Zero cripto, zero complessità.</p><div class="grid gap-6 md:grid-cols-2"><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Cliente</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Paga in euro (<a href="#glossary-fiat" class="glossary-link">FIAT</a>) su pagina sicura del <a href="#glossary-psp" class="glossary-link">PSP</a>.</li><li>Riceve l\'<a href="#glossary-egi" class="glossary-link">EGI</a>: la piattaforma esegue <a href="#glossary-mint" class="glossary-link">mint</a> e <a href="#glossary-transfer" class="glossary-link">transfer</a> e salva l\'<a href="#glossary-anchor-hash" class="glossary-link">anchor hash</a>.</li><li>Verifica pubblica con QR.</li></ul></div><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Merchant</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Riceve denaro in <a href="#glossary-fiat" class="glossary-link">FIAT</a> dal <a href="#glossary-psp" class="glossary-link">PSP</a> (<a href="#glossary-payout" class="glossary-link">payout</a>).</li><li>Vede l\'<a href="#glossary-egi" class="glossary-link">EGI</a> emesso e i report.</li><li><a href="#glossary-royalties" class="glossary-link">Royalties</a> e ripartizioni sono gestite dal <a href="#glossary-psp" class="glossary-link">PSP</a> (<a href="#glossary-off-chain" class="glossary-link">off-chain</a>).</li></ul></div></div>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">account_balance_wallet</span> Wallet Auto-Generato per Utenti FIAT (Custodia Tecnica Limitata)</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Per gli utenti che acquistano in valuta <a href="#glossary-fiat" class="glossary-link">FIAT</a> e non possiedono competenze blockchain, FlorenceEGI genera automaticamente un <a href="#glossary-wallet" class="glossary-link">wallet</a> dedicato su Algorand.</p><div class="space-y-3 text-gray-700"><ul class="space-y-2 list-disc list-inside"><li>Il <a href="#glossary-wallet" class="glossary-link">wallet</a> è creato al momento della registrazione o del primo acquisto.</li><li>Contiene esclusivamente NFT unici (<a href="#glossary-egi" class="glossary-link">EGI</a>), senza alcun token fungibile o criptovaluta.</li><li>Le chiavi private sono cifrate con algoritmo AES-256 a livello server e archiviate in modo sicuro nel database conforme <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</li><li>L\'utente può in qualunque momento accedere alla propria area personale, scaricare la frase segreta per importarla in Pera Wallet o in altro client compatibile e richiederne la cancellazione definitiva dai sistemi FlorenceEGI.</li><li>Finché non effettua questa operazione, il <a href="#glossary-wallet" class="glossary-link">wallet</a> rimane invisibile e inattivo per l\'utente.</li><li>Il <a href="#glossary-wallet" class="glossary-link">wallet</a> <strong>non è utilizzabile</strong> per detenere o trasferire ALGO, stablecoin o altri asset di valore monetario.</li></ul></div><div class="p-4 mt-6 border-l-4 border-blue-600 rounded-lg bg-blue-50"><h5 class="mb-2 font-semibold text-blue-900">Conformità Normativa <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a></h5><p class="text-sm text-blue-800">FlorenceEGI <strong>non esegue operazioni di cambio</strong>, <strong>non custodisce fondi</strong>, né intermedia transazioni tra utenti. Questa gestione costituisce <strong>custodia tecnica limitata di asset digitali non finanziari</strong> e non configura attività di CASP (Crypto-Asset Service Provider) ai sensi del Regolamento <a href="#glossary-mica" class="glossary-link">MiCA</a>. La piattaforma opera quindi <strong>fuori dal perimetro MiCA</strong>, soggetta esclusivamente agli obblighi di sicurezza informatica e protezione dei dati personali previsti dal <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</p></div></div></details></div>' +
                    '<div class="p-6 border rounded-lg bg-gray-50"><h3 class="mb-4 text-2xl font-bold text-gray-800">Livello 2 — Ho un wallet, pago in FIAT</h3><p class="mb-6 text-gray-600">Gli utenti più esperti possono usare il proprio wallet per ricevere il certificato, senza imporre la cripto come pagamento.</p><div class="grid gap-6 md:grid-cols-2"><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Cliente</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Paga sempre in <a href="#glossary-fiat" class="glossary-link">FIAT</a>.</li><li>Sceglie dove ricevere l\'<a href="#glossary-egi" class="glossary-link">EGI</a>: nel <a href="#glossary-wallet" class="glossary-link">wallet</a> personale (<a href="#glossary-non-custodial" class="glossary-link">non-custodial</a>) o in uno <a href="#glossary-custodial" class="glossary-link">custodial</a> della piattaforma.</li></ul></div><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Merchant</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Incassa sempre in <a href="#glossary-fiat" class="glossary-link">FIAT</a>.</li><li>L\'<a href="#glossary-egi" class="glossary-link">EGI</a> viene trasferito con tracciabilità <a href="#glossary-on-chain" class="glossary-link">on-chain</a>.</li></ul></div></div>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">vpn_key</span> Gestione Wallet Utenti Livello 2 – Modello Non-Custodial FIAT</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Quando l\'utente decide di utilizzare un proprio <a href="#glossary-wallet" class="glossary-link">wallet</a> Algorand, la gestione passa in modalità <a href="#glossary-non-custodial" class="glossary-link">non-custodial</a>.</p><div class="space-y-3 text-gray-700"><ul class="space-y-2 list-disc list-inside"><li>L\'utente esporta la frase segreta dal <a href="#glossary-wallet" class="glossary-link">wallet</a> generato in precedenza, la importa in Pera Wallet (o altro client compatibile) e richiede la cancellazione definitiva dal database FlorenceEGI.</li><li>Da quel momento FlorenceEGI <strong>non detiene più alcuna chiave privata</strong> né può accedere ai suoi asset.</li><li>Durante un nuovo acquisto in valuta <a href="#glossary-fiat" class="glossary-link">FIAT</a>, l\'utente inserisce l\'indirizzo del proprio <a href="#glossary-wallet" class="glossary-link">wallet</a>.</li><li>Il <a href="#glossary-mint" class="glossary-link">mint</a> dell\'<a href="#glossary-egi" class="glossary-link">EGI</a> (<a href="#glossary-asa" class="glossary-link">ASA</a> supply = 1) viene eseguito direttamente con <strong>sender = wallet dell\'utente</strong>, senza alcuna transazione di trasferimento successiva.</li><li>FlorenceEGI paga le micro-fee di rete come <strong>fee-payer tecnico</strong>.</li><li><strong>Nessun fondo in criptovaluta</strong> transita tra le parti; il pagamento rimane interamente in <a href="#glossary-fiat" class="glossary-link">FIAT</a> tramite <a href="#glossary-psp" class="glossary-link">PSP</a>.</li><li>Il <a href="#glossary-wallet" class="glossary-link">wallet</a> appartiene e resta sotto il <strong>controllo esclusivo dell\'utente</strong>.</li></ul></div><div class="p-4 mt-6 border-l-4 border-green-600 rounded-lg bg-green-50"><h5 class="mb-2 font-semibold text-green-900">Conformità Normativa <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a></h5><p class="text-sm text-green-800">FlorenceEGI <strong>non svolge funzioni di custodia, intermediazione o scambio</strong> di asset digitali. Questa modalità è <strong>pienamente fuori dal perimetro <a href="#glossary-mica" class="glossary-link">MiCA</a></strong>, trattandosi di semplice <strong>emissione di NFT unici</strong> verso un <a href="#glossary-wallet" class="glossary-link">wallet</a> di proprietà dell\'utente, con pagamento in valuta tradizionale.</p></div></div></details></div>' +
                    '<div class="p-6 border rounded-lg bg-gray-50"><h3 class="mb-4 text-2xl font-bold text-gray-800">Livello 3 — Accetto pagamenti Crypto (opzionale)</h3><p class="mb-6 text-gray-600">Questo livello è facoltativo e gestito da partner esterni per mantenere la piattaforma <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a>.</p><div class="grid gap-6 md:grid-cols-2"><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Merchant</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Si affida a un <a href="#glossary-partner-autorizzato" class="glossary-link">Partner autorizzato (CASP/EMI)</a>.</li><li>I clienti pagano sul checkout del Partner.</li><li>Il <a href="#glossary-settlement" class="glossary-link">settlement</a> è gestito dal Partner.</li></ul></div><div><h4 class="mb-2 text-lg font-semibold text-emerald-800">Per il Cliente</h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Paga in crypto sul checkout del Partner.</li><li>Riceve l\'<a href="#glossary-egi" class="glossary-link">EGI</a> come sempre.</li></ul></div></div>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">currency_bitcoin</span> Pagamenti Stablecoin via PSP Partner – Wallet-to-Wallet Direct</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">L\'utente esperto che possiede un <a href="#glossary-wallet" class="glossary-link">wallet</a> Algorand può effettuare acquisti utilizzando stablecoin al posto della valuta <a href="#glossary-fiat" class="glossary-link">FIAT</a>.</p><div class="space-y-3 text-gray-700"><ul class="space-y-2 list-disc list-inside"><li>L\'utente mantiene il <strong>controllo esclusivo</strong> del proprio <a href="#glossary-wallet" class="glossary-link">wallet</a> (<a href="#glossary-non-custodial" class="glossary-link">non-custodial</a>).</li><li>Nel form di acquisto seleziona "Pagamento in stablecoin" e inserisce l\'indirizzo del <a href="#glossary-wallet" class="glossary-link">wallet</a>.</li><li>Il <a href="#glossary-mint" class="glossary-link">mint</a> dell\'<a href="#glossary-egi" class="glossary-link">EGI</a> (<a href="#glossary-asa" class="glossary-link">ASA</a> univoco) avviene con <strong>sender = wallet dell\'utente</strong>.</li><li>Il pagamento è eseguito <strong>direttamente wallet-to-wallet</strong> tra l\'utente e un <a href="#glossary-psp" class="glossary-link">Payment Service Provider (PSP)</a> partner della piattaforma, scelto e sottoscritto dall\'utente tramite accordo privato.</li><li>FlorenceEGI <strong>non gestisce conversioni FIAT↔crypto</strong>, non detiene fondi, non partecipa alla transazione in stablecoin e non custodisce chiavi private.</li><li>Le stablecoin accettate devono essere <strong>emesse da soggetti conformi <a href="#glossary-mica" class="glossary-link">MiCA</a></strong> e riconosciuti dalla piattaforma come <a href="#glossary-psp" class="glossary-link">PSP</a> autorizzati.</li></ul></div><div class="p-4 mt-6 border-l-4 border-purple-600 rounded-lg bg-purple-50"><h5 class="mb-2 font-semibold text-purple-900">Conformità Normativa <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a></h5><p class="text-sm text-purple-800">In questa modalità FlorenceEGI opera <strong>unicamente come infrastruttura di registrazione su <a href="#glossary-blockchain" class="glossary-link">blockchain</a></strong>, senza alcun ruolo finanziario o di intermediazione. La gestione rientra <strong>pienamente fuori dal perimetro <a href="#glossary-mica" class="glossary-link">MiCA</a></strong>, poiché i pagamenti crypto sono gestiti esclusivamente da <a href="#glossary-psp" class="glossary-link">PSP</a> partner conformi (<a href="#glossary-casp" class="glossary-link">CASP</a>/<a href="#glossary-emi" class="glossary-link">EMI</a>), con cui l\'utente ha un rapporto contrattuale diretto.</p></div></div></details></div>' +
                    '</div>' +
                    '<div class="mt-8">' +
                    '<h3 class="mb-4 text-2xl font-bold text-center text-gray-800">Cosa fa (e non fa) la piattaforma</h3>' +
                    '<div class="grid max-w-4xl gap-6 mx-auto md:grid-cols-2">' +
                    '<div class="p-4 border-l-4 border-green-600 rounded-lg bg-green-50"><h4 class="mb-2 text-lg font-bold text-green-800">Cosa Fa</h4><ul class="space-y-1 text-gray-700 list-disc list-inside"><li>Incassa <a href="#glossary-fiat" class="glossary-link">FIAT</a> tramite <a href="#glossary-psp" class="glossary-link">PSP</a>.</li><li>Emette e trasferisce <a href="#glossary-egi" class="glossary-link">EGI</a>.</li><li>Scrive <a href="#glossary-anchor-hash" class="glossary-link">anchor hash</a>.</li><li>Gestisce QR e verifica pubblica.</li><li>Calcola <a href="#glossary-royalties" class="glossary-link">royalties</a> per il <a href="#glossary-psp" class="glossary-link">PSP</a>.</li></ul></div>' +
                    '<div class="p-4 border-l-4 border-red-600 rounded-lg bg-red-50"><h4 class="mb-2 text-lg font-bold text-red-800">Cosa NON Fa</h4><ul class="space-y-1 text-gray-700 list-disc list-inside"><li>Custodire criptovalute per terzi.</li><li>Fare da exchange crypto/<a href="#glossary-fiat" class="glossary-link">fiat</a>.</li><li>Processare pagamenti crypto.</li></ul></div>' +
                    '</div>' +
                    '</div>'
            },
            piattaforma: {
                title: 'Ruolo della Piattaforma',
                nav: 'Piattaforma',
                intro: 'In questa sezione viene definito il ruolo di FlorenceEGI: un facilitatore tecnologico che non gestisce fondi per conto terzi. Vengono illustrati gli obblighi fiscali specifici della piattaforma, la gestione delle proprie commissioni (fee) e come fornisce strumenti di supporto agli utenti senza mai sostituirsi a loro nelle responsabilità fiscali.',
                content: '<h3 class="mb-4 text-2xl font-bold text-emerald-700">Obblighi Fiscali di FlorenceEGI</h3>' +
                    '<div class="grid gap-8 md:grid-cols-2">' +
                    '<div class="p-6 rounded-lg bg-gray-50"><h4 class="mb-3 text-lg font-semibold text-gray-800">Gestione <a href="#glossary-fee" class="glossary-link">Fee</a></h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Incassa **esclusivamente** la propria <a href="#glossary-fee" class="glossary-link">fee</a> di servizio.</li><li>I fondi non vengono mai trattenuti per conto di terzi (<a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a>).</li><li>Le <a href="#glossary-fee" class="glossary-link">fee</a> vengono accreditate direttamente sul <a href="#glossary-wallet" class="glossary-link">wallet</a> della piattaforma.</li></ul></div>' +
                    '<div class="p-6 rounded-lg bg-gray-50"><h4 class="mb-3 text-lg font-semibold text-gray-800">Fatturazione e <a href="#glossary-iva" class="glossary-link">IVA</a></h4><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Emette <a href="#glossary-fatturazione-elettronica" class="glossary-link">fattura elettronica</a> per ogni <a href="#glossary-fee" class="glossary-link">fee</a> incassata tramite <a href="#glossary-sdi" class="glossary-link">SDI</a>.</li><li>Adotta <a href="#glossary-fatturazione-batch" class="glossary-link">fatturazione cumulativa (batch)</a> per operazioni ad alto volume.</li><li>Gestisce l\'<a href="#glossary-iva" class="glossary-link">IVA</a> secondo le normative nazionali e internazionali (<a href="#glossary-oss" class="glossary-link">OSS</a>/<a href="#glossary-moss" class="glossary-link">MOSS</a>).</li></ul></div>' +
                    '</div>' +
                    '<div class="mt-8">' +
                    '<h4 class="mb-4 text-xl font-bold text-center text-emerald-700">Flusso Finanziario della Piattaforma</h4>' +
                    '<div class="flex flex-col items-center justify-center gap-4 text-center md:flex-row md:flex-wrap">' +
                    '<div class="p-4 bg-blue-100 rounded-lg shadow-sm"><p class="font-semibold">Transazione Utente</p><p class="text-sm">(es. <a href="#glossary-mint" class="glossary-link">Minting</a>, Trading)</p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 rounded-lg shadow-sm bg-emerald-100"><p class="font-semibold">Separazione <a href="#glossary-fee" class="glossary-link">Fee</a></p><p class="text-sm">La <a href="#glossary-fee" class="glossary-link">fee</a> viene separata</p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 bg-green-100 rounded-lg shadow-sm"><p class="font-semibold"><a href="#glossary-wallet" class="glossary-link">Wallet</a> FlorenceEGI</p><p class="text-sm">La <a href="#glossary-fee" class="glossary-link">fee</a> è incassata</p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 bg-yellow-100 rounded-lg shadow-sm"><p class="font-semibold">Fatturazione</p><p class="text-sm">Emissione <a href="#glossary-fatturazione-elettronica" class="glossary-link">fattura</a> all\'utente</p></div>' +
                    '</div>' +
                    '</div>'
            },
            creator: {
                title: 'Gestione Fiscale per Creator e Mecenati',
                nav: 'Creator / Mecenate',
                intro: 'Se sei un Creator o un Mecenate, questa sezione è per te. Qui troverai una guida chiara su come vengono gestiti i tuoi incassi, quali sono i tuoi obblighi fiscali a seconda che tu sia un privato o una Partita IVA, e quali strumenti di automazione e reportistica FlorenceEGI mette a tua disposizione per semplificare la tua compliance.',
                content: '<div class="grid gap-8 md:grid-cols-2">' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-emerald-700">I Tuoi Obblighi Fiscali</h3>' +
                    '<div class="space-y-4">' +
                    '<div class="p-4 rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Se sei un Privato</h4><p class="mt-2 text-gray-700">Per vendite occasionali, devi emettere una <a href="#glossary-ricevuta-prestazione-occasionale" class="glossary-link">ricevuta per prestazione occasionale</a> e dichiarare il reddito come "reddito diverso". Se l\'attività diventa abituale, è obbligatorio aprire <a href="#glossary-partita-iva" class="glossary-link">Partita IVA</a>.</p></div>' +
                    '<div class="p-4 rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Se hai <a href="#glossary-partita-iva" class="glossary-link">Partita IVA</a></h4><p class="mt-2 text-gray-700">È obbligatoria la <a href="#glossary-fatturazione-elettronica" class="glossary-link">fatturazione elettronica</a> per ogni incasso ricevuto, applicando il tuo regime fiscale e l\'<a href="#glossary-iva" class="glossary-link">IVA</a>, se prevista.</p></div>' +
                    '</div>' +
                    '<h3 class="mt-6 mb-4 text-2xl font-bold text-emerald-700">Strumenti a Tua Disposizione</h3>' +
                    '<ul class="space-y-2 text-gray-700 list-disc list-inside">' +
                    '<li><a href="#glossary-dashboard" class="glossary-link">Dashboard</a> con <a href="#glossary-report" class="glossary-link">report</a> dettagliato delle vendite.</li>' +
                    '<li><a href="#glossary-export-csv" class="glossary-link">Esportazione dati in CSV/XML</a> per la tua contabilità.</li>' +
                    '<li><a href="#glossary-alert-fiscale" class="glossary-link">Alert automatici</a> al raggiungimento di soglie fiscali.</li>' +
                    '<li>Modelli di ricevuta/fattura scaricabili.</li>' +
                    '</ul>' +
                    '</div>' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-center text-emerald-700">Flusso di Incasso per <a href="#glossary-creator" class="glossary-link">Creator</a></h3>' +
                    '<div class="p-6 bg-white border border-gray-200 rounded-lg shadow-md">' +
                    '<div class="flex flex-col items-center space-y-4 text-center">' +
                    '<div class="w-full p-4 bg-blue-100 rounded-lg"><p class="font-semibold">1. Vendita <a href="#glossary-egi" class="glossary-link">EGI</a></p><p class="text-sm">Un utente acquista il tuo <a href="#glossary-egi" class="glossary-link">EGI</a>.</p></div>' +
                    '<div class="text-2xl font-bold transform rotate-90 text-emerald-500">&#10230;</div>' +
                    '<div class="w-full p-4 rounded-lg bg-emerald-100"><p class="font-semibold">2. Accredito Diretto</p><p class="text-sm">L\'importo (al netto della <a href="#glossary-fee" class="glossary-link">fee</a> di piattaforma) viene inviato istantaneamente.</p></div>' +
                    '<div class="text-2xl font-bold transform rotate-90 text-emerald-500">&#10230;</div>' +
                    '<div class="w-full p-4 bg-green-100 rounded-lg"><p class="font-semibold">3. Il Tuo <a href="#glossary-wallet" class="glossary-link">Wallet</a></p><p class="text-sm">Ricevi i fondi direttamente sul tuo <a href="#glossary-wallet" class="glossary-link">wallet</a>, senza intermediari.</p></div>' +
                    '<div class="text-2xl font-bold transform rotate-90 text-emerald-500">&#10230;</div>' +
                    '<div class="w-full p-4 bg-yellow-100 rounded-lg"><p class="font-semibold">4. <a href="#glossary-compliance" class="glossary-link">Compliance</a> Fiscale</p><p class="text-sm">Emetti fattura/ricevuta e dichiari il reddito.</p></div>' +
                    '</div>' +
                    '<p class="mt-4 text-sm text-center text-gray-600"><strong>Ricorda:</strong> Sei l\'unico responsabile della tua dichiarazione fiscale. FlorenceEGI non è un <a href="#glossary-sostituto-imposta" class="glossary-link">sostituto d\'imposta</a>.</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
            },
            epp: {
                title: 'Gestione Fiscale per EPP',
                nav: 'Ente / EPP',
                intro: 'Questa area è dedicata agli EPP (Environmental Protection Project). Spiega come gli enti, sia piccoli no profit che grandi organizzazioni, ricevono i fondi, quali sono i loro obblighi specifici riguardo le donazioni (con un focus sull\'emissione delle ricevute) e come la piattaforma facilita la gestione e la reportistica senza intervenire nei flussi finanziari.',
                content: '<div class="grid gap-8 md:grid-cols-2">' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-emerald-700">Gestione Donazioni</h3>' +
                    '<div class="space-y-4">' +
                    '<div class="p-4 rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Piccoli Enti No Profit (<a href="#glossary-ets" class="glossary-link">ETS</a>/<a href="#glossary-onlus" class="glossary-link">ONLUS</a>)</h4><p class="mt-2 text-gray-700">Non devono emettere <a href="#glossary-fatturazione-elettronica" class="glossary-link">fattura elettronica</a> per le donazioni. Devono rilasciare una <a href="#glossary-ricevuta-donazione" class="glossary-link">ricevuta di donazione</a> (anche cumulativa annuale) solo su richiesta del donatore. I fondi sono ricevuti direttamente sul <a href="#glossary-wallet" class="glossary-link">wallet</a> dell\'ente.</p></div>' +
                    '<div class="p-4 rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Grandi Enti e Aziende</h4><p class="mt-2 text-gray-700">Gestiscono la <a href="#glossary-compliance" class="glossary-link">compliance</a> internamente tramite i propri sistemi contabili (<a href="#glossary-erp" class="glossary-link">ERP</a>, <a href="#glossary-crm" class="glossary-link">CRM</a>). FlorenceEGI fornisce <a href="#glossary-report" class="glossary-link">report</a> ed <a href="#glossary-export-csv" class="glossary-link">export</a> dei dati per facilitare l\'integrazione, ma la responsabilità della documentazione fiscale resta dell\'ente.</p></div>' +
                    '</div>' +
                    '</div>' +
                    '<div>' +
                    '<h3 class="mb-4 text-2xl font-bold text-emerald-700">Gestione Ricevute di Donazione</h3>' +
                    '<ul class="p-4 space-y-3 text-gray-700 list-disc list-inside rounded-lg bg-gray-50">' +
                    '<li>L\'emissione è obbligatoria <strong>solo su richiesta</strong> del donatore (<a href="#glossary-mecenate" class="glossary-link">mecenate</a>).</li>' +
                    '<li>Si raccomanda l\'emissione di una <a href="#glossary-ricevuta-donazione" class="glossary-link">ricevuta cumulativa</a> (annuale o mensile) per semplificare la gestione.</li>' +
                    '<li>L\'<a href="#glossary-epp" class="glossary-link">EPP</a> può abilitare la <strong>generazione automatica</strong> delle ricevute tramite la piattaforma.</li>' +
                    '<li>Il donatore può scaricare la ricevuta dalla propria <a href="#glossary-dashboard" class="glossary-link">dashboard</a>.</li>' +
                    '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mt-8">' +
                    '<h4 class="mb-4 text-xl font-bold text-center text-emerald-700">Flusso di Donazione per <a href="#glossary-epp" class="glossary-link">EPP</a></h4>' +
                    '<div class="flex flex-col items-center justify-center gap-4 text-center md:flex-row md:flex-wrap">' +
                    '<div class="p-4 bg-blue-100 rounded-lg shadow-sm"><p class="font-semibold">Donazione Utente</p><p class="text-sm">Tramite acquisto <a href="#glossary-egi" class="glossary-link">EGI</a></p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 rounded-lg shadow-sm bg-emerald-100"><p class="font-semibold">Accredito Diretto</p><p class="text-sm">La quota di donazione è inviata</p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 bg-green-100 rounded-lg shadow-sm"><p class="font-semibold"><a href="#glossary-wallet" class="glossary-link">Wallet</a> <a href="#glossary-epp" class="glossary-link">EPP</a></p><p class="text-sm">L\'ente riceve i fondi</p></div>' +
                    '<div class="text-2xl font-bold rotate-90 text-emerald-600 md:rotate-0">&#10230;</div>' +
                    '<div class="p-4 bg-yellow-100 rounded-lg shadow-sm"><p class="font-semibold"><a href="#glossary-ricevuta-donazione" class="glossary-link">Ricevuta</a> (su richiesta)</p><p class="text-sm">L\'ente emette la ricevuta</p></div>' +
                    '</div>' +
                    '</div>'
            },
            trader: {
                title: 'Gestione Fiscale per Trader e Alto Flusso',
                nav: 'Trader Pro',
                intro: 'Il trading ad alto volume presenta sfide fiscali uniche. Questa sezione spiega come FlorenceEGI affronta questa complessità attraverso l\'automazione, la fatturazione cumulativa per le fee di piattaforma e la gestione semplificata delle micro-donazioni agli EPP, garantendo la tracciabilità senza ostacolare l\'operatività.',
                content: '<h3 class="mb-4 text-2xl font-bold text-emerald-700"><a href="#glossary-compliance" class="glossary-link">Compliance</a> per il Trading <a href="#glossary-egi" class="glossary-link">EGI</a></h3>' +
                    '<div class="grid gap-8 md:grid-cols-2">' +
                    '<div class="p-6 rounded-lg bg-gray-50">' +
                    '<h4 class="mb-3 text-lg font-semibold text-gray-800">Gestione <a href="#glossary-fee" class="glossary-link">Fee</a> di Piattaforma</h4>' +
                    '<p class="mb-3 text-gray-700">Per gestire l\'alto numero di micro-transazioni, la piattaforma adotta un sistema di <a href="#glossary-fatturazione-batch" class="glossary-link">fatturazione cumulativa batch</a>.</p>' +
                    '<ul class="space-y-2 text-gray-700 list-disc list-inside">' +
                    '<li>Riceverai <strong>una sola <a href="#glossary-fatturazione-elettronica" class="glossary-link">fattura elettronica</a> periodica</strong> (es. mensile) per tutte le <a href="#glossary-fee" class="glossary-link">fee</a> maturate.</li>' +
                    '<li>Alla fattura sarà allegato un <a href="#glossary-report" class="glossary-link">report</a> con il dettaglio di ogni singola transazione.</li>' +
                    '</ul>' +
                    '</div>' +
                    '<div class="p-6 rounded-lg bg-gray-50">' +
                    '<h4 class="mb-3 text-lg font-semibold text-gray-800">Gestione Donazioni agli <a href="#glossary-epp" class="glossary-link">EPP</a></h4>' +
                    '<p class="mb-3 text-gray-700">Anche le micro-donazioni derivanti da migliaia di trade sono gestite in modo semplice.</p>' +
                    '<ul class="space-y-2 text-gray-700 list-disc list-inside">' +
                    '<li>Puoi richiedere una <a href="#glossary-ricevuta-donazione" class="glossary-link">ricevuta di donazione cumulativa</a> (annuale/mensile) all\'<a href="#glossary-epp" class="glossary-link">EPP</a>.</li>' +
                    '<li>La richiesta e il download possono essere automatizzati tramite la tua <a href="#glossary-dashboard" class="glossary-link">dashboard</a>.</li>' +
                    '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="p-6 mt-8 border-l-4 rounded-r-lg border-amber-400 bg-amber-50">' +
                    '<h4 class="font-bold text-amber-800">Responsabilità sulla <a href="#glossary-plusvalenza" class="glossary-link">Plusvalenza</a></h4>' +
                    '<p class="mt-2 text-amber-700"><strong>Attenzione:</strong> FlorenceEGI NON si occupa della fiscalità delle transazioni tra utenti. Sei tu il responsabile della dichiarazione dei tuoi guadagni (<a href="#glossary-plusvalenza" class="glossary-link">plusvalenze</a> o altri proventi) secondo il tuo regime fiscale. La piattaforma fornisce solo la reportistica completa per facilitare i tuoi calcoli.</p>' +
                    '</div>'
            },
            internazionale: {
                title: 'Gestione IVA e Fiscalità Internazionale',
                nav: 'IVA e Internaz.',
                intro: 'Operando in un contesto globale, la corretta gestione dell\'IVA è fondamentale. Questa sezione illustra come FlorenceEGI gestisce l\'IVA sulle proprie commissioni in base alla residenza dell\'utente (Italia, UE, Extra-UE), sfruttando i regimi OSS/MOSS e l\'automazione per garantire la compliance cross-border.',
                content: '<h3 class="mb-4 text-2xl font-bold text-emerald-700">Gestione <a href="#glossary-iva" class="glossary-link">IVA</a> sulle <a href="#glossary-fee" class="glossary-link">Fee</a> della Piattaforma</h3>' +
                    '<p class="mb-6 text-gray-600">L\'applicazione dell\'<a href="#glossary-iva" class="glossary-link">IVA</a> sulle <a href="#glossary-fee" class="glossary-link">fee</a> di FlorenceEGI dipende dalla tua residenza fiscale e dal tuo status (privato o azienda). Ecco come funziona:</p>' +
                    '<div class="space-y-4">' +
                    '<div class="p-4 border rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Utenti residenti in Italia</h4><p class="mt-2 text-gray-700">Viene applicata l\'aliquota <a href="#glossary-iva" class="glossary-link">IVA</a> ordinaria italiana su tutte le <a href="#glossary-fee" class="glossary-link">fee</a> incassate.</p></div>' +
                    '<div class="p-4 border rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Utenti residenti in Unione Europea</h4><ul class="mt-2 space-y-2 text-gray-700 list-disc list-inside"><li><strong>Privati:</strong> Viene applicata l\'<a href="#glossary-iva" class="glossary-link">IVA</a> del paese di residenza del consumatore, secondo il regime <a href="#glossary-oss" class="glossary-link">OSS (One Stop Shop)</a>.</li><li><strong>Aziende (con <a href="#glossary-partita-iva" class="glossary-link">Partita IVA</a> UE):</strong> Si applica il meccanismo del <a href="#glossary-reverse-charge" class="glossary-link">reverse charge</a>. La fattura viene emessa senza <a href="#glossary-iva" class="glossary-link">IVA</a>.</li></ul></div>' +
                    '<div class="p-4 border rounded-lg bg-gray-50"><h4 class="text-lg font-semibold text-gray-800">Utenti residenti Extra-UE</h4><p class="mt-2 text-gray-700">Generalmente, le fatture per le <a href="#glossary-fee" class="glossary-link">fee</a> vengono emesse senza <a href="#glossary-iva" class="glossary-link">IVA</a>. La transazione viene comunque <a href="#glossary-tracciabilita" class="glossary-link">tracciata</a> e segnalata secondo le normative vigenti.</p></div>' +
                    '</div>' +
                    '<div class="p-6 mt-8 border-l-4 border-blue-400 rounded-r-lg bg-blue-50">' +
                    '<h4 class="font-bold text-blue-800">Nota sulle Donazioni e sulle Vendite tra Utenti</h4>' +
                    '<p class="mt-2 text-blue-700">Le <strong>donazioni</strong> agli <a href="#glossary-epp" class="glossary-link">EPP</a> sono atti di liberalità e quindi <strong>non soggette a <a href="#glossary-iva" class="glossary-link">IVA</a></strong>. Per le <strong>vendite tra utenti</strong> (es. <a href="#glossary-creator" class="glossary-link">Creator</a> che vende <a href="#glossary-egi" class="glossary-link">EGI</a>), l\'applicazione dell\'<a href="#glossary-iva" class="glossary-link">IVA</a> dipende dal regime fiscale del venditore (<a href="#glossary-merchant" class="glossary-link">merchant</a>), che è responsabile della corretta fatturazione.</p>' +
                    '</div>'
            },
            merchant: {
                title: 'Merchant, Pagamenti e Fatturazione',
                nav: 'Merchant & Pagamenti',
                intro: 'FlorenceEGI consente la vendita di EGI a diversi soggetti: Creator, Mecenati, Aziende e Collector. Chiunque effettui una vendita diventa merchant ai fini operativi e fiscali della piattaforma.',
                content: '<div class="space-y-8">' +
                    '<details class="collapsible-section" open><summary><span class="text-xl material-icons">how_to_reg</span> Registrazione e Autenticazione</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Per ricevere corrispettivi, ogni <a href="#glossary-merchant" class="glossary-link">merchant</a> deve:</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Completare la registrazione con <strong>autenticazione forte</strong> (SPID, CIE, OTP o equivalente);</li><li>Fornire <strong>dati fiscali validi</strong> (<a href="#glossary-partita-iva" class="glossary-link">Partita IVA</a> o Codice Fiscale);</li><li>Accettare i termini di utilizzo e l\'informativa <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</li></ul></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">payment</span> Metodi di Pagamento</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Ogni <a href="#glossary-merchant" class="glossary-link">merchant</a> può abilitare uno o più metodi di pagamento:</p><ul class="space-y-3 text-gray-700 list-disc list-inside"><li><strong><a href="#glossary-fiat" class="glossary-link">FIAT</a></strong>, tramite <a href="#glossary-psp" class="glossary-link">Payment Service Provider (PSP)</a> convenzionati;</li><li><strong>Stablecoin</strong> su Algorand (USDCa, EURC);</li><li><strong>Criptovalute</strong> (ALGO, BTC, ETH) tramite <a href="#glossary-wallet" class="glossary-link">wallet</a> o <a href="#glossary-psp" class="glossary-link">PSP</a> dedicati.</li></ul><div class="p-4 mt-6 border-l-4 rounded-lg border-amber-600 bg-amber-50"><h5 class="mb-2 font-semibold text-amber-900">Responsabilità Merchant</h5><p class="text-sm text-amber-800">Il <a href="#glossary-merchant" class="glossary-link">merchant</a> è responsabile della corretta ricezione dei pagamenti e della gestione dei propri <a href="#glossary-wallet" class="glossary-link">wallet</a>. <strong>FlorenceEGI non custodisce chiavi private</strong>, non riceve fondi e non partecipa ai flussi di pagamento.</p></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">verified_user</span> Uso di PSP Autorizzati</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Il <a href="#glossary-merchant" class="glossary-link">merchant</a> può utilizzare <a href="#glossary-psp" class="glossary-link">PSP</a> esterni per la ricezione e conversione dei pagamenti in criptovaluta o stablecoin.</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>I rapporti contrattuali (KYC, AML, conversioni) sono gestiti <strong>direttamente tra merchant e <a href="#glossary-psp" class="glossary-link">PSP</a></strong>.</li><li>FlorenceEGI riceve esclusivamente la <strong>notifica di pagamento avvenuto</strong> per procedere alla registrazione dell\'<a href="#glossary-egi" class="glossary-link">EGI</a> su <a href="#glossary-blockchain" class="glossary-link">blockchain</a>.</li></ul></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">receipt_long</span> Fatturazione Elettronica</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Per ogni transazione completata, FlorenceEGI fornisce al <a href="#glossary-merchant" class="glossary-link">merchant</a> i dati necessari alla <a href="#glossary-fatturazione-elettronica" class="glossary-link">fatturazione elettronica</a>.</p><div class="p-4 mb-4 rounded-lg bg-gray-50"><h5 class="mb-2 font-semibold text-gray-800">Integrazione <a href="#glossary-sdi" class="glossary-link">SDI</a></h5><p class="text-sm text-gray-700">La piattaforma è integrata con un unico provider <a href="#glossary-sdi" class="glossary-link">SDI</a> accreditato, in grado di inviare e ricevere fatture XML conformi allo standard <strong>FatturaPA 1.6.1</strong>.</p></div><p class="mb-3 text-gray-700">Ogni <a href="#glossary-merchant" class="glossary-link">merchant</a> può:</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Utilizzare il <strong>sistema automatico di fatturazione</strong> fornito da FlorenceEGI,</li><li>Oppure ricevere il <strong>file XML</strong> per l\'invio tramite il proprio software di fatturazione.</li></ul><div class="p-4 mt-4 border-l-4 border-red-600 rounded-lg bg-red-50"><h5 class="mb-2 font-semibold text-red-900">Responsabilità Fiscale</h5><p class="text-sm text-red-800">Le fatture sono emesse <strong>in nome e per conto del <a href="#glossary-merchant" class="glossary-link">merchant</a></strong>, che rimane responsabile dei dati fiscali e degli adempimenti tributari. FlorenceEGI archivia le transazioni e genera report contabili e fiscali standard utili alla dichiarazione dei redditi.</p></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">policy</span> Trasparenza e Responsabilità</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Tutti i <a href="#glossary-merchant" class="glossary-link">merchant</a> devono dichiarare in modo chiaro i metodi di pagamento accettati e, se applicabile, il <a href="#glossary-psp" class="glossary-link">PSP</a> utilizzato.</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>FlorenceEGI mostra tali informazioni <strong>prima della conferma d\'acquisto</strong>.</li><li>La piattaforma <strong>non interviene nei flussi di denaro</strong> e non svolge funzioni di intermediazione finanziaria.</li></ul><div class="p-4 mt-6 border-l-4 border-green-600 rounded-lg bg-green-50"><h5 class="mb-2 font-semibold text-green-900">Conformità Normativa <a href="#glossary-mica-safe" class="glossary-link">MiCA-safe</a></h5><p class="text-sm text-green-800">FlorenceEGI rimane <strong>fuori dal perimetro <a href="#glossary-mica" class="glossary-link">MiCA</a></strong>, conforme a <strong>PSD2</strong> e allineata al <a href="#glossary-gdpr" class="glossary-link">GDPR</a>.</p></div></div></details>' +
                    '</div>'
            },
            rendicontazione: {
                title: 'Rendicontazione Fiscale e Conservazione Digitale',
                nav: 'Rendicontazione & Archiviazione',
                intro: 'FlorenceEGI garantisce la massima trasparenza fiscale attraverso un sistema completo di registrazione, rendicontazione automatica e conservazione digitale a norma di legge.',
                content: '<div class="space-y-8">' +
                    '<details class="collapsible-section" open><summary><span class="text-xl material-icons">fact_check</span> Registro delle Transazioni</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">FlorenceEGI mantiene un registro digitale di tutte le transazioni concluse sulla piattaforma, comprendente:</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Identificativo univoco dell\'<a href="#glossary-egi" class="glossary-link">EGI</a>;</li><li>Data e ora dell\'operazione;</li><li>Importo lordo, eventuali commissioni e quota <a href="#glossary-epp" class="glossary-link">EPP</a>;</li><li>Identificativi delle parti (acquirente e <a href="#glossary-merchant" class="glossary-link">merchant</a> verificato);</li><li>Modalità di pagamento e <a href="#glossary-psp" class="glossary-link">PSP</a> utilizzato;</li><li>Riferimento della fattura o documento contabile emesso.</li></ul><div class="p-4 mt-4 border-l-4 border-blue-600 rounded-lg bg-blue-50"><h5 class="mb-2 font-semibold text-blue-900">Sicurezza e Integrità</h5><p class="text-sm text-blue-800">Tutti i dati sono <strong>firmati digitalmente</strong> e conservati in archivi a <strong>prova di manomissione</strong> (<a href="#glossary-audit-trail" class="glossary-link">audit trail</a>).</p></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">assessment</span> Rendicontazione Automatica</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">La piattaforma genera in modo periodico:</p><ul class="space-y-3 text-gray-700 list-disc list-inside"><li><strong>Report trimestrali</strong> delle vendite per singolo <a href="#glossary-merchant" class="glossary-link">merchant</a>, in formato <a href="#glossary-export-csv" class="glossary-link">CSV e XML</a>;</li><li><strong>Report annuali riepilogativi</strong> utili alla dichiarazione dei redditi;</li><li><strong>Registro <a href="#glossary-iva" class="glossary-link">IVA</a></strong> (se applicabile) per le operazioni soggette a <a href="#glossary-fatturazione-elettronica" class="glossary-link">fatturazione elettronica</a>.</li></ul><div class="p-4 mt-4 rounded-lg bg-gray-50"><h5 class="mb-2 font-semibold text-gray-800">Accesso ai Documenti</h5><p class="text-sm text-gray-700">I documenti sono disponibili nell\'<strong>area riservata</strong> del <a href="#glossary-merchant" class="glossary-link">merchant</a> e possono essere esportati o inviati automaticamente al provider <a href="#glossary-sdi" class="glossary-link">SDI</a> integrato.</p></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">archive</span> Conservazione Digitale</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">FlorenceEGI garantisce la <strong>conservazione sostitutiva digitale</strong> dei documenti fiscali (fatture, ricevute, report) per un periodo minimo di <strong>10 anni</strong>, in conformità alle normative italiane vigenti (D.M. 17/06/2014 e Linee Guida AgID).</p><div class="grid gap-6 mt-4 md:grid-cols-2"><div class="p-4 rounded-lg bg-gray-50"><h5 class="mb-2 font-semibold text-gray-800">Provider Accreditato</h5><p class="text-sm text-gray-700">La conservazione avviene presso il provider <a href="#glossary-sdi" class="glossary-link">SDI</a> accreditato o, in alternativa, su infrastruttura cloud certificata.</p></div><div class="p-4 rounded-lg bg-gray-50"><h5 class="mb-2 font-semibold text-gray-800">Sicurezza Garantita</h5><ul class="text-sm text-gray-700 list-disc list-inside"><li>Firma digitale e marca temporale;</li><li>Replica geografica;</li><li>Verifica periodica d\'integrità (hash SHA-256).</li></ul></div></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">visibility</span> Accesso e Trasparenza</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Ogni <a href="#glossary-merchant" class="glossary-link">merchant</a> può accedere in tempo reale alla propria situazione contabile tramite l\'<strong>area amministrativa personale</strong>.</p><div class="p-4 mt-4 border-l-4 border-indigo-600 rounded-lg bg-indigo-50"><h5 class="mb-2 font-semibold text-indigo-900">Accesso Autorità Competenti</h5><p class="text-sm text-indigo-800">Le autorità competenti (Agenzia delle Entrate, Guardia di Finanza) possono richiedere l\'accesso ai dati fiscali mediante <strong>procedura autenticata</strong>, nel rispetto del <a href="#glossary-gdpr" class="glossary-link">GDPR</a> e delle norme sulla riservatezza commerciale.</p></div></div></details>' +
                    '<details class="collapsible-section"><summary><span class="text-xl material-icons">gavel</span> Ambito Normativo</summary><div class="collapsible-content"><p class="mb-4 text-gray-700">Il sistema di rendicontazione e conservazione è conforme a:</p><ul class="space-y-2 text-gray-700 list-disc list-inside"><li>Regolamento <a href="#glossary-mica" class="glossary-link">MiCA</a> (UE 2023/1114) — <strong>esclusione attività <a href="#glossary-casp" class="glossary-link">CASP</a></strong>;</li><li>Direttiva <strong>PSD2</strong> (UE 2015/2366) — nessuna intermediazione finanziaria;</li><li>Regolamento <a href="#glossary-gdpr" class="glossary-link">GDPR</a> (UE 2016/679);</li><li>Normativa italiana sulla <a href="#glossary-fatturazione-elettronica" class="glossary-link">fatturazione elettronica</a> e conservazione digitale.</li></ul></div></details>' +
                    '</div>'
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const roleNav = document.getElementById('role-nav');
            const contentContainer = document.getElementById('content-container');
            let chartInstance = null;

            const createNavButton = (id, text) => {
                const button = document.createElement('button');
                button.dataset.role = id;
                button.className =
                    'nav-item px-4 py-2 sm:px-5 sm:py-2.5 text-sm sm:text-base font-semibold text-gray-700 bg-gray-100 rounded-full hover:bg-emerald-600 hover:text-white hover:shadow-md';
                button.textContent = text;
                button.onclick = () => showSection(id);
                return button;
            };

            // Add Home button
            const homeButton = document.createElement('a');
            homeButton.href = '/';
            homeButton.className =
                'inline-flex items-center gap-2 px-5 py-3 font-medium text-white transition-all duration-200 ease-in-out transform bg-emerald-600 rounded-xl hover:bg-emerald-700 hover:scale-105 hover:shadow-lg nav-item';
            homeButton.innerHTML = '<span class="text-xl material-icons">home</span><span>Home</span>';
            roleNav.appendChild(homeButton);

            // Add FlorenceEGI Source Truth button
            const sourceTruthButton = document.createElement('a');
            sourceTruthButton.href = '{{ route('info.florenceegi-source-truth') }}';
            sourceTruthButton.className =
                'inline-flex items-center gap-2 px-5 py-3 font-medium text-white transition-all duration-200 ease-in-out transform bg-blue-600 rounded-xl hover:bg-blue-700 hover:scale-105 hover:shadow-lg nav-item';
            sourceTruthButton.innerHTML =
                '<span class="text-xl material-icons">description</span><span>Source Truth</span>';
            roleNav.appendChild(sourceTruthButton);

            Object.keys(contentData).forEach(key => {
                const sectionData = contentData[key];
                roleNav.appendChild(createNavButton(key, sectionData.nav));
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

            const showSection = (roleId) => {
                document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
                document.getElementById(`section-${roleId}`).classList.add('active');
                document.querySelectorAll('#role-nav button').forEach(el => el.classList.remove('active-nav'));
                document.querySelector(`#role-nav button[data-role='${roleId}']`).classList.add('active-nav');
                window.scrollTo(0, 0);
                if (roleId === 'panoramica') renderChart();
            };

            const renderChart = () => {
                const ctx = document.getElementById('responsibilityChart');
                if (!ctx) return;
                if (chartInstance) chartInstance.destroy();
                chartInstance = new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Responsabilità dell\'Utente (Creator, EPP, Trader)',
                            'Responsabilità della Piattaforma (FlorenceEGI)'
                        ],
                        datasets: [{
                            data: [85, 15],
                            backgroundColor: ['rgb(16, 185, 129)', 'rgb(209, 213, 219)'],
                            borderColor: '#fdfcfb',
                            borderWidth: 4,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    boxWidth: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (c) => `${c.label}: ${c.parsed}%`
                                }
                            }
                        }
                    }
                });
            }
            showSection('panoramica');

            // === LOGICA TORNA AL TESTO ===
            let previousSection = null;
            let previousScrollPosition = 0;
            const backButton = document.getElementById('backToTextButton');
            const backButtonLabel = document.getElementById('backToTextLabel');

            // Intercetta click sui glossary links
            document.addEventListener('click', (e) => {
                const glossaryLink = e.target.closest('.glossary-link');
                if (glossaryLink && glossaryLink.getAttribute('href')?.startsWith('#glossary-')) {
                    // Salva la sezione attiva corrente
                    const activeSection = document.querySelector('.content-section.active');
                    if (activeSection) {
                        previousSection = activeSection.id.replace('section-', '');
                        previousScrollPosition = window.scrollY;

                        // Trova il nome della sezione per il pulsante
                        const sectionData = contentData[previousSection];
                        if (sectionData) {
                            backButtonLabel.textContent = `Torna a ${sectionData.nav}`;
                        }
                    }
                }
            });

            // Mostra/nascondi pulsante quando si è in una voce di glossario
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash;
                if (hash.startsWith('#glossary-') && previousSection) {
                    // Sei in una voce di glossario
                    backButton.classList.add('show');
                } else {
                    // Non sei in una voce di glossario
                    backButton.classList.remove('show');
                    previousSection = null;
                }
            });

            // Gestisci click sul pulsante Torna al Testo
            backButton.addEventListener('click', () => {
                if (previousSection) {
                    // Torna alla sezione precedente
                    showSection(previousSection);

                    // Riporta allo scroll precedente con un piccolo delay per permettere il render
                    setTimeout(() => {
                        window.scrollTo({
                            top: previousScrollPosition,
                            behavior: 'smooth'
                        });
                    }, 100);

                    // Nascondi pulsante
                    backButton.classList.remove('show');

                    // Rimuovi hash dall'URL
                    history.pushState('', document.title, window.location.pathname + window.location
                        .search);
                }
            });

            // Check iniziale se siamo già in una voce di glossario (es. deep link)
            if (window.location.hash.startsWith('#glossary-')) {
                // Non possiamo sapere da dove veniva, quindi offriamo ritorno a panoramica
                previousSection = 'panoramica';
                backButtonLabel.textContent = 'Torna a Panoramica';
                backButton.classList.add('show');
            }
        });
    </script>
</body>

</html>
