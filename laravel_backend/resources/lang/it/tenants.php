<?php

/**
 * @package Resources\Lang\It
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Traduzioni per gestione tenant (PA & Enterprises)
 */

return [
    // Messaggi di successo
    'created_successfully' => 'Tenant creato con successo',
    'updated_successfully' => 'Tenant aggiornato con successo',
    'deleted_successfully' => 'Tenant eliminato con successo',
    
    // Labels form
    'name' => 'Nome',
    'slug' => 'Slug',
    'code' => 'Codice',
    'entity_type' => 'Tipo entità',
    'email' => 'Email',
    'phone' => 'Telefono',
    'address' => 'Indirizzo',
    'vat_number' => 'P.IVA / Codice Fiscale',
    'is_active' => 'Attivo',
    'trial_ends_at' => 'Fine trial',
    'subscription_ends_at' => 'Fine sottoscrizione',
    'notes' => 'Note',
    
    // Entity types
    'entity_type_pa' => 'Pubblica Amministrazione',
    'entity_type_company' => 'Azienda Privata',
    'entity_type_public_entity' => 'Ente Pubblico',
    'entity_type_other' => 'Altro',
    
    // Placeholders
    'name_placeholder' => 'Es: Comune di Firenze',
    'slug_placeholder' => 'Es: firenze',
    'code_placeholder' => 'Codice identificativo opzionale',
    'email_placeholder' => 'email@esempio.it',
    'phone_placeholder' => '+39 055 1234567',
    'address_placeholder' => 'Indirizzo completo',
    'vat_number_placeholder' => 'P.IVA o Codice Fiscale',
    
    // Actions
    'create' => 'Crea Tenant',
    'edit' => 'Modifica Tenant',
    'delete' => 'Elimina Tenant',
    'view' => 'Visualizza Tenant',
    'save' => 'Salva',
    'cancel' => 'Annulla',
    'back' => 'Indietro',
    'search' => 'Cerca tenant...',
    'filter' => 'Filtra',
    'reset' => 'Reimposta filtri',
    'sort_by_name' => 'Nome',
    'sort_by_date' => 'Data creazione',
    'sort_by_type' => 'Tipo entità',
    'sort_asc' => 'Crescente',
    'sort_desc' => 'Decrescente',
    
    // Stats labels
    'total' => 'Totale',
    'active' => 'Attivi',
    'inactive' => 'Inattivi',
    'pa_entities' => 'Enti PA',
    'companies' => 'Aziende',
    
    // Detail labels
    'details' => 'Dettagli Tenant',
    'statistics' => 'Statistiche',
    'users_count' => 'Utenti',
    'documents_count' => 'Documenti',
    'conversations_count' => 'Conversazioni',
    'messages_count' => 'Messaggi',
    'memories_count' => 'Memorie',
    
    // Validation messages
    'slug_required' => 'Lo slug è obbligatorio',
    'slug_unique' => 'Questo slug è già in uso',
    'slug_format' => 'Lo slug può contenere solo lettere minuscole, numeri e trattini',
    'name_required' => 'Il nome è obbligatorio',
    'entity_type_required' => 'Il tipo entità è obbligatorio',
];
