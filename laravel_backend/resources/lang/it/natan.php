<?php

return [
    'chat' => [
        'input_placeholder' => 'Scrivi un messaggio...',
        'send_button' => 'Invia',
        'send_aria' => 'Invia messaggio',
    ],
    
    'sidebar' => [
        'chat' => 'Chat',
    ],
    
    'history' => [
        'title' => 'Cronologia Chat',
        'empty' => 'Nessuna conversazione precedente',
        'previous' => 'Precedenti (:count)',
        'untitled' => 'Conversazione senza titolo',
    ],
    
    'suggestions' => [
        'title' => 'Domande suggerite',
    ],
    
    'persona' => [
        'select_title' => 'Persona',
        'auto_mode' => 'Auto',
        'strategic' => 'Consulente Strategico',
        'technical' => 'Esperto Tecnico',
        'legal' => 'Consulente Legale/Amministrativo',
        'financial' => 'Analista Finanziario',
        'urban_social' => 'Urbanista/Social Impact',
        'communication' => 'Esperto Comunicazione',
        'archivist' => 'Archivista/Documentalista',
    ],
    
    'panel' => [
        'title' => 'Domande e Suggerimenti',
    ],
    
    'tabs' => [
        'questions' => 'Domande',
        'explanations' => 'Spiegazioni',
    ],
    
    'questions' => [
        'title' => 'Domande Strategiche',
        'close' => 'Chiudi',
        'suggested' => 'Suggerite',
        'all' => 'Tutte',
        'all_categories' => 'Tutte le categorie',
        'no_suggestions' => 'Nessuna domanda suggerita disponibile',
        'no_questions' => 'Nessuna domanda disponibile',
    ],
    
    'categories' => [
        'strategic' => 'Strategia',
        'technical' => 'Tecnico',
        'financial' => 'Finanziario',
        'legal' => 'Legale',
        'urban_social' => 'Urban & Social',
        'communication' => 'Comunicazione',
        'search_classification' => 'Ricerca & Classificazione',
        'temporal_analysis' => 'Analisi Temporale',
        'compliance_normativa' => 'Compliance & Normativa',
        'anomalies_control' => 'Anomalie & Controllo',
        'synthesis_reporting' => 'Sintesi & Reporting',
        'relationships_links' => 'Relazioni & Collegamenti',
        'decision_support' => 'Supporto Decisionale',
        'power_questions' => 'Power Questions',
    ],
    
    'explanations' => [
        'title' => 'Spiegazioni e Guide',
        'description' => 'Approfondimenti su come funziona NATAN e le sue funzionalità.',
        'use' => [
            'title' => 'Ultra Semantic Engine (USE)',
            'description' => 'Sistema anti-allucinazione che genera solo risposte verificate con fonti accreditate. Non immagina. Dimostra.',
        ],
        'urs' => [
            'title' => 'Ultra Reliability Score (URS)',
            'description' => 'Punteggio di affidabilità da A (massima) a X (non verificato) per ogni affermazione, basato su fonti e coerenza logica.',
        ],
        'claims' => [
            'title' => 'Claim Verificati',
            'description' => 'Ogni risposta è scomposta in affermazioni atomiche, ognuna verificata con fonti documentali e calcolo URS.',
        ],
    ],
    
    'consultants' => [
        'title' => 'Consulenti Specializzati',
        'description' => 'Scegli una persona NATAN per risposte specializzate nel dominio selezionato.',
        'coming_soon' => 'Presto disponibile',
        'strategic_desc' => 'Analisi strategica e decisioni aziendali',
        'technical_desc' => 'Supporto tecnico e implementazioni',
        'legal_desc' => 'Aspetti legali e conformità normativa',
        'financial_desc' => 'Analisi finanziarie e budget',
    ],
    
    'trust' => [
        'zero_leak' => 'Zero-Leak Perimetrale',
        'multi_tenant' => 'Multi-Tenant Isolato',
        'verified' => 'Verificato',
        'blockchain' => 'Ancorato Blockchain',
    ],
    
    'gdpr' => [
        'data_info' => 'I tuoi dati sono protetti e isolati',
    ],
    
    'scrapers' => [
        'title' => 'Web Scrapers Python',
        'description' => 'Configura e gestisci l\'acquisizione automatica di atti pubblici da fonti web esterne con import MongoDB.',
        'stats' => [
            'total_scrapers' => 'Scraper Disponibili',
            'available' => 'Scraper Disponibili',
            'total_documents' => 'Documenti in MongoDB',
        ],
        'actions' => [
            'view' => 'Visualizza',
            'execute' => 'Esegui',
            'preview' => 'Anteprima',
        ],
        'empty' => [
            'title' => 'Nessun Scraper Configurato',
            'description' => 'Nessuno scraper Python disponibile al momento.',
        ],
        'gdpr' => [
            'title' => 'GDPR Compliance',
            'description' => 'Tutti gli scraper operano esclusivamente su dati pubblici resi disponibili dalle PA ai sensi del D.Lgs 33/2013 (Trasparenza Amministrativa). I campi PII vengono automaticamente sanitizzati prima del salvataggio.',
        ],
        'import' => [
            'title' => 'Import MongoDB',
            'description' => 'Configura ed esegui lo scraper Python con opzioni di dry-run, import MongoDB, download PDF e tracking costi.',
            'year_single' => 'Anno Singolo',
            'year_range' => 'Range Anni (es: 2023-2024)',
            'year_placeholder' => 'Es: 2024',
        ],
        'options' => [
            'dry_run' => 'Dry Run',
            'dry_run_desc' => 'Simula senza salvare',
            'mongodb_import' => 'Import MongoDB',
            'mongodb_import_desc' => 'Importa in MongoDB',
            'download_pdfs' => 'Download PDFs',
            'download_pdfs_desc' => 'Scarica file PDF',
            'tenant_id' => 'Tenant ID',
        ],
        'results' => [
            'title' => 'Risultati Esecuzione',
        ],
        'loading' => 'Esecuzione in corso...',
        'error' => [
            'title' => 'Errore',
        ],
        'info' => [
            'configuration' => 'Configurazione',
            'source' => 'Fonte',
            'description' => 'Descrizione',
            'base_url' => 'URL Base',
            'script' => 'Script Python',
            'gdpr' => 'Conformità GDPR',
            'legal_basis' => 'Base Legale',
            'data_type' => 'Tipo Dati',
            'public_data' => 'Dati Pubblici',
        ],
    ],
];
