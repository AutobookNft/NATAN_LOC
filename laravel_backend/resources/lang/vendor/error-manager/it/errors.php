<?php

declare(strict_types=1);

/**
 * @package App\Lang\Vendor\ErrorManager
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-20
 * @purpose Traduzioni italiane per error-manager::errors.*
 */

return [
    'dev' => [
        // Generic errors
        'undefined_error_code' => 'Codice di errore non definito: :code',
        'fatal_fallback_failure' => 'Errore fatale nel sistema di gestione errori. Impossibile utilizzare il fallback.',
        'unexpected_error' => 'Errore inaspettato: :message',
        'generic_server_error' => 'Errore generico del server. Dettagli: :message',
        'json_error' => 'Errore di parsing JSON: :message',
        'invalid_input' => 'Input non valido: :field = :value',

        // Auth errors
        'authentication_error' => 'Errore di autenticazione. Utente non autenticato.',
        'authorization_error' => 'Errore di autorizzazione. Utente :user_id non ha i permessi necessari per :action',
        'csrf_token_mismatch' => 'Token CSRF non valido o scaduto.',

        // Routing errors
        'route_not_found' => 'Route non trovata: :route',
        'resource_not_found' => 'Risorsa non trovata: :resource con ID :id',
        'method_not_allowed' => 'Metodo HTTP non consentito: :method sulla route :route',
        'too_many_requests' => 'Troppe richieste da IP :ip. Rate limit superato.',

        // Database errors
        'database_error' => 'Errore database: :message',
        'record_not_found' => 'Record non trovato nella tabella :table con condizioni :conditions',

        // GDPR errors
        'gdpr_activity_log_error' => 'Errore durante la registrazione dell\'attività GDPR per utente :user_id: :message',
        'gdpr_audit_stats_error' => 'Errore durante il calcolo delle statistiche GDPR per utente :user_id: :message',
        'gdpr_audit_purge_error' => 'Errore durante la rimozione dei log GDPR obsoleti: :message',
        'gdpr_violation_attempt' => 'Tentativo di violazione GDPR rilevato. Utente: :user_id, Azione: :action, Dettagli: :details',
        'gdpr_notification_dispatch_failed' => 'Errore durante l\'invio della notifica GDPR: :message',
        'gdpr_consent_history_failed' => 'Errore durante la registrazione dello storico consensi per utente :user_id: :message',
        'terms_acceptance_check_failed' => 'Errore durante la verifica dell\'accettazione dei termini per utente :user_id: :message',

        // GDPR Export errors
        'gdpr_export_history_failed' => 'Errore durante il recupero dello storico export GDPR per utente :user_id: :message',
        'gdpr_export_invalid_format' => 'Formato export non valido richiesto: :format. Formati supportati: :supported',
        'gdpr_export_invalid_categories' => 'Categorie export non valide richieste: :categories. Categorie disponibili: :available',
        'gdpr_export_generation_failed' => 'Errore durante la generazione dell\'export GDPR per utente :user_id: :message',
        'gdpr_export_processing_failed' => 'Errore durante l\'elaborazione dell\'export GDPR ID :export_id: :message',
        'gdpr_export_not_ready' => 'Export GDPR ID :export_id non ancora pronto. Stato attuale: :status',
        'gdpr_export_expired' => 'Export GDPR ID :export_id scaduto il :expired_at',
        'gdpr_export_file_not_found' => 'File export GDPR ID :export_id non trovato nel database',
        'gdpr_export_file_not_found_on_disk' => 'File export GDPR ID :export_id non trovato sul disco. Percorso atteso: :path',
        'gdpr_export_download_failed' => 'Errore durante il download dell\'export GDPR ID :export_id: :message',
        'gdpr_export_cleanup_failed' => 'Errore durante la pulizia dei file export scaduti: :message',

        // GDPR Processing Restrictions errors
        'gdpr_processing_restriction_limit_reached' => 'Limite restrizioni di elaborazione GDPR raggiunto per utente :user_id. Massimo consentito: :limit',
        'gdpr_processing_restriction_create_error' => 'Errore durante la creazione della restrizione di elaborazione GDPR per utente :user_id: :message',
        'gdpr_processing_restriction_remove_error' => 'Errore durante la rimozione della restrizione di elaborazione GDPR ID :restriction_id: :message',
        'gdpr_expired_restriction_process_error' => 'Errore durante l\'elaborazione delle restrizioni GDPR scadute: :message',
        'gdpr_notification_class_invalid' => 'Classe notifica GDPR non valida: :class. Classi disponibili: :available',
        'gdpr_restriction_notification_error' => 'Errore durante l\'invio della notifica per restrizione GDPR ID :restriction_id: :message',
    ],

    'user' => [
        // Generic errors
        'undefined_error_code' => 'Si è verificato un errore imprevisto. Il nostro team è stato notificato.',
        'fatal_fallback_failure' => 'Si è verificato un errore critico. Il nostro team tecnico è stato allertato.',
        'unexpected_error' => 'Si è verificato un errore imprevisto. Riprova più tardi o contatta l\'assistenza.',
        'generic_server_error' => 'Si è verificato un problema sul server. Il nostro team tecnico è stato notificato.',
        'json_error' => 'Errore durante l\'elaborazione dei dati. Riprova.',
        'invalid_input' => 'I dati inseriti non sono validi. Controlla i campi e riprova.',

        // Auth errors
        'authentication_error' => 'Non sei autenticato. Effettua l\'accesso per continuare.',
        'authorization_error' => 'Non hai i permessi necessari per eseguire questa azione.',
        'csrf_token_mismatch' => 'La sessione è scaduta. Ricarica la pagina e riprova.',

        // Routing errors
        'route_not_found' => 'Pagina non trovata.',
        'unexpected_error' => 'Risorsa non trovata.',
        'method_not_allowed' => 'Operazione non consentita.',
        'too_many_requests' => 'Hai effettuato troppe richieste. Attendi qualche minuto prima di riprovare.',

        // Database errors
        'database_error' => 'Si è verificato un problema temporaneo. Riprova tra qualche istante.',
        'record_not_found' => 'La risorsa richiesta non è stata trovata.',

        // GDPR errors
        'gdpr_activity_log_error' => 'Si è verificato un problema durante la registrazione dell\'attività. L\'azione è stata comunque completata.',
        'gdpr_audit_stats_error' => 'Si è verificato un problema durante il calcolo delle statistiche. Riprova più tardi.',
        'gdpr_audit_purge_error' => 'Si è verificato un problema durante la pulizia dei dati. Il nostro team è stato notificato.',
        'generic_internal_error' => 'Si è verificato un errore interno. Il nostro team è stato notificato e risolverà il problema al più presto.',
        'gdpr_notification_dispatch_failed' => 'Si è verificato un problema durante l\'invio della notifica. Riprova più tardi.',
        'gdpr_consent_history_failed' => 'Si è verificato un problema durante la registrazione dello storico consensi. L\'azione è stata comunque completata.',
        'generic_error' => 'Si è verificato un errore. Riprova più tardi.',

        // GDPR Export errors
        'gdpr_export_history_failed' => 'Non è stato possibile recuperare lo storico delle esportazioni. Riprova più tardi.',
        'gdpr_export_invalid_format' => 'Il formato richiesto non è valido. Scegli un formato supportato.',
        'gdpr_export_invalid_categories' => 'Le categorie selezionate non sono valide. Controlla e riprova.',
        'gdpr_export_generation_failed' => 'Si è verificato un problema durante la generazione dell\'esportazione dei dati. Il nostro team è stato notificato.',
        'gdpr_export_processing_failed' => 'Si è verificato un problema durante l\'elaborazione dell\'esportazione. Riprova più tardi.',
        'gdpr_export_not_ready' => 'L\'esportazione è ancora in elaborazione. Attendi qualche momento e riprova.',
        'gdpr_export_expired' => 'L\'esportazione richiesta non è più disponibile. Richiedi una nuova esportazione.',
        'gdpr_export_file_not_found' => 'Il file di esportazione richiesto non è stato trovato. Richiedi una nuova esportazione.',
        'gdpr_export_file_not_found_on_disk' => 'Il file di esportazione non è più disponibile. Richiedi una nuova esportazione.',
        'gdpr_export_download_failed' => 'Si è verificato un problema durante il download del file. Riprova più tardi.',
        'gdpr_export_cleanup_failed' => 'Si è verificato un problema durante la pulizia dei file. Il nostro team è stato notificato.',

        // GDPR Processing Restrictions errors
        'gdpr_processing_restriction_limit_reached' => 'Hai raggiunto il numero massimo di restrizioni di elaborazione consentite. Rimuovi una restrizione esistente per crearne una nuova.',
        'gdpr_processing_restriction_create_error' => 'Si è verificato un problema durante la creazione della restrizione. Riprova più tardi.',
        'gdpr_processing_restriction_remove_error' => 'Si è verificato un problema durante la rimozione della restrizione. Riprova più tardi.',
        'gdpr_expired_restriction_process_error' => 'Si è verificato un problema durante l\'elaborazione delle restrizioni scadute. Il nostro team è stato notificato.',
        'gdpr_notification_class_invalid' => 'Si è verificato un problema nella configurazione delle notifiche. Il nostro team è stato notificato.',
        'gdpr_restriction_notification_error' => 'Si è verificato un problema durante l\'invio della notifica. Il nostro team è stato notificato.',
    ],
];

