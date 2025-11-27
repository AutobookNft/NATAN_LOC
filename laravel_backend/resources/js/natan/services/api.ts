/**
 * API Service for NATAN Frontend
 * Handles all HTTP requests to Laravel backend and Python FastAPI
 */

import type { UseQueryResponse, ApiConfig, ChatCommandResponse, NaturalQueryResponse } from '../types';

export class ApiService {
    private baseUrl: string;
    private apiToken: string | null = null;
    private timeout: number;

    constructor(config: ApiConfig) {
        this.baseUrl = config.baseUrl;
        this.apiToken = config.apiToken || null;
        this.timeout = config.timeout || 120000;
    }

    async executeCommand(command: string): Promise<ChatCommandResponse> {
        const laravelUrl = window.location.origin;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(`${laravelUrl}/natan/commands/execute`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({ command }),
        });

        const data = await response.json().catch(() => ({
            success: false,
            error: response.statusText || 'Unknown error',
        }));

        if (!response.ok || data.success === false) {
            const errorMessage = data.error || data.message || `HTTP ${response.status}`;
            throw new Error(errorMessage);
        }

        return data as ChatCommandResponse;
    }

    async executeNaturalQuery(text: string): Promise<NaturalQueryResponse> {
        const laravelUrl = window.location.origin;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(`${laravelUrl}/natan/commands/natural-query`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({ text }),
            });

            const data = await response.json().catch(() => ({
                success: false,
                message: response.statusText || 'Unknown error',
                code: 'invalid_response',
            }));

            // Log response for debugging memory system
            console.log('[ApiService] Natural query response:', {
                success: data.success,
                code: data.code,
                message: data.message?.substring(0, 100),
                hasRows: !!data.rows,
                rowsCount: data.rows?.length || 0
            });

            if (!response.ok) {
                return {
                    success: false,
                    message: data.message || data.error || `HTTP ${response.status}`,
                    code: data.code ?? null,
                    rows: [],
                    metadata: data.metadata ?? {},
                    verification_status: 'direct_query',
                };
            }

            return data as NaturalQueryResponse;
        } catch (error) {
            return {
                success: false,
                message: error instanceof Error ? error.message : 'Network error',
                code: 'network_error',
                rows: [],
                metadata: {},
                verification_status: 'direct_query',
            };
        }
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
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(options.headers || {}),
        };

        if (this.apiToken) {
            headers['Authorization'] = `Bearer ${this.apiToken}`;
        }

        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
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
    ): Promise<any> {
        // Use relative URL - works correctly because browser uses the same origin as the page
        // Laravel serves on port 7000, so /api/v1/chat will resolve to http://localhost:7000/api/v1/chat
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Explicitly ensure POST method (prevent any GET requests)
        console.log('[ApiService][sendChatQuery] Sending POST request to /api/v1/chat', {
            messagesCount: messages.length,
            tenantId,
            persona,
            useRagFortress
        });

        const response = await fetch('/api/v1/chat', {
            method: 'POST', // CRITICAL: Must be POST, not GET
            credentials: 'same-origin', // CRITICAL: Include cookies for session auth
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
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

    /**
     * Save conversation to Laravel backend
     */
    async saveConversation(data: {
        conversation_id?: string;
        title?: string;
        persona?: string;
        messages: Array<{
            id: string;
            role: 'user' | 'assistant';
            content: string;
            timestamp: string | Date;
            claims?: any[];
            sources?: any[];
            verification_status?: string;
            avg_urs?: number;
            tokens_used?: {
                input: number;
                output: number;
            };
            model_used?: string;
        }>;
    }): Promise<any> {
        // Call Laravel backend (not Python FastAPI)
        const laravelUrl = window.location.origin;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Usa /natan/conversations/save invece di /api/natan/ per evitare problemi nginx
        const response = await fetch(`${laravelUrl}/natan/conversations/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({
                ...data,
                messages: data.messages.map(msg => ({
                    ...msg,
                    timestamp: msg.timestamp instanceof Date ? msg.timestamp.toISOString() : msg.timestamp,
                })),
            }),
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Get conversation from Laravel backend
     */
    async getConversation(conversationId: string): Promise<any> {
        const laravelUrl = window.location.origin;

        // Usa /natan/conversations/ invece di /api/natan/ per evitare problemi nginx
        const response = await fetch(`${laravelUrl}/natan/conversations/${conversationId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Estimate query processing time and complexity
     * Returns immediately with processing notice if query will take time
     */
    async estimateQuery(text: string): Promise<{
        will_take_time: boolean;
        estimated_seconds: number;
        processing_notice: string | null;
        query_type: string;
        num_documents_estimated: number;
    }> {
        const laravelUrl = window.location.origin;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(`${laravelUrl}/natan/commands/estimate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({ text }),
            });

            if (!response.ok) {
                console.warn('[ApiService] Estimate failed, proceeding without notice');
                return {
                    will_take_time: false,
                    estimated_seconds: 5,
                    processing_notice: null,
                    query_type: 'unknown',
                    num_documents_estimated: 0,
                };
            }

            return await response.json();
        } catch (error) {
            console.warn('[ApiService] Estimate error:', error);
            return {
                will_take_time: false,
                estimated_seconds: 5,
                processing_notice: null,
                query_type: 'error',
                num_documents_estimated: 0,
            };
        }
    }
}

// Singleton instance
// When served from Laravel, use empty baseUrl for same-origin API calls
// Laravel proxy at /api/v1/chat forwards to FastAPI on port 8001
export const apiService = new ApiService({
    baseUrl: import.meta.env.VITE_API_BASE_URL || '',  // Empty = same origin (Laravel)
    timeout: 120000,
});





