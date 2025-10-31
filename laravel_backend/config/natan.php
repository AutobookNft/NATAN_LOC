<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NATAN Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for NATAN AI Assistant chat system
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Claude Context Limit
    |--------------------------------------------------------------------------
    |
    | ENTERPRISE STRATEGY: Search scans ALL sources (1M+ acts if present),
    | but only TOP N most relevant are sent to Claude API.
    |
    | This prevents rate limits while maintaining accuracy:
    | - Search: NO LIMIT (scan entire archive)
    | - Claude: TOP N by weighted similarity
    |
    | Values:
    | - 50: Safe for new accounts (25k tokens)
    | - 100: Recommended for production (50k tokens)
    | - 200: High accuracy mode (100k tokens)
    |
    | Note: 100 acts ≈ 50k tokens (Claude accepts 200k window)
    |
    */
    'claude_context_limit' => env('NATAN_CLAUDE_CONTEXT_LIMIT', 100),

    /*
    |--------------------------------------------------------------------------
    | Claude Context Limit - MINIMUM (Adaptive Retry)
    |--------------------------------------------------------------------------
    |
    | When rate_limit_error occurs, system automatically retries with reduced
    | context size. This is the MINIMUM limit before giving up.
    |
    | Retry sequence: 100 → 50 → 25 → 10 → 5
    |
    | Default: 5 (minimum viable context for meaningful response)
    |
    */
    'claude_context_limit_minimum' => env('NATAN_CLAUDE_CONTEXT_LIMIT_MINIMUM', 5),

    /*
    |--------------------------------------------------------------------------
    | Intelligent Chunking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for NATAN intelligent chunking system when dealing with
    | large datasets (thousands of public acts).
    |
    */

    /*
    | Maximum tokens per Claude API call
    | Default: 180000 (safety margin on Claude's 200k context window)
    | This accounts for system prompts, user query, and output generation.
    */
    'max_tokens_per_call' => env('NATAN_MAX_TOKENS_PER_CALL', 180000),

    /*
    | Reserved tokens for system prompts
    | Default: 2000
    | Space needed for NATAN system instructions and context.
    */
    'reserved_tokens_system' => env('NATAN_RESERVED_TOKENS_SYSTEM', 2000),

    /*
    | Reserved tokens for Claude output generation
    | Default: 8000
    | Space needed for Claude to generate comprehensive responses.
    */
    'reserved_tokens_output' => env('NATAN_RESERVED_TOKENS_OUTPUT', 8000),

    /*
    | Average tokens per character (empirical)
    | Default: 0.25 (4 characters ≈ 1 token for Italian text)
    | Used to estimate token count from text length.
    */
    'avg_tokens_per_char' => env('NATAN_AVG_TOKENS_PER_CHAR', 0.25),

    /*
    | Default slider limits for user-controlled analysis
    | User can choose how many acts to analyze via slider UI.
    */
    'slider_min_acts' => env('NATAN_SLIDER_MIN_ACTS', 50),
    'slider_max_acts' => env('NATAN_SLIDER_MAX_ACTS', 5000),
    'slider_default_acts' => env('NATAN_SLIDER_DEFAULT_ACTS', 500),

    /*
    | Cost estimation (EUR per API call)
    | Used to show user estimated cost before analysis.
    */
    'cost_per_chunk' => env('NATAN_COST_PER_CHUNK', 0.09),
    'cost_aggregation' => env('NATAN_COST_AGGREGATION', 0.03),

    /*
    | Time estimation (seconds per chunk)
    | Used to show user estimated time before analysis.
    */
    'time_per_chunk_seconds' => env('NATAN_TIME_PER_CHUNK_SECONDS', 10),
    'time_aggregation_seconds' => env('NATAN_TIME_AGGREGATION_SECONDS', 15),

    /*
    | Minimum relevance score for pre-filtering
    | Acts below this score are discarded before sending to Claude.
    | Range: 0.0 to 1.0 (0 = all acts, 1 = only perfect matches)
    */
    'min_relevance_score' => env('NATAN_MIN_RELEVANCE_SCORE', 0.3),

    /*
    | Chunking strategy
    | Available: 'token-based', 'relevance-based', 'adaptive'
    | Default: 'token-based' (most reliable)
    */
    'chunking_strategy' => env('NATAN_CHUNKING_STRATEGY', 'token-based'),

    /*
    | Enable progress tracking (WebSocket/polling)
    | Shows real-time progress bar to user during analysis.
    */
    'enable_progress_tracking' => env('NATAN_ENABLE_PROGRESS_TRACKING', true),

    /*
    | Rate limit backoff configuration
    | Exponential backoff when hitting Anthropic rate limits.
    */
    'rate_limit_max_retries' => env('NATAN_RATE_LIMIT_MAX_RETRIES', 3),
    'rate_limit_initial_delay_seconds' => env('NATAN_RATE_LIMIT_INITIAL_DELAY', 2),

    /*
    |--------------------------------------------------------------------------
    | Unified Knowledge Base (NEW v5.0)
    |--------------------------------------------------------------------------
    |
    | Enable unified knowledge retrieval system that merges all sources
    | (Acts, Web, Memory, Files) into a single semantic search.
    |
    | Benefits:
    | - Single source of truth for Claude (no prioritization conflicts)
    | - Automatic source citation in responses
    | - Better semantic relevance across all sources
    | - Intelligent caching (Acts 30d, Web 6h, Memory 7d, Files 90d)
    |
    | Set to TRUE to enable the new system.
    | Set to FALSE to use legacy RAG + Web Search (separate sources).
    |
    */
    'enable_unified_knowledge' => env('NATAN_ENABLE_UNIFIED_KNOWLEDGE', false),

];
