<?php

return [
    // System & UI Labels
    'system_title' => 'NATAN_LOC',
    'system_response_title' => 'NATAN Analysis System',
    'welcome_message' => 'System ready for analysis. Enter a query or select a strategic prompt.',
    'input_placeholder' => 'Enter a query or select a strategic prompt...',
    'execute_button' => 'EXECUTE',
    'shift_enter_hint' => 'Shift+Enter for new line',
    'process_console_title' => 'Process Console',
    'tenant_label' => 'TENANT',
    'no_tenant' => 'NO TENANT',
    'rag_status_label' => 'RAG-FORTRESS',
    'rag_status_active' => 'ACTIVE',

    'chat' => [
        'input_placeholder' => 'Type a message...',
        'send_button' => 'Send',
        'send_aria' => 'Send message',
    ],

    'sidebar' => [
        'chat' => 'Chat',
    ],

    'history' => [
        'title' => 'Chat History',
        'empty' => 'No previous conversations',
        'previous' => 'Previous (:count)',
        'untitled' => 'Untitled conversation',
    ],

    'suggestions' => [
        'title' => 'Suggested questions',
        'prompt_recommended' => 'Recommended Prompts',
    ],

    'persona' => [
        'select_title' => 'Persona',
        'auto_mode' => 'Auto',
        'strategic' => 'Strategic Consultant',
        'technical' => 'Technical Expert',
        'legal' => 'Legal & Administrative Consultant',
        'financial' => 'Financial Analyst',
        'urban_social' => 'Urban Planner & Social Impact Specialist',
        'communication' => 'Communication & PR Specialist',
        'archivist' => 'Archivist & Information Retrieval Specialist',
    ],

    'panel' => [
        'title' => 'Questions & Suggestions',
    ],

    'tabs' => [
        'questions' => 'Questions',
        'explanations' => 'Explanations',
    ],

    'questions' => [
        'title' => 'Strategic Questions',
        'close' => 'Close',
        'suggested' => 'Suggested',
        'all' => 'All',
        'all_categories' => 'All Categories',
        'no_suggestions' => 'No suggested questions available',
        'no_questions' => 'No questions available',
        'strategic_library' => 'Strategic Library',
    ],

    'categories' => [
        'strategic' => 'Strategic',
        'technical' => 'Technical',
        'financial' => 'Financial',
        'legal' => 'Legal',
        'urban_social' => 'Urban & Social',
        'communication' => 'Communication',
        'search_classification' => 'Search & Classification',
        'temporal_analysis' => 'Temporal Analysis',
        'compliance_normativa' => 'Compliance & Regulatory',
        'anomalies_control' => 'Anomalies & Control',
        'synthesis_reporting' => 'Synthesis & Reporting',
        'relationships_links' => 'Relationships & Links',
        'decision_support' => 'Decision Support',
        'power_questions' => 'Power Questions',
    ],

    'explanations' => [
        'title' => 'Explanations and Guides',
        'description' => 'Learn how NATAN works and its features.',
        'use' => [
            'title' => 'Ultra Semantic Engine (USE)',
            'description' => 'Anti-hallucination system that generates only verified responses with accredited sources. It doesn\'t imagine. It proves.',
        ],
    ],

    'use' => [
        'no_claims' => 'No verified claims available',
        'inference_badge' => '[Inference]',
        'no_sources' => 'No sources available',
        'sources' => 'Sources',
        'unknown_source' => 'Unknown source',
        'source_page' => '(p. :page)',
        'page_number' => '(p. :page)',
        'urs_breakdown' => 'URS Breakdown',
        'blocked_claims_title' => 'Blocked Claims',
        'blocked_claims_explanation' => 'The following statements were blocked because they do not meet the minimum reliability threshold (URS < 0.5).',
    ],

    'explanations' => [
        'urs' => [
            'title' => 'Ultra Reliability Score (URS)',
            'description' => 'Reliability score from A (maximum) to X (unverified) for each statement, based on sources and logical consistency.',
        ],
        'claims' => [
            'title' => 'Verified Claims',
            'description' => 'Each response is broken down into atomic statements, each verified with documentary sources and URS calculation.',
        ],
    ],

    'consultants' => [
        'title' => 'Specialized Consultants',
        'description' => 'Choose a NATAN persona for specialized responses in the selected domain.',
        'coming_soon' => 'Coming soon',
        'strategic_desc' => 'Strategic analysis and business decisions',
        'technical_desc' => 'Technical support and implementations',
        'legal_desc' => 'Legal aspects and regulatory compliance',
        'financial_desc' => 'Financial analysis and budgets',
    ],

    'trust' => [
        'zero_leak' => 'Zero-Leak Perimeter',
        'multi_tenant' => 'Multi-Tenant Isolated',
        'verified' => 'Verified',
        'blockchain' => 'Blockchain Anchored',
    ],

    'memory_label' => 'Memory',
    'memory_tooltip' => 'Conversations in memory',
    'secure_label' => 'Secure',
    'zero_leak_label' => 'Zero-Leak',

    'console' => [
        'interface_ready' => 'Interface ready.',
    ],

    'gdpr' => [
        'data_info' => 'Your data is protected and isolated',
    ],
    'errors' => [
        'authentication_required' => 'Authentication required. Please log in to continue.',
        'tenant_id_required' => 'Tenant ID required. Unable to determine current tenant.',
        'validation_failed' => 'Validation failed. Please check the entered fields.',
        'no_ai_consent' => 'AI processing consent not provided. Please accept consent to use this feature.',
        'superadmin_required' => 'Access denied. NATAN is only accessible to users with superadmin role.',
        'natan_access_required' => 'Access denied. NATAN_LOC is only accessible to PA roles (pa_entity, pa_entity_admin, admin, editor, superadmin).',
    ],

    'commands' => [
        'errors' => [
            'permission_denied' => 'Access denied: your role cannot execute this command.',
            'admin_required' => 'This command is restricted to tenant administrators (pa_entity_admin role).',
            'gateway_unreachable' => 'Command service temporarily unavailable. Please try again shortly.',
        ],
        'values' => [
            'blockchain_yes' => 'Anchored on blockchain',
            'blockchain_no' => 'Not anchored on blockchain',
            'no_title' => 'Untitled document',
            'no_limit' => 'no limit',
            'unknown_type' => 'Document type not specified',
        ],
        'links' => [
            'open_document' => 'Open document',
        ],
        'fields' => [
            'document_id' => 'Document ID',
            'protocol_number' => 'Protocol number',
            'protocol_date' => 'Protocol date',
            'document_type' => 'Type',
            'department' => 'Department',
            'blockchain_status' => 'Blockchain status',
            'count' => 'Total',
        ],
        'atto' => [
            'errors' => [
                'identifier_required' => 'Provide at least one identifier (e.g. numero=pa_act_xxx or protocollo=12345).',
            ],
            'messages' => [
                'not_found' => 'No document found (document_id: :document_id, protocol: :protocol_number).',
                'found' => 'Document found: **:title**',
            ],
        ],
        'atti' => [
            'messages' => [
                'empty' => 'No documents match the requested filters.',
                'found' => 'Results: **:count** (limit: :limit)',
            ],
        ],
        'stats' => [
            'errors' => [
                'unsupported_target' => 'Unsupported statistics target (:target).',
            ],
            'messages' => [
                'summary' => 'Total acts: **:count** (from: :from, to: :to)',
            ],
        ],
        'helper' => [
            'toggle_label' => 'Open legend and quick commands',
            'legend_title' => 'URS Legend',
            'legend_urs_a' => 'Maximum reliability: documents fully verified with accredited sources.',
            'legend_urs_b' => 'High reliability: solid sources, minor integrations may be required.',
            'legend_urs_c' => 'Medium reliability: relevant information with partial coverage.',
            'legend_urs_x' => 'Unverified: manual review is required before using the data.',
            'commands_title' => 'Quick commands',
            'commands_description' => 'Use the @ prefix to query the database directly without going through the AI.',
            'atto_hint' => 'Retrieve a specific document by document_id (e.g. numero=pa_act_...).',
            'atti_hint' => 'Filter acts by type, department and custom limits.',
            'atti_protocol_label' => '@atti protocol=',
            'atti_protocol_hint' => 'Find all acts with a specific protocol number.',
            'stats_hint' => 'Quick statistics on acts (administrators only).',
        ],
        'natural' => [
            'errors' => [
                'blacklisted' => 'Request blocked: the text contains forbidden terms. Remove them and try again.',
                'throttled' => 'You exceeded the allowed request limit. Please retry in :minutes minutes.',
                'parse_failed' => 'I could not interpret the request. Please be more specific.',
            ],
            'messages' => [
                'summary' => 'Results for ":query" (documents found: :count, limit: :limit).',
                'no_results' => 'No documents found for ":query".',
                'expanded_date_range' => 'No exact document in the requested period. The search was automatically extended by :days_before days before and :days_after days after.',
            ],
        ],
    ],
];
