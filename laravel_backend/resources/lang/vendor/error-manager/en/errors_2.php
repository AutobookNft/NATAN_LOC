<?php

declare(strict_types=1);

/**
 * @package App\Lang\Vendor\ErrorManager
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-20
 * @purpose English translations for error-manager::errors_2.* (USE, NATAN, RAG, Blockchain)
 */

return [
    'dev' => [
        // USE Pipeline errors
        'use_query_failed' => 'Error executing USE query. Service: :service, Method: :method, Tenant: :tenant_id, User: :user_id, Question: :question, Error: :message',
        'use_embedding_failed' => 'Error generating USE embedding. Service: :service, Method: :method, Tenant: :tenant_id, Error: :message',
        'use_audit_save_failed' => 'Error saving USE audit. Service: :service, Method: :method, Tenant: :tenant_id, User: :user_id, Answer ID: :answer_id, Error: :message',

        // NATAN errors
        'natan_api_call_failed' => 'Error calling NATAN API. Endpoint: :endpoint, Tenant: :tenant_id, User: :user_id, Error: :message',
        'natan_query_processing_failed' => 'Error processing NATAN query. Tenant: :tenant_id, User: :user_id, Query: :query, Error: :message',
        'natan_history_failed' => 'Error retrieving NATAN history. Tenant: :tenant_id, User: :user_id, Error: :message',
        'natan_session_retrieval_failed' => 'Error retrieving NATAN session. Session ID: :session_id, Tenant: :tenant_id, User: :user_id, Error: :message',
        'natan_session_delete_failed' => 'Error deleting NATAN session. Session ID: :session_id, Tenant: :tenant_id, User: :user_id, Error: :message',

        // RAG errors
        'rag_context_retrieval_failed' => 'Error retrieving RAG context. Tenant: :tenant_id, Query: :query, Error: :message',
        'rag_embedding_generation_failed' => 'Error generating RAG embedding. Tenant: :tenant_id, Text: :text_preview, Error: :message',

        // Blockchain errors
        'blockchain_anchor_failed' => 'Error anchoring to blockchain. Hash: :hash, Tenant: :tenant_id, User: :user_id, Error: :message',
        'pa_act_blockchain_anchor_failed' => 'Error anchoring PA document to blockchain. Document ID: :document_id, Tenant: :tenant_id, Hash: :hash_preview, Error: :message',
        'blockchain_network_error' => 'Blockchain network connection error. Network: :network, Error: :message',
        'blockchain_transaction_pool_error' => 'Blockchain transaction pool error. Transaction ID: :txid, Error: :message',
        'algorand_account_create_failed' => 'Error creating Algorand account. Tenant: :tenant_id, User: :user_id, Error: :message',
        'algorand_account_info_failed' => 'Error retrieving Algorand account information. Address: :address, Error: :message',

        // Microservice errors
        'microservice_not_reachable' => 'Microservice not reachable. URL: :url, Service: :service, Error: :message',
        'microservice_health_check_failed' => 'Microservice health check failed. URL: :url, Service: :service, Error: :message',
        'microservice_auto_start_failed' => 'Microservice auto-start attempt failed. Service: :service, Path: :path, Error: :message',
    ],

    'user' => [
        // USE Pipeline errors
        'use_query_failed' => 'An error occurred while processing your question. Please try again later.',
        'use_embedding_failed' => 'An error occurred while processing your request. Please try again later.',
        'use_audit_save_failed' => 'A problem occurred while saving data. The operation was still completed.',

        // NATAN errors
        'natan_api_call_failed' => 'An error occurred while communicating with the NATAN system. Our team has been notified.',
        'natan_query_processing_failed' => 'An error occurred while processing your request. Please try again later.',
        'natan_history_failed' => 'Unable to retrieve conversation history. Please try again later.',
        'natan_session_retrieval_failed' => 'Unable to retrieve the session. Please try again later.',
        'natan_session_delete_failed' => 'An error occurred while deleting the session. Please try again later.',

        // RAG errors
        'rag_context_retrieval_failed' => 'An error occurred while searching for information. Please try again later.',
        'rag_embedding_generation_failed' => 'A problem occurred while processing. Please try again later.',

        // Blockchain errors
        'blockchain_anchor_failed' => 'An error occurred while notarizing the document on blockchain. The document was still saved correctly.',
        'pa_act_blockchain_anchor_failed' => 'An error occurred while notarizing the document on blockchain. The document was still saved correctly and notarization will be retried automatically.',
        'blockchain_network_error' => 'The blockchain network is currently unreachable. Please try again later.',
        'blockchain_transaction_pool_error' => 'A problem occurred with the blockchain transaction pool. Please try again later.',
        'algorand_account_create_failed' => 'An error occurred while creating the Algorand account. Our team has been notified.',
        'algorand_account_info_failed' => 'Unable to retrieve Algorand account information. Please try again later.',

        // Microservice errors
        'microservice_not_reachable' => 'Service temporarily unavailable. Our team has been notified.',
        'microservice_health_check_failed' => 'Service temporarily unavailable. Please try again in a few minutes.',
        'microservice_auto_start_failed' => 'Service temporarily unavailable. Our team has been alerted and will resolve the issue as soon as possible.',
    ],
];


















