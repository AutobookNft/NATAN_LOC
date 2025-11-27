/**
 * API Service for NATAN Frontend
 * Handles all HTTP requests to Laravel backend and Python FastAPI
 */

import type { UseQueryResponse, ApiConfig, ChatResponse } from '../types';

export class ApiService {
    private baseUrl: string;
    private apiToken: string | null = null;
    private timeout: number;

    constructor(config: ApiConfig) {
        this.baseUrl = config.baseUrl;
        this.apiToken = config.apiToken || null;
        this.timeout = config.timeout || 120000;
    }

    setApiToken(token: string): void {
        this.apiToken = token;
    }

    /**
     * Ensure all required services are running
     */
    private async ensureServices(): Promise<boolean> {
        try {
            // Try to call ensure-services endpoint (if backend is accessible)
            const response = await fetch(`${this.baseUrl}/api/v1/system/ensure-services`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                signal: AbortSignal.timeout(30000), // 30s timeout
            });
            return response.ok;
        } catch (error) {
            console.warn('Could not ensure services via API:', error);
            return false;
        }
    }

    private async fetchWithAuth(
        endpoint: string,
        options: RequestInit = {},
        retryOnConnectionError: boolean = true
    ): Promise<Response> {
        const url = `${this.baseUrl}${endpoint}`;
        const headers = new Headers(options.headers as HeadersInit | undefined);
        headers.set('Content-Type', 'application/json');
        headers.set('Accept', 'application/json');

        if (this.apiToken) {
            headers.set('Authorization', `Bearer ${this.apiToken}`);
        }

        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            headers.set('X-CSRF-TOKEN', csrfToken);
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);

        try {
            const response = await fetch(url, {
                ...options,
                headers,
                signal: controller.signal,
            });

            clearTimeout(timeoutId);
            return response;
        } catch (error) {
            clearTimeout(timeoutId);

            // If connection error and retry enabled, try to ensure services
            if (retryOnConnectionError && error instanceof TypeError &&
                (error.message.includes('Failed to fetch') ||
                    error.message.includes('ERR_CONNECTION_REFUSED') ||
                    error.name === 'TypeError')) {

                console.log('🔧 Connection error detected, attempting to ensure services...');

                try {
                    const servicesEnsured = await this.ensureServices();

                    if (servicesEnsured) {
                        console.log('✅ Services ensured, waiting for startup...');
                        // Wait for services to start (max 10 seconds)
                        await new Promise(resolve => setTimeout(resolve, 5000));

                        // Retry the request once (with retry disabled to avoid infinite loop)
                        console.log('🔄 Retrying request...');
                        return this.fetchWithAuth(endpoint, options, false);
                    } else {
                        console.warn('⚠️ Could not ensure services automatically');
                    }
                } catch (ensureError) {
                    console.error('Error ensuring services:', ensureError);
                }
            }

            throw error;
        }
    }

    /**
     * Send USE query to Python FastAPI
     */
    async sendUseQuery(
        question: string,
        tenantId: number,
        persona: string = 'strategic',
        queryEmbedding?: number[]
    ): Promise<UseQueryResponse> {
        const response = await this.fetchWithAuth('/api/v1/use/query', {
            method: 'POST',
            body: JSON.stringify({
                question,
                tenant_id: tenantId,
                persona,
                query_embedding: queryEmbedding,
            }),
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ detail: 'Unknown error' }));
            throw new Error(error.detail || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Generate embedding for text
     */
    async generateEmbedding(text: string, tenantId: number): Promise<number[]> {
        const response = await this.fetchWithAuth('/api/v1/embed', {
            method: 'POST',
            body: JSON.stringify({
                text,
                tenant_id: tenantId,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to generate embedding: ${response.statusText}`);
        }

        const data = await response.json();
        return data.embedding || [];
    }

    /**
     * Send RAG-Fortress chat query to Python FastAPI via Laravel proxy
     */
    async sendChatQuery(
        messages: Array<{ role: 'user' | 'assistant'; content: string }>,
        tenantId: number,
        persona: string = 'strategic',
        useRagFortress: boolean = true
    ): Promise<ChatResponse> {
        const response = await this.fetchWithAuth('/api/v1/chat', {
            method: 'POST',
            body: JSON.stringify({
                messages,
                tenant_id: tenantId,
                persona,
                use_rag_fortress: useRagFortress,
            }),
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ detail: 'Unknown error' }));
            throw new Error(error.detail || error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Save conversation to database
     */
    async saveConversation(data: {
        conversation_id?: string;
        title?: string;
        persona?: string;
        messages: Array<{
            id: string;
            role: 'user' | 'assistant';
            content: string;
            timestamp: string;
            tokens_used?: { input: number; output: number };
            model_used?: string;
            claims?: any[];
            sources?: any[];
            verification_status?: string;
            avg_urs?: number;
            command_name?: string;
            command_result?: any;
        }>;
    }): Promise<{ success: boolean; conversation?: any; message?: string }> {
        const response = await this.fetchWithAuth('/api/natan/conversations/save', {
            method: 'POST',
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Get conversation by ID
     */
    async getConversation(conversationId: string): Promise<{ success: boolean; conversation?: any; message?: string }> {
        const response = await this.fetchWithAuth(`/api/natan/conversations/${conversationId}`, {
            method: 'GET',
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Health check
     */
    async healthCheck(): Promise<boolean> {
        try {
            const response = await this.fetchWithAuth('/healthz', {
                method: 'GET',
            });
            return response.ok;
        } catch {
            return false;
        }
    }
}

// Singleton instance
// When served from Laravel (e.g., localhost:7000), use empty baseUrl for same-origin API calls
// The Laravel proxy at /api/v1/chat will forward to FastAPI
export const apiService = new ApiService({
    baseUrl: import.meta.env.VITE_API_BASE_URL || '',  // Empty = same origin (Laravel)
    timeout: 120000,
});





