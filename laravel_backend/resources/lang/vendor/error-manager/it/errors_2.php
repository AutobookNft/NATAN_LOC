<?php

declare(strict_types=1);

/**
 * @package App\Lang\Vendor\ErrorManager
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-20
 * @purpose Traduzioni italiane per error-manager::errors_2.* (USE, NATAN, RAG, Blockchain)
 */

return [
    'dev' => [
        // USE Pipeline errors
        'use_query_failed' => 'Errore durante l\'esecuzione della query USE. Service: :service, Method: :method, Tenant: :tenant_id, User: :user_id, Question: :question, Errore: :message',
        'use_embedding_failed' => 'Errore durante la generazione dell\'embedding USE. Service: :service, Method: :method, Tenant: :tenant_id, Errore: :message',
        'use_audit_save_failed' => 'Errore durante il salvataggio dell\'audit USE. Service: :service, Method: :method, Tenant: :tenant_id, User: :user_id, Answer ID: :answer_id, Errore: :message',
        'use_proxy_failed' => 'Errore durante il proxy USE query. Tenant: :tenant_id, User: :user_id, Question: :question, Errore: :error_message',
        'use_embedding_proxy_failed' => 'Errore durante il proxy USE embedding. Tenant: :tenant_id, User: :user_id, Errore: :error_message',

        // NATAN errors
        'natan_api_call_failed' => 'Errore durante la chiamata API NATAN. Endpoint: :endpoint, Tenant: :tenant_id, User: :user_id, Errore: :message',
        'natan_query_processing_failed' => 'Errore durante l\'elaborazione della query NATAN. Tenant: :tenant_id, User: :user_id, Query: :query, Errore: :message',
        'natan_history_failed' => 'Errore durante il recupero dello storico NATAN. Tenant: :tenant_id, User: :user_id, Errore: :message',
        'natan_session_retrieval_failed' => 'Errore durante il recupero della sessione NATAN. Session ID: :session_id, Tenant: :tenant_id, User: :user_id, Errore: :message',
        'natan_session_delete_failed' => 'Errore durante l\'eliminazione della sessione NATAN. Session ID: :session_id, Tenant: :tenant_id, User: :user_id, Errore: :message',

        // RAG errors
        'rag_context_retrieval_failed' => 'Errore durante il recupero del contesto RAG. Tenant: :tenant_id, Query: :query, Errore: :message',
        'rag_embedding_generation_failed' => 'Errore durante la generazione dell\'embedding RAG. Tenant: :tenant_id, Text: :text_preview, Errore: :message',

        // Blockchain errors
        'blockchain_anchor_failed' => 'Errore durante l\'ancoraggio su blockchain. Hash: :hash, Tenant: :tenant_id, User: :user_id, Errore: :message',
        'pa_act_blockchain_anchor_failed' => 'Errore durante l\'ancoraggio del documento PA su blockchain. Document ID: :document_id, Tenant: :tenant_id, Hash: :hash_preview, Errore: :message',
        'blockchain_network_error' => 'Errore di connessione alla rete blockchain. Network: :network, Errore: :message',
        'blockchain_transaction_pool_error' => 'Errore nel pool delle transazioni blockchain. Transaction ID: :txid, Errore: :message',
        'algorand_account_create_failed' => 'Errore durante la creazione dell\'account Algorand. Tenant: :tenant_id, User: :user_id, Errore: :message',
        'algorand_account_info_failed' => 'Errore durante il recupero delle informazioni account Algorand. Address: :address, Errore: :message',

        // Microservice errors
        'microservice_not_reachable' => 'Microservizio non raggiungibile. URL: :url, Service: :service, Errore: :message',
        'microservice_health_check_failed' => 'Health check del microservizio fallito. URL: :url, Service: :service, Errore: :message',
        'microservice_auto_start_failed' => 'Tentativo di avvio automatico del microservizio fallito. Service: :service, Path: :path, Errore: :message',
    ],

    'user' => [
        // USE Pipeline errors
        'use_query_failed' => 'Si è verificato un errore durante l\'elaborazione della tua domanda. Riprova più tardi.',
        'use_embedding_failed' => 'Si è verificato un errore durante l\'elaborazione della richiesta. Riprova più tardi.',
        'use_audit_save_failed' => 'Si è verificato un problema durante il salvataggio dei dati. L\'operazione è stata comunque completata.',
        'use_proxy_failed' => 'Si è verificato un errore durante l\'elaborazione della tua domanda. Riprova più tardi.',
        'use_embedding_proxy_failed' => 'Si è verificato un errore durante l\'elaborazione della richiesta. Riprova più tardi.',

        // NATAN errors
        'natan_api_call_failed' => 'Si è verificato un errore durante la comunicazione con il sistema NATAN. Il nostro team è stato notificato.',
        'natan_query_processing_failed' => 'Si è verificato un errore durante l\'elaborazione della tua richiesta. Riprova più tardi.',
        'natan_history_failed' => 'Non è stato possibile recuperare lo storico delle conversazioni. Riprova più tardi.',
        'natan_session_retrieval_failed' => 'Non è stato possibile recuperare la sessione. Riprova più tardi.',
        'natan_session_delete_failed' => 'Si è verificato un errore durante l\'eliminazione della sessione. Riprova più tardi.',

        // RAG errors
        'rag_context_retrieval_failed' => 'Si è verificato un errore durante la ricerca delle informazioni. Riprova più tardi.',
        'rag_embedding_generation_failed' => 'Si è verificato un problema durante l\'elaborazione. Riprova più tardi.',

        // Blockchain errors
        'blockchain_anchor_failed' => 'Si è verificato un errore durante la notarizzazione del documento su blockchain. Il documento è stato comunque salvato correttamente.',
        'pa_act_blockchain_anchor_failed' => 'Si è verificato un errore durante la notarizzazione del documento su blockchain. Il documento è stato comunque salvato correttamente e la notarizzazione verrà riprovata automaticamente.',
        'blockchain_network_error' => 'La rete blockchain non è al momento raggiungibile. Riprova più tardi.',
        'blockchain_transaction_pool_error' => 'Si è verificato un problema con il pool delle transazioni blockchain. Riprova più tardi.',
        'algorand_account_create_failed' => 'Si è verificato un errore durante la creazione dell\'account Algorand. Il nostro team è stato notificato.',
        'algorand_account_info_failed' => 'Non è stato possibile recuperare le informazioni dell\'account Algorand. Riprova più tardi.',

        // Microservice errors
        'microservice_not_reachable' => 'Servizio temporaneamente non disponibile. Il nostro team è stato notificato.',
        'microservice_health_check_failed' => 'Servizio temporaneamente non disponibile. Riprova tra qualche minuto.',
        'microservice_auto_start_failed' => 'Servizio temporaneamente non disponibile. Il nostro team è stato allertato e risolverà il problema al più presto.',
    ],
];
