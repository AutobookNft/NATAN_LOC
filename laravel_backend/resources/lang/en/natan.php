<?php

/**
 * @package Resources\Lang\En
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 3.0.0 (FlorenceEGI - N.A.T.A.N. Web Search Integration)
 * @date 2025-10-26
 * @purpose N.A.T.A.N. AI chat system translations (PA/Enterprise)
 */

return [
    // === CHAT INTERFACE ===
    'chat.title' => 'N.A.T.A.N. - AI Strategic Consulting',
    'chat.subtitle' => 'Query your administrative documents with artificial intelligence',
    'chat.input_placeholder' => 'Ask something about your documents... (Shift+Enter to send)',
    'chat.send_button' => 'Send',
    'chat.send_aria' => 'Send message',
    'chat.clear_chat' => 'New conversation',
    'chat.clear_aria' => 'Clear chat history',

    // === WEB SEARCH TOGGLE (NEW v3.0) ===
    'web_search.toggle_label' => 'Search web too',
    'web_search.toggle_hint' => 'Enrich responses with global best practices, updated regulations and funding opportunities',
    'web_search.enabled' => 'Web search active',
    'web_search.disabled' => 'Web search disabled',
    'web_search.tooltip' => 'Include external sources from internet (best practices, regulations, funding)',

    // === WEB SOURCES DISPLAY ===
    'web_sources.title' => 'External Sources (Web)',
    'web_sources.count' => '{count} results from web',
    'web_sources.from_cache' => '(Cached results)',
    'web_sources.provider' => 'Provider:',
    'web_sources.relevance' => 'Relevance:',
    'web_sources.open_link' => 'Open source',
    'web_sources.copy_url' => 'Copy URL',

    // === PERSONA SELECTOR ===
    'persona.select_title' => 'Select Consultant',
    'persona.auto_mode' => 'Auto (recommended)',
    'persona.auto_hint' => 'N.A.T.A.N. chooses the most suitable consultant for your question',
    'persona.manual_mode' => 'Manual',
    'persona.confidence' => 'Confidence',
    'persona.selected_by' => 'Selected by',
    'persona.reasoning' => 'Reasoning',
    'persona.alternatives' => 'Available alternatives',

    // === PERSONAS NAMES ===
    'persona.strategic' => 'Strategic Consultant',
    'persona.technical' => 'Technical Expert',
    'persona.legal' => 'Legal Consultant',
    'persona.financial' => 'Financial Analyst',
    'persona.urban_social' => 'Urban Planner/Social Impact',
    'persona.communication' => 'Communication Expert',

    // === QUICK ACTIONS ===
    'actions.title' => 'Elaborate this response',
    'actions.simplify' => 'Simplify',
    'actions.deepen' => 'Deepen',
    'actions.actionable' => 'Concrete actions',
    'actions.presentation' => 'For presentation',
    'actions.citizens' => 'For citizens',

    // === SOURCES ===
    'sources.title' => 'Sources',
    'sources.internal' => 'Internal Documents',
    'sources.external' => 'Web Sources',
    'sources.count' => '{count} documents analyzed',
    'sources.collapse' => 'Hide sources',
    'sources.expand' => 'Show sources',
    'sources.view_document' => 'View document',
    'sources.blockchain_verified' => 'Blockchain verified',

    // === FREE CHAT ===
    'free_chat.title' => 'Free Chat with AI',
    'free_chat.subtitle' => 'General consulting without document constraints',
    'free_chat.placeholder' => 'Ask anything...',

    // === SUGGESTIONS ===
    'suggestions.title' => 'Suggested strategic questions',
    'suggestions.refresh' => 'Other questions',
    'suggestions.loading' => 'Loading suggestions...',

    // === MESSAGES ===
    'messages.thinking' => 'Analyzing...',
    'messages.searching_docs' => 'Searching documents...',
    'messages.searching_web' => 'Searching web...',
    'messages.generating' => 'Generating response...',
    'messages.error' => 'An error occurred. Please try again.',
    'messages.empty_response' => 'No response generated.',
    'messages.no_sources' => 'No documents found for this query.',

    // === COPY FEATURE ===
    'copy.button' => 'Copy',
    'copy.success' => 'Copied!',
    'copy.aria' => 'Copy response to clipboard',

    // === FEEDBACK ===
    'feedback.helpful' => 'Was this response helpful?',
    'feedback.yes' => 'Yes, helpful',
    'feedback.no' => 'No, not helpful',
    'feedback.thanks' => 'Thanks for your feedback!',

    // === ERRORS ===
    'errors.generic' => 'An error occurred. Please try again later.',
    'errors.no_api_key' => 'API configuration missing. Contact administrator.',
    'errors.rate_limit' => 'Too many requests. Please wait a few seconds.',
    'errors.rate_limit_exhausted' => 'The AI service is temporarily under heavy load. Please try again in 2-3 minutes. We apologize for the inconvenience.',
    'errors.invalid_query' => 'Invalid question. Please provide more details.',
    'errors.no_results' => 'No results found.',
    'errors.web_search_failed' => 'Web search failed. Continuing with internal documents only.',
    'errors.insufficient_credits' => 'Insufficient credits: you have :balance credits, but :required are needed for this analysis.',
    'errors.ai_consent_required' => 'AI processing consent required. Please update your privacy settings to use N.A.T.A.N.',

    // === GDPR & PRIVACY ===
    'gdpr.data_info' => 'Your data is processed securely and GDPR-compliant',
    'gdpr.no_pii_sent' => 'No personal data is sent to external services',
    'gdpr.audit_trail' => 'All interactions are logged for audit purposes',

    // === INTELLIGENT CHUNKING (Phase 3 - Frontend Integration) ===
    'chunking.timeout_error' => 'The processing is taking longer than expected (>5 minutes)',
    'chunking.session_not_found' => 'Processing session not found. Please try again.',
    'chunking.unauthorized' => 'You do not have permission to access this processing session.',
    'chunking.polling_error' => 'Error checking processing status',
    'chunking.final_error' => 'Error retrieving final result',
    'chunking.retry_button' => 'Retry Analysis',
    // === USE PIPELINE (Ultra Semantic Engine) ===
    'use' => [
        'no_claims' => 'No verified claims available.',
        'inference_badge' => '[Inference]',
        'no_sources' => 'No sources available for this claim.',
        'sources' => 'Sources',
        'unknown_source' => 'Unknown source',
        'source_page' => '(p. :page)',
        'urs_breakdown' => 'URS Breakdown',
        'blocked_claims_title' => 'Blocked Claims',
        'blocked_claims_explanation' => 'The following claims have been blocked because they do not meet minimum reliability requirements (URS < 0.5).',
        'urs_label_a' => 'High Reliability',
        'urs_label_b' => 'Medium-High Reliability',
        'urs_label_c' => 'Medium Reliability',
        'urs_label_x' => 'Low Reliability',
    ],
];
