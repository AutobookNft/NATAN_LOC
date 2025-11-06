/**
 * TypeScript types for NATAN Frontend
 */

export interface Message {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    timestamp: Date;
    claims?: Claim[];
    blockedClaims?: Claim[];
    sources?: Source[];
    avgUrs?: number;
    verificationStatus?: 'SAFE' | 'WARNING' | 'BLOCKED' | 'direct_query' | 'DIRECT_QUERY';
    tokensUsed?: {
        input: number;
        output: number;
        total?: number;
        prompt?: number;
        completion?: number;
    } | null;
    modelUsed?: string | null;
}

export interface Claim {
    text: string;
    urs: number;
    ursLabel: 'A' | 'B' | 'C' | 'X';
    ursBreakdown?: UrsBreakdown;
    sourceRefs?: SourceRef[];
    isInference?: boolean;
}

export interface UrsBreakdown {
    coverage: number;
    reference_count: number;
    extractor_quality: number;
    date_coherence: number;
    out_of_domain: number;
    total: number;
}

export interface SourceRef {
    source_id?: string;
    url: string;
    title: string;
    type: string;
    page?: number;
    chunk_index?: number;
    document_id?: string;
    hash?: string;
}

export interface Source {
    id: string;
    url: string;
    title: string;
    type: 'internal' | 'external';
    relevance?: number;
}

export interface UseQueryResponse {
    status: 'success' | 'error' | 'blocked' | 'no_results';
    question: string;
    answer?: string;  // Main natural language answer (synthesized response)
    answer_id?: string;
    verified_claims?: Claim[];  // Verified claims with sources (proof)
    blocked_claims?: Claim[];
    avg_urs?: number;
    verification_status?: 'SAFE' | 'WARNING' | 'BLOCKED' | 'direct_query' | 'DIRECT_QUERY';
    chunks_used?: any[];
    model_used?: string;
    tokens_used?: {
        input: number;
        output: number;
    };
    message?: string;
    reason?: string;
}

export interface ApiConfig {
    baseUrl: string;
    apiToken?: string;
    timeout: number;
}







