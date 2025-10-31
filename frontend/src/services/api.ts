/**
 * API Service for NATAN Frontend
 * Handles all HTTP requests to Laravel backend and Python FastAPI
 */

import type { UseQueryResponse, ApiConfig } from '../types';

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

    private async fetchWithAuth(
        endpoint: string,
        options: RequestInit = {}
    ): Promise<Response> {
        const url = `${this.baseUrl}${endpoint}`;
        const headers: HeadersInit = {
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
export const apiService = new ApiService({
    baseUrl: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
    timeout: 120000,
});


