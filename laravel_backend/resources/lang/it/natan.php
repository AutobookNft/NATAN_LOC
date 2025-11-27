<?php

return [
    // System & UI Labels
    'system_title' => 'NATAN_LOC',
    'system_response_title' => 'Sistema di Analisi NATAN',
    'welcome_message' => 'Sistema pronto per l\'analisi. Inserisci un quesito o seleziona un prompt strategico.',
    'input_placeholder' => 'Inserisci un quesito o seleziona un prompt strategico...',
    'execute_button' => 'ESEGUI',
    'shift_enter_hint' => 'Shift+Invio per nuova riga',
    'process_console_title' => 'Console Processi',
    'tenant_label' => 'TENANT',
    'no_tenant' => 'NESSUN TENANT',
    'rag_status_label' => 'RAG-FORTRESS',
    'rag_status_active' => 'ACTIVE',

    'chat' => [
        'input_placeholder' => 'Scrivi un messaggio...',
        'send_button' => 'Invia',
        'send_aria' => 'Invia messaggio',
    ],

    'sidebar' => [
        'chat' => 'Chat',
        'natan_chat' => 'CHAT NATAN',
        'infraufficio_chat' => 'CHAT INFRAUFFICIO',
        'infraufficio_bacheca' => 'BACHECA INFRA COMUNE',
    ],

    'context' => [
        'natan_chat' => 'Chat Natan',
        'infraufficio_chat' => 'Chat Infraufficio',
        'infraufficio_bacheca' => 'Bacheca Infra Comune',
    ],

    'history' => [
        'title' => 'Cronologia Chat',
        'empty' => 'Nessuna conversazione precedente',
        'previous' => 'Precedenti (:count)',
        'untitled' => 'Conversazione senza titolo',
        'new_chat' => 'Nuova Chat',
    ],

    'projects' => [
        'title' => 'Progetti',
        'empty' => 'Nessun progetto creato',
        'new_project' => 'Nuovo Progetto',
        'active' => 'Progetto Attivo',
        'create_title' => 'Nome Progetto',
        'create_description' => 'Descrizione (opzionale)',
        'create_button' => 'Crea',
        'cancel_button' => 'Annulla',
    ],

    'suggestions' => [
        'title' => 'Domande suggerite',
        'prompt_recommended' => 'Prompt Consigliati',
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
        'strategic_library' => 'Libreria Strategica',
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
    ],

    'use' => [
        'no_claims' => 'Nessuna claim verificata disponibile',
        'inference_badge' => '[Deduzione]',
        'no_sources' => 'Nessuna fonte disponibile',
        'sources' => 'Fonti',
        'unknown_source' => 'Fonte sconosciuta',
        'source_page' => '(p. :page)',
        'page_number' => '(p. :page)',
        'urs_breakdown' => 'Dettaglio URS',
        'blocked_claims_title' => 'Claim Bloccate',
        'blocked_claims_explanation' => 'Le seguenti affermazioni sono state bloccate perché non raggiungono il livello minimo di affidabilità (URS < 0.5).',
    ],

    'explanations' => [
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

    'memory_label' => 'Memoria',
    'memory_tooltip' => 'Conversazioni in memoria',
    'secure_label' => 'Secure',
    'zero_leak_label' => 'Zero-Leak',

    'console' => [
        'interface_ready' => 'Interfaccia pronta.',
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
    'errors' => [
        'authentication_required' => 'Autenticazione richiesta. Effettua il login per continuare.',
        'tenant_id_required' => 'Tenant ID richiesto. Impossibile determinare il tenant corrente.',
        'validation_failed' => 'Validazione fallita. Controlla i campi inseriti.',
        'no_ai_consent' => 'Consenso per il trattamento AI non fornito. Accetta il consenso per utilizzare questa funzionalità.',
        'superadmin_required' => 'Accesso negato. NATAN è accessibile solo agli utenti con ruolo superadmin.',
        'natan_access_required' => 'Accesso negato. NATAN_LOC è accessibile solo a ruoli PA (pa_entity, pa_entity_admin, admin, editor, superadmin).',
    ],

    'commands' => [
        'errors' => [
            'permission_denied' => 'Accesso negato: il tuo ruolo non consente di eseguire questo comando.',
            'admin_required' => 'Questo comando è riservato agli amministratori del tenant (ruolo pa_entity_admin).',
            'gateway_unreachable' => 'Servizio comandi non disponibile. Riprova tra qualche istante.',
        ],
        'values' => [
            'blockchain_yes' => 'Ancorato su blockchain',
            'blockchain_no' => 'Non ancorato su blockchain',
            'no_title' => 'Titolo non disponibile',
            'no_limit' => 'nessun limite',
            'unknown_type' => 'Tipo documento non specificato',
        ],
        'links' => [
            'open_document' => 'Apri documento',
        ],
        'fields' => [
            'document_id' => 'Document ID',
            'protocol_number' => 'Numero di protocollo',
            'protocol_date' => 'Data protocollo',
            'document_type' => 'Tipologia',
            'department' => 'Dipartimento',
            'blockchain_status' => 'Stato blockchain',
            'count' => 'Totale',
        ],
        'atto' => [
            'errors' => [
                'identifier_required' => 'Specifica almeno un identificativo (es. numero=pa_act_xxx oppure protocollo=12345).',
            ],
            'messages' => [
                'not_found' => 'Nessun documento trovato (document_id: :document_id, protocollo: :protocol_number).',
                'found' => 'Documento trovato: **:title**',
            ],
        ],
        'atti' => [
            'messages' => [
                'empty' => 'Nessun documento risponde ai criteri indicati.',
                'found' => 'Risultati: **:count** (limite: :limit)',
            ],
        ],
        'stats' => [
            'errors' => [
                'unsupported_target' => 'Tipo di statistica non supportato (:target).',
            ],
            'messages' => [
                'summary' => 'Totale atti: **:count** (dal: :from, al: :to)',
            ],
        ],
        'helper' => [
            'toggle_label' => 'Apri legenda e comandi rapidi',
            'legend_title' => 'Legenda URS',
            'legend_urs_a' => 'Affidabilità massima: documenti verificati con fonti complete.',
            'legend_urs_b' => 'Affidabilità alta: fonti solide, possibili integrazioni secondarie.',
            'legend_urs_c' => 'Affidabilità media: informazioni rilevanti ma con copertura parziale.',
            'legend_urs_x' => 'Non verificato: serve verifica manuale prima di utilizzare i dati.',
            'commands_title' => 'Comandi rapidi',
            'commands_description' => 'Usa i prefissi @ per interrogare direttamente il database senza passare dall’AI.',
            'atto_hint' => 'Recupera un documento specifico con document_id (es. numero=pa_act_...).',
            'atti_hint' => 'Filtra gli atti per tipologia, dipartimento e limiti personalizzati.',
            'atti_protocol_label' => '@atti protocollo=',
            'atti_protocol_hint' => 'Cerca tutti gli atti con uno specifico numero di protocollo.',
            'stats_hint' => 'Statistiche rapide sugli atti (solo amministratori).',
        ],
        'natural' => [
            'errors' => [
                'blacklisted' => 'Richiesta bloccata: il testo contiene termini non consentiti. Rimuovili e riprova.',
                'throttled' => 'Hai superato il limite di richieste consentite. Riprova tra :minutes minuti.',
                'parse_failed' => 'Non riesco a interpretare la richiesta. Prova a essere più specifico.',
            ],
            'messages' => [
                'summary' => 'Risultati per ":query" (documenti trovati: :count, limite: :limit).',
                'no_results' => 'Nessun documento trovato per ":query".',
                'expanded_date_range' => 'Nessun documento esatto nel periodo richiesto. Ho esteso automaticamente la ricerca di :days_before giorni prima e :days_after giorni dopo.',
            ],
        ],
    ],
];
