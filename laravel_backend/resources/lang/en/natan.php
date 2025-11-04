<?php

return [
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

    'gdpr' => [
        'data_info' => 'Your data is protected and isolated',
    ],
    'errors' => [
        'authentication_required' => 'Authentication required. Please log in to continue.',
        'tenant_id_required' => 'Tenant ID required. Unable to determine current tenant.',
        'validation_failed' => 'Validation failed. Please check the entered fields.',
        'no_ai_consent' => 'AI processing consent not provided. Please accept consent to use this feature.',
    ],
];
