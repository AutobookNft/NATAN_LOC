/**
 * Chat Interface Component
 * Main chat UI with message display and input
 */

import type { Message, UseQueryResponse } from '../types';
import { apiService } from '../services/api';
import { ClaimRenderer } from './ClaimRenderer';
import { MessageComponent } from './Message';

export class ChatInterface {
  private container: HTMLElement;
  private messagesContainer: HTMLElement;
  private inputForm: HTMLFormElement;
  private inputField: HTMLTextAreaElement;
  private sendButton: HTMLButtonElement;
  private messages: Message[] = [];
  private tenantId: number;
  private persona: string = 'strategic';
  private isLoading: boolean = false;

  constructor(container: HTMLElement, tenantId: number = 1) {
    this.container = container;
    this.tenantId = tenantId;
    const chatContainer = container.querySelector('#chat-container');
    if (!chatContainer) {
      throw new Error('Chat container not found');
    }
    this.render(chatContainer);
    this.attachEventListeners();
  }

  private render(container: HTMLElement): void {
    container.innerHTML = `
      <div class="flex flex-col h-[calc(100vh-12rem)]">
        <!-- Messages area -->
        <div 
          id="messages-container" 
          class="flex-1 overflow-y-auto p-4 space-y-4 mb-4 bg-white rounded-lg shadow-sm border border-gray-200"
          role="log"
          aria-live="polite"
          aria-label="Messaggi della conversazione"
        >
          <!-- Messages will be rendered here -->
        </div>

        <!-- Input area -->
        <form id="chat-input-form" class="flex gap-2">
          <div class="flex-1 relative">
            <textarea
              id="chat-input"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              placeholder="Chiedi qualcosa ai tuoi documenti... (Shift+Enter per inviare)"
              aria-label="Messaggio"
              aria-describedby="input-hint"
            ></textarea>
            <p id="input-hint" class="sr-only">
              Premi Invio per inviare il messaggio, Shift+Invio per una nuova riga
            </p>
          </div>
          <button
            id="send-button"
            type="submit"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Invia messaggio"
          >
            Invia
          </button>
        </form>
      </div>
    `;

    this.messagesContainer = container.querySelector('#messages-container') as HTMLElement;
    this.inputForm = container.querySelector('#chat-input-form') as HTMLFormElement;
    this.inputField = container.querySelector('#chat-input') as HTMLTextAreaElement;
    this.sendButton = container.querySelector('#send-button') as HTMLButtonElement;
  }

  private attachEventListeners(): void {
    this.inputForm.addEventListener('submit', (e) => this.handleSubmit(e));
    
    // Shift+Enter for new line, Enter for send
    this.inputField.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.handleSubmit(e);
      }
    });
  }

  private async handleSubmit(e: Event): Promise<void> {
    e.preventDefault();
    
    const question = this.inputField.value.trim();
    if (!question || this.isLoading) {
      return;
    }

    // Add user message
    const userMessage: Message = {
      id: this.generateId(),
      role: 'user',
      content: question,
      timestamp: new Date(),
    };
    this.addMessage(userMessage);

    // Clear input
    this.inputField.value = '';
    this.setLoading(true);

    try {
      // Send USE query
      const response: UseQueryResponse = await apiService.sendUseQuery(
        question,
        this.tenantId,
        this.persona
      );

      // Add assistant message with claims
      const assistantMessage: Message = {
        id: this.generateId(),
        role: 'assistant',
        content: this.formatResponse(response),
        timestamp: new Date(),
        claims: response.verified_claims || [],
        blockedClaims: response.blocked_claims || [],
        sources: this.extractSources(response),
        avgUrs: response.avg_urs,
        verificationStatus: response.verification_status,
      };
      
      this.addMessage(assistantMessage);
    } catch (error) {
      console.error('Error sending query:', error);
      const errorMessage: Message = {
        id: this.generateId(),
        role: 'assistant',
        content: `Errore: ${error instanceof Error ? error.message : 'Errore sconosciuto'}`,
        timestamp: new Date(),
      };
      this.addMessage(errorMessage);
    } finally {
      this.setLoading(false);
    }
  }

  private addMessage(message: Message): void {
    this.messages.push(message);
    const messageElement = MessageComponent.render(message);
    this.messagesContainer.appendChild(messageElement);
    this.scrollToBottom();
  }

  private formatResponse(response: UseQueryResponse): string {
    if (response.status === 'success' && response.verified_claims?.length) {
      return 'Risposta generata con Ultra Semantic Engine. Vedi i claim verificati qui sotto.';
    } else if (response.status === 'no_results') {
      return 'Nessun risultato trovato nei documenti.';
    } else if (response.status === 'blocked') {
      return `Query bloccata: ${response.reason || 'Nessuna ragione specificata'}`;
    } else {
      return response.message || 'Risposta generata.';
    }
  }

  private extractSources(response: UseQueryResponse): any[] {
    const sources: any[] = [];
    if (response.verified_claims) {
      response.verified_claims.forEach(claim => {
        if (claim.sourceRefs) {
          claim.sourceRefs.forEach(ref => {
            if (!sources.find(s => s.url === ref.url)) {
              sources.push({
                id: ref.source_id || ref.url,
                url: ref.url,
                title: ref.title,
                type: 'internal',
              });
            }
          });
        }
      });
    }
    return sources;
  }

  private scrollToBottom(): void {
    this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
  }

  private setLoading(loading: boolean): void {
    this.isLoading = loading;
    this.sendButton.disabled = loading;
    this.inputField.disabled = loading;
    
    if (loading) {
      this.sendButton.textContent = 'Invio...';
    } else {
      this.sendButton.textContent = 'Invia';
    }
  }

  private generateId(): string {
    return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }
}

