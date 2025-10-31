<?php

/**
 * @package Resources\Lang\It
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 3.0.0 (FlorenceEGI - N.A.T.A.N. Web Search Integration)
 * @date 2025-10-26
 * @purpose Traduzioni sistema chat AI N.A.T.A.N. (PA/Enterprise)
 */

return [
    // === GENERAL ===
    'open_assistant' => 'Apri N.A.T.A.N. - Assistente AI',
    
    // === CHAT INTERFACE ===
    'chat.title' => 'N.A.T.A.N. - Consulenza Strategica AI',
    'chat.subtitle' => 'Interroga i tuoi documenti amministrativi con intelligenza artificiale',
    'chat.input_placeholder' => 'Chiedi qualcosa ai tuoi documenti... (Shift+Enter per inviare)',
    'chat.send_button' => 'Invia',
    'chat.send_aria' => 'Invia messaggio',
    'chat.clear_chat' => 'Nuova conversazione',
    'chat.clear_aria' => 'Cancella cronologia chat',

    // === WEB SEARCH TOGGLE (NEW v3.0) ===
    'web_search.toggle_label' => 'Cerca anche sul web',
    'web_search.toggle_hint' => 'Arricchisci le risposte con best practices globali, normative aggiornate e opportunità di finanziamento',
    'web_search.enabled' => 'Ricerca web attiva',
    'web_search.disabled' => 'Ricerca web disattivata',
    'web_search.tooltip' => 'Include fonti esterne da internet (best practices, normative, funding)',

    // === WEB SOURCES DISPLAY ===
    'web_sources.title' => 'Fonti Esterne (Web)',
    'web_sources.count' => '{count} risultati dal web',
    'web_sources.from_cache' => '(Risultati cache)',
    'web_sources.provider' => 'Provider:',
    'web_sources.relevance' => 'Rilevanza:',
    'web_sources.open_link' => 'Apri fonte',
    'web_sources.copy_url' => 'Copia URL',

    // === PERSONA SELECTOR ===
    'persona.select_title' => 'Seleziona Consulente',
    'persona.auto_mode' => 'Automatico (consigliato)',
    'persona.auto_hint' => 'N.A.T.A.N. sceglie il consulente più adatto per la tua domanda',
    'persona.manual_mode' => 'Manuale',
    'persona.confidence' => 'Confidenza',
    'persona.selected_by' => 'Selezionato',
    'persona.reasoning' => 'Motivazione',
    'persona.alternatives' => 'Alternative disponibili',

    // === PERSONAS NAMES ===
    'persona.strategic' => 'Consulente Strategico',
    'persona.technical' => 'Esperto Tecnico',
    'persona.legal' => 'Consulente Legale',
    'persona.financial' => 'Analista Finanziario',
    'persona.urban_social' => 'Urbanista/Social Impact',
    'persona.communication' => 'Esperto Comunicazione',

    // === QUICK ACTIONS ===
    'actions.title' => 'Elabora questa risposta',
    'actions.simplify' => 'Semplifica',
    'actions.deepen' => 'Approfondisci',
    'actions.actionable' => 'Azioni concrete',
    'actions.presentation' => 'Per presentazione',
    'actions.citizens' => 'Per cittadini',

    // === SOURCES ===
    'sources.title' => 'Fonti',
    'sources.internal' => 'Documenti Interni',
    'sources.external' => 'Fonti Web',
    'sources.count' => '{count} documenti analizzati',
    'sources.collapse' => 'Nascondi fonti',
    'sources.expand' => 'Mostra fonti',
    'sources.view_document' => 'Visualizza documento',
    'sources.blockchain_verified' => 'Verificato blockchain',

    // === FREE CHAT ===
    'free_chat.title' => 'Chat Libera con AI',
    'free_chat.subtitle' => 'Consulenza generale senza vincoli di documenti',
    'free_chat.placeholder' => 'Chiedi qualsiasi cosa...',

    // === SUGGESTIONS ===
    'suggestions.title' => 'Domande strategiche suggerite',
    'suggestions.refresh' => 'Altre domande',
    'suggestions.loading' => 'Caricamento suggerimenti...',

    // === MESSAGES ===
    'messages.thinking' => 'Sto analizzando...',
    'messages.searching_docs' => 'Ricerca tra i documenti in corso...',
    'messages.searching_web' => 'Ricerca sul web in corso...',
    'messages.generating' => 'Generazione risposta...',
    'messages.error' => 'Si è verificato un errore. Riprova.',
    'messages.empty_response' => 'Nessuna risposta generata.',
    'messages.no_sources' => 'Nessun documento trovato per questa query.',

    // === COPY FEATURE ===
    'copy.button' => 'Copia',
    'copy.success' => 'Copiato!',
    'copy.aria' => 'Copia risposta negli appunti',

    // === FEEDBACK ===
    'feedback.helpful' => 'Questa risposta è stata utile?',
    'feedback.yes' => 'Sì, utile',
    'feedback.no' => 'No, non utile',
    'feedback.thanks' => 'Grazie per il feedback!',

    // === ERRORS ===
    'errors.generic' => 'Si è verificato un errore. Riprova tra poco.',
    'errors.no_api_key' => 'Configurazione API mancante. Contatta l\'amministratore.',
    'errors.rate_limit' => 'Troppi richieste. Attendi qualche secondo.',
    'errors.rate_limit_exhausted' => 'Il servizio AI è momentaneamente sotto carico. Per favore riprova tra 2-3 minuti. Ci scusiamo per l\'inconveniente.',
    'errors.invalid_query' => 'La domanda non è valida. Fornisci più dettagli.',
    'errors.no_results' => 'Nessun risultato trovato.',
    'errors.web_search_failed' => 'Ricerca web non riuscita. Continua solo con documenti interni.',
    'errors.insufficient_credits' => 'Crediti insufficienti: hai :balance crediti, ma ne servono :required per questa analisi.',
    'errors.ai_consent_required' => 'È richiesto il consenso per l\'elaborazione AI. Aggiorna le tue impostazioni privacy per usare N.A.T.A.N.',

    // === GDPR & PRIVACY ===
    'gdpr.data_info' => 'I tuoi dati sono trattati in modo sicuro e conforme GDPR',
    'gdpr.no_pii_sent' => 'Nessun dato personale viene inviato a servizi esterni',
    'gdpr.audit_trail' => 'Tutte le interazioni sono registrate per audit',

    // === CHAT HISTORY (NEW v3.1) ===
    'history.title' => 'Cronologia Conversazioni',
    'history.subtitle' => 'Le tue sessioni passate con N.A.T.A.N.',
    'history.empty' => 'Nessuna conversazione precedente',
    'history.empty_hint' => 'Inizia una nuova conversazione per vederla apparire qui',
    'history.session_count' => '{count} conversazioni|{count} conversazione|{count} conversazioni',
    'history.load_session' => 'Carica conversazione',
    'history.delete_session' => 'Elimina conversazione',
    'history.delete_confirm' => 'Sei sicuro di voler eliminare questa conversazione? L\'operazione è irreversibile.',
    'history.deleted_success' => 'Conversazione eliminata con successo',
    'history.deleted_error' => 'Errore durante l\'eliminazione. Riprova.',
    'history.session_date' => 'Sessione del {date}',
    'history.message_count' => '{count} messaggi',
    'history.first_message' => 'Prima domanda:',
    'history.with_persona' => 'con {persona}',
    'history.toggle' => 'Mostra/Nascondi Cronologia',
    'history.loading' => 'Caricamento cronologia...',
    'history.loading_session' => 'Caricamento conversazione...',
    'history.no_consent' => 'Consenso mancante per accedere alla cronologia',
    'history.unauthorized' => 'Non autorizzato ad accedere a questa conversazione',
    'history.new_conversation' => 'Nuova Conversazione',

    // === CONFIGURATION PANEL (Superadmin) ===
    'config' => [
        'title' => 'Configurazione NATAN AI',
        'confirm_reset' => 'Sei sicuro di voler ripristinare i valori predefiniti?',
        'reset_defaults' => 'Ripristina Predefiniti',
        'save_changes' => 'Salva Modifiche',
        'updated_successfully' => 'Configurazione NATAN aggiornata con successo.',
        'reset_successfully' => 'Configurazione NATAN ripristinata ai valori predefiniti.',

        'sections' => [
            'claude_limits' => 'Limiti API Claude',
            'token_management' => 'Gestione Token',
            'user_controls' => 'Controlli Utente',
            'estimation' => 'Stima Costi e Tempi',
            'quality' => 'Qualità e Strategia',
            'system' => 'Funzionalità Sistema',
        ],

        'claude_context_limit' => 'Limite Contesto Claude',
        'claude_context_limit_minimum' => 'Limite Minimo Contesto',
        'max_tokens_per_call' => 'Token Massimi per Chiamata',
        'reserved_tokens_system' => 'Token Riservati Sistema',
        'reserved_tokens_output' => 'Token Riservati Output',
        'avg_tokens_per_char' => 'Token Medi per Carattere',
        'slider_min_acts' => 'Minimo Atti (Slider)',
        'slider_max_acts' => 'Massimo Atti (Slider)',
        'slider_default_acts' => 'Atti Predefiniti (Slider)',
        'cost_per_chunk' => 'Costo per Chunk (EUR)',
        'cost_aggregation' => 'Costo Aggregazione (EUR)',
        'time_per_chunk_seconds' => 'Tempo per Chunk (secondi)',
        'time_aggregation_seconds' => 'Tempo Aggregazione (secondi)',
        'min_relevance_score' => 'Score Minimo Rilevanza',
        'chunking_strategy' => 'Strategia Chunking',
        'enable_progress_tracking' => 'Abilita Progress Bar Real-Time',
        'rate_limit_max_retries' => 'Tentativi Massimi Rate Limit',
        'rate_limit_initial_delay_seconds' => 'Ritardo Iniziale Rate Limit (secondi)',

        'help' => [
            'claude_context_limit' => 'Numero massimo di atti da inviare a Claude in una singola chiamata (5-500).',
            'claude_context_limit_minimum' => 'Limite minimo prima di abbandonare il retry in caso di rate limit (1-50).',
            'max_tokens_per_call' => 'Margine di sicurezza sulla context window di Claude (200k tokens).',
            'reserved_tokens_system' => 'Spazio riservato per prompt di sistema e istruzioni NATAN.',
            'reserved_tokens_output' => 'Spazio riservato per la generazione della risposta di Claude.',
            'avg_tokens_per_char' => 'Media empirica: circa 4 caratteri = 1 token per testo italiano.',
            'slider_min_acts' => 'Valore minimo che l\'utente può scegliere con lo slider.',
            'slider_max_acts' => 'Valore massimo che l\'utente può scegliere con lo slider.',
            'slider_default_acts' => 'Valore predefinito dello slider quando l\'utente apre la ricerca.',
            'cost_per_chunk' => 'Costo stimato in EUR per ogni chunk processato da Claude.',
            'cost_aggregation' => 'Costo stimato in EUR per la chiamata finale di aggregazione.',
            'time_per_chunk_seconds' => 'Tempo medio in secondi per processare un chunk.',
            'time_aggregation_seconds' => 'Tempo medio in secondi per aggregare i risultati.',
            'min_relevance_score' => 'Score minimo (0.0-1.0) per includere un atto nei risultati. Valori più alti = più selettivi.',
            'chunking_strategy' => 'Strategia per suddividere grandi dataset: token-based (affidabile), relevance-based (qualità), adaptive (ML).',
            'rate_limit_max_retries' => 'Numero massimo di tentativi in caso di rate limit Anthropic.',
            'rate_limit_initial_delay_seconds' => 'Ritardo iniziale prima del primo retry (poi exponential backoff).',
        ],

        'strategies' => [
            'token_based' => 'Token-Based (Affidabile)',
            'relevance_based' => 'Relevance-Based (Qualità)',
            'adaptive' => 'Adaptive (Machine Learning)',
        ],
    ],

    // === INTELLIGENT CHUNKING SYSTEM (NEW v4.0) ===
    'no_acts_found' => 'Nessun atto trovato corrispondente alla ricerca.',
    'analysis_ready_to_start' => 'Analisi pronta per essere avviata.',

    'errors' => [
        'search_preview_failed' => 'Errore durante la ricerca preliminare. Riprova.',
        'analysis_failed' => 'Errore durante l\'analisi. Riprova.',
    ],

    // === INTELLIGENT CHUNKING - Frontend Errors (Phase 3) ===
    'chunking' => [
        'timeout_error' => 'L\'elaborazione sta richiedendo più tempo del previsto (>5 minuti)',
        'session_not_found' => 'Sessione di elaborazione non trovata. Riprova.',
        'unauthorized' => 'Non hai i permessi per accedere a questa sessione di elaborazione.',
        'polling_error' => 'Errore durante il controllo dello stato di elaborazione',
        'final_error' => 'Errore durante il recupero del risultato finale',
        'retry_button' => 'Riprova Analisi',
    ],

    // === USE PIPELINE (Ultra Semantic Engine) ===
    'use' => [
        'no_claims' => 'Nessun claim verificato disponibile.',
        'inference_badge' => '[Deduzione]',
        'no_sources' => 'Nessuna fonte disponibile per questo claim.',
        'sources' => 'Fonti',
        'unknown_source' => 'Fonte sconosciuta',
        'source_page' => '(p. :page)',
        'urs_breakdown' => 'Dettagli URS',
        'blocked_claims_title' => 'Claim Bloccati',
        'blocked_claims_explanation' => 'I seguenti claim sono stati bloccati perché non soddisfano i requisiti di affidabilità minimi (URS < 0.5).',
        'urs_label_a' => 'Affidabilità Alta',
        'urs_label_b' => 'Affidabilità Media-Alta',
        'urs_label_c' => 'Affidabilità Media',
        'urs_label_x' => 'Affidabilità Bassa',
    ],
];
