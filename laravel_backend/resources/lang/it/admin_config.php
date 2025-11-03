<?php

/**
 * @package Resources\Lang\It
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Traduzioni configurazioni admin tenant-specific
 */

return [
    'title' => 'Configurazioni Tenant',
    'subtitle' => 'Gestisci le configurazioni specifiche per :tenant',
    'no_tenant' => 'Nessun tenant associato al tuo account.',
    'tenant_not_found' => 'Tenant non trovato.',
    'saved_successfully' => 'Configurazioni salvate con successo.',
    
    // Sections
    'ai_section' => 'Configurazioni AI',
    'scraping_section' => 'Configurazioni Web Scraping',
    'embeddings_section' => 'Configurazioni Embeddings',
    'notifications_section' => 'Notifiche',
    
    // AI Settings
    'ai_default_model' => 'Modello AI Predefinito',
    'ai_default_model_help' => 'Modello AI da utilizzare per default nelle conversazioni.',
    'ai_temperature' => 'Temperature',
    'ai_temperature_help' => 'Valore da 0.0 (deterministico) a 2.0 (molto creativo). Valore consigliato: 0.7',
    'ai_max_tokens' => 'Max Tokens',
    'ai_max_tokens_help' => 'Numero massimo di token nella risposta. Range: 1-32000',
    
    // Scraping Settings
    'scraping_enabled' => 'Abilita Web Scraping Automatico',
    'scraping_interval' => 'Intervallo Scraping (ore)',
    'scraping_interval_help' => 'Intervallo in ore tra gli scraping automatici.',
    
    // Embeddings Settings
    'embeddings_enabled' => 'Abilita Generazione Embeddings',
    'embeddings_model' => 'Modello Embeddings',
    'embeddings_model_help' => 'Modello da utilizzare per generare embeddings dei documenti.',
    
    // Notifications Settings
    'notifications_email' => 'Notifiche via Email',
    'notifications_slack' => 'Notifiche via Slack',
    
    // Actions
    'save' => 'Salva Configurazioni',
    'cancel' => 'Annulla',
];

