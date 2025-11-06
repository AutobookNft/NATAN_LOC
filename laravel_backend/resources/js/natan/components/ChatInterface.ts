/**
 * Chat Interface Component
 * Main chat UI with message display and input
 * Mobile-first: integrates with Blade components, no DOM creation
 */

import type { Message, UseQueryResponse } from '../types';
import { apiService } from '../services/api';
import { MessageComponent } from './Message';

export class ChatInterface {
    private messagesContainer!: HTMLElement;
    private inputForm!: HTMLFormElement;
    private inputField!: HTMLTextAreaElement;
    private sendButton!: HTMLButtonElement;
    private welcomeMessage: HTMLElement | null = null;
    private personaSelector: HTMLSelectElement | null = null;
    private suggestionsToggle: HTMLElement | null = null;
    private suggestionsContent: HTMLElement | null = null;
    private messages: Message[] = [];
    private tenantId: number | null;
    private persona: string = 'auto';
    private isLoading: boolean = false;

    constructor(tenantId: number | null = null) {
        // tenantId viene passato da /api/session o risolto dal backend
        // Se null, il backend risolverà automaticamente usando TenantResolver
        this.tenantId = tenantId;
        this.findDOMElements();
        this.attachEventListeners();
        this.initMobileComponents();
        this.initChatHistoryListeners();
    }

    /**
     * Auto-resize textarea to fit content (mobile-friendly)
     */
    private autoResizeTextarea(): void {
        this.inputField.style.height = 'auto';
        const maxHeight = 150; // max-height from CSS
        const scrollHeight = this.inputField.scrollHeight;
        this.inputField.style.height = `${Math.min(scrollHeight, maxHeight)}px`;
    }

    /**
     * Find DOM elements created by Blade components (mobile-first approach)
     */
    private findDOMElements(): void {
        // Messages container (from chat-interface.blade.php)
        this.messagesContainer = document.querySelector('#chat-messages') as HTMLElement;
        if (!this.messagesContainer) {
            throw new Error('Chat messages container not found. Ensure <x-natan.chat-interface /> is rendered.');
        }

        // Input form (from chat-input.blade.php)
        this.inputForm = document.querySelector('#chat-form') as HTMLFormElement;
        if (!this.inputForm) {
            throw new Error('Chat form not found. Ensure <x-natan.chat-input /> is rendered.');
        }

        // Input field
        this.inputField = document.querySelector('#chat-input') as HTMLTextAreaElement;
        if (!this.inputField) {
            throw new Error('Chat input not found.');
        }

        // Send button
        this.sendButton = document.querySelector('#send-button') as HTMLButtonElement;
        if (!this.sendButton) {
            throw new Error('Send button not found.');
        }

        // Welcome message (to hide after first message)
        this.welcomeMessage = document.querySelector('#welcome-message');

        // Persona selector (optional)
        this.personaSelector = document.querySelector('#persona-selector') as HTMLSelectElement | null;

        // Suggestions toggle (mobile only)
        this.suggestionsToggle = document.querySelector('#suggestions-toggle');
        this.suggestionsContent = document.querySelector('#suggestions-content');
    }

    /**
     * Initialize mobile-specific components
     */
    private initMobileComponents(): void {
        // Mobile drawer toggle
        const mobileMenuToggle = document.querySelector('#mobile-menu-toggle');
        const mobileDrawer = document.querySelector('#mobile-drawer');
        const mobileDrawerOverlay = document.querySelector('#mobile-drawer-overlay');
        const mobileDrawerClose = document.querySelector('#mobile-drawer-close');

        if (mobileMenuToggle && mobileDrawer && mobileDrawerOverlay) {
            const openDrawer = () => {
                mobileDrawer.classList.remove('-translate-x-full');
                mobileDrawerOverlay.classList.remove('hidden');
                mobileDrawer.setAttribute('aria-hidden', 'false');
                mobileMenuToggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden'; // Prevent body scroll
            };

            const closeDrawer = () => {
                mobileDrawer.classList.add('-translate-x-full');
                mobileDrawerOverlay.classList.add('hidden');
                mobileDrawer.setAttribute('aria-hidden', 'true');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = ''; // Restore body scroll
            };

            mobileMenuToggle.addEventListener('click', openDrawer);
            mobileDrawerOverlay.addEventListener('click', closeDrawer);
            if (mobileDrawerClose) {
                mobileDrawerClose.addEventListener('click', closeDrawer);
            }

            // Close drawer on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !mobileDrawer.classList.contains('-translate-x-full')) {
                    closeDrawer();
                }
            });
        }

        // Suggestions toggle (mobile only)
        const toggle = this.suggestionsToggle;
        const content = this.suggestionsContent;
        if (toggle && content) {
            toggle.addEventListener('click', () => {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    content.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    const chevron = toggle.querySelector('svg');
                    if (chevron) chevron.classList.remove('rotate-180');
                } else {
                    content.classList.remove('hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                    const chevron = toggle.querySelector('svg');
                    if (chevron) chevron.classList.add('rotate-180');
                }
            });
        }

        // Suggestion buttons (pre-fill input)
        // Listen for suggestion clicks (from suggestions panel)
        document.querySelectorAll('[data-suggestion]').forEach((button) => {
            button.addEventListener('click', (e) => {
                const suggestion = (e.currentTarget as HTMLElement).dataset.suggestion;
                if (suggestion) {
                    this.inputField.value = suggestion;
                    this.inputField.focus();
                    // Auto-resize textarea
                    this.autoResizeTextarea();
                }
            });
        });

        // Chat history items (load conversation)
        document.querySelectorAll('[data-chat-id]').forEach((button) => {
            button.addEventListener('click', (e) => {
                const chatId = (e.currentTarget as HTMLElement).dataset.chatId;
                if (chatId) {
                    // TODO: Load conversation from history
                    console.log('Load chat:', chatId);
                }
            });
        });
        
        // Listen for question clicks (from right panel desktop and mobile drawer)
        document.querySelectorAll('[data-question]').forEach((button) => {
            button.addEventListener('click', () => {
                const question = button.getAttribute('data-question');
                if (question) {
                    this.sendMessage(question);
                }
            });
        });
        
        // Listen for suggestion-clicked event (from mobile drawer)
        document.addEventListener('suggestion-clicked', ((e: CustomEvent<{ question: string }>) => {
            this.sendMessage(e.detail.question);
        }) as EventListener);
    }

    /**
     * Attach event listeners
     */
    private attachEventListeners(): void {
        // Form submit
        this.inputForm.addEventListener('submit', (e) => this.handleSubmit(e));

        // Shift+Enter for new line, Enter for send
        this.inputField.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleSubmit(e);
            }
        });

        // Auto-resize textarea on input
        this.inputField.addEventListener('input', () => this.autoResizeTextarea());

        // Persona selector change
        if (this.personaSelector) {
            this.personaSelector.addEventListener('change', (e) => {
                this.persona = (e.target as HTMLSelectElement).value;
            });
        }
    }

    private async handleSubmit(e: Event): Promise<void> {
        e.preventDefault();

        const question = this.inputField.value.trim();
        if (!question || this.isLoading) {
            return;
        }

        // Hide welcome message on first user message
        if (this.welcomeMessage && this.messages.length === 0) {
            this.welcomeMessage.classList.add('hidden');
        }

        // Add user message
        const userMessage: Message = {
            id: this.generateId(),
            role: 'user',
            content: question,
            timestamp: new Date(),
        };
        this.addMessage(userMessage);

        // Clear input and reset height
        this.inputField.value = '';
        this.inputField.style.height = 'auto';
        this.setLoading(true);

        try {
            // Send USE query
            // Se tenantId è null, usa 1 come fallback temporaneo
            // Il backend risolverà il tenant_id corretto usando TenantResolver
            const response: UseQueryResponse = await apiService.sendUseQuery(
                question,
                this.tenantId ?? 1,
                this.persona
            );

            // Add assistant message with natural language answer and verified claims
            // CRITICAL: NEVER expose blocked_claims to user - they contain invented/false data
            // Even if backend sends them, we filter them out for security
            const assistantMessage: Message = {
                id: this.generateId(),
                role: 'assistant',
                content: response.answer || this.formatResponse(response),  // Use natural language answer if available
                timestamp: new Date(),
                claims: response.verified_claims || [],  // ONLY verified claims with sources (shown as proof below)
                blockedClaims: [],  // NEVER expose blocked claims - they contain invented data (security risk)
                sources: this.extractSources(response),
                avgUrs: response.avg_urs,
                verificationStatus: response.verification_status,
                tokensUsed: response.tokens_used || null,  // Store tokens for cost calculation
                modelUsed: response.model_used || null,  // Store model for cost calculation
            };
            
            // Log blocked claims count for internal monitoring (never show to user)
            if (response.blocked_claims && response.blocked_claims.length > 0) {
                console.warn(`[NATAN SECURITY] ${response.blocked_claims.length} claims were blocked (not shown to user for safety)`);
            }

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

    /**
     * Send a message programmatically (used by question clicks)
     */
    public sendMessage(content: string): void {
        if (!content.trim() || this.isLoading) {
            return;
        }
        
        // Set input value and trigger submit
        this.inputField.value = content;
        this.autoResizeTextarea();
        this.inputForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }

    private addMessage(message: Message): void {
        this.messages.push(message);
        const messageElement = MessageComponent.render(message);
        this.messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
        
        // Save conversation after each message (user or assistant)
        // This ensures conversations are saved even if assistant fails to respond
        this.saveMessageToConversation(message);
    }
    
    /**
     * Save message to conversation (create or update conversation in natan_chat_messages)
     * Called after each message (user or assistant) to ensure conversations are always saved
     */
    private async saveMessageToConversation(message: Message): Promise<void> {
        try {
            // Get current conversation ID or generate new one
            let conversationId = this.getCurrentConversationId();
            
            // Prepare all messages for saving (include tokens, model, claims, sources, verification_status)
            const messagesToSave = this.messages.map(msg => ({
                id: msg.id,
                role: msg.role,
                content: msg.content,
                timestamp: msg.timestamp instanceof Date ? msg.timestamp.toISOString() : new Date(msg.timestamp).toISOString(),
                // Include tokens_used and model_used for cost calculation (only for assistant messages)
                ...(msg.role === 'assistant' && msg.tokensUsed ? {
                    tokens_used: msg.tokensUsed,
                    model_used: msg.modelUsed ?? null,
                } : {}),
                // Include claims, sources, and verification_status for assistant messages (CRITICAL for reopening chats)
                ...(msg.role === 'assistant' ? {
                    claims: msg.claims ?? [],
                    sources: msg.sources ?? [],
                    verification_status: msg.verificationStatus ?? null,
                    avg_urs: msg.avgUrs ?? null,
                } : {}),
            }));

            // Generate title from first user message if new conversation
            let title: string | undefined = undefined;
            if (!conversationId) {
                const firstUserMessage = this.messages.find(m => m.role === 'user');
                if (firstUserMessage) {
                    title = firstUserMessage.content.substring(0, 50) + (firstUserMessage.content.length > 50 ? '...' : '');
                }
            }

            // Save conversation
            console.log('💾 Saving conversation:', {
                conversationId: conversationId ?? 'new',
                messageCount: messagesToSave.length,
                lastMessageRole: message.role,
                hasTokens: messagesToSave.some(m => m.tokens_used),
            });
            
            const result = await apiService.saveConversation({
                conversation_id: conversationId ?? undefined,
                title,
                persona: this.persona,
                messages: messagesToSave,
            });

            console.log('✅ Conversation save result:', result);

            // Update conversation ID if it was created
            if (result.success && result.conversation) {
                const savedConversationId = result.conversation.id || result.conversation.session_id;
                
                if (!conversationId) {
                    // Store conversation ID for future saves
                    this.setCurrentConversationId(savedConversationId);
                    // Update memory badge count only on new conversation
                    this.updateMemoryCount();
                    console.log('📝 New conversation created:', savedConversationId);
                } else {
                    console.log('📝 Conversation updated:', conversationId);
                }
            } else {
                console.warn('⚠️ Conversation save returned success=false:', result);
            }
        } catch (error) {
            console.error('❌ Error saving message to conversation:', error);
            // Don't throw - save failure shouldn't block chat functionality
            // But log it for debugging
        }
    }

    /**
     * Update memory badge count
     */
    private async updateMemoryCount(): Promise<void> {
        const memoryBadge = document.getElementById('memory-badge');
        const memoryCount = document.getElementById('memory-count');
        
        if (memoryCount) {
            // TODO: Fetch actual count from API
            // For now, increment if conversation was saved
            const currentCount = parseInt(memoryCount.textContent || '0', 10);
            memoryCount.textContent = (currentCount + 1).toString();
        }
    }

    private formatResponse(response: UseQueryResponse): string {
        // Use natural language answer if available, otherwise fallback to status message
        if (response.answer) {
            return response.answer;
        } else if (response.status === 'success' && response.verified_claims?.length) {
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

    /**
     * Initialize chat history item click listeners
     */
    private initChatHistoryListeners(): void {
        // Listen for clicks on chat history items
        document.addEventListener('click', (e) => {
            const button = (e.target as HTMLElement).closest('[data-chat-id]') as HTMLButtonElement | null;
            if (button) {
                const conversationId = button.getAttribute('data-chat-id');
                if (conversationId) {
                    this.loadConversation(conversationId);
                }
            }
        });

        // Close mobile drawer when loading a conversation
        const mobileDrawer = document.querySelector('#mobile-drawer');
        const mobileDrawerOverlay = document.querySelector('#mobile-drawer-overlay');
        if (mobileDrawer && mobileDrawerOverlay) {
            document.addEventListener('click', (e) => {
                const button = (e.target as HTMLElement).closest('[data-chat-id]') as HTMLButtonElement | null;
                if (button && mobileDrawer.classList.contains('-translate-x-full') === false) {
                    mobileDrawer.classList.add('-translate-x-full');
                    mobileDrawerOverlay.classList.add('hidden');
                    mobileDrawer.setAttribute('aria-hidden', 'true');
                }
            });
        }
    }

    /**
     * Load conversation by conversation_id
     */
    private async loadConversation(conversationId: string): Promise<void> {
        console.log('📂 Loading conversation:', conversationId);
        
        // Clear current messages
        this.messages = [];
        this.messagesContainer.innerHTML = '';

        // Show loading state
        this.setLoading(true);

        // Hide welcome message
        if (this.welcomeMessage) {
            this.welcomeMessage.classList.add('hidden');
        }

        try {
            // Fetch conversation from API
            const result = await apiService.getConversation(conversationId);

            if (!result.success || !result.conversation) {
                throw new Error(result.message || 'Conversazione non trovata');
            }

            const conversation = result.conversation;
            console.log('✅ Conversation loaded:', {
                id: conversation.id,
                messageCount: conversation.messages?.length || 0,
            });

            // Set current conversation ID
            this.setCurrentConversationId(conversationId);

            // Load messages
            if (conversation.messages && conversation.messages.length > 0) {
                // Clear messages array before adding loaded ones
                this.messages = [];

                // Add each message to the chat
                for (const msg of conversation.messages) {
                    const message: Message = {
                        id: msg.id || this.generateId(),
                        role: msg.role,
                        content: msg.content,
                        timestamp: new Date(msg.timestamp),
                        tokensUsed: msg.tokens_used ? {
                            input: msg.tokens_used.input || 0,
                            output: msg.tokens_used.output || 0,
                        } : null,
                        modelUsed: msg.model_used || null,
                        // Restore claims, sources, and verification_status from saved data
                        claims: msg.claims || [],
                        sources: msg.sources || [],
                        verificationStatus: msg.verification_status || null,
                        avgUrs: msg.avg_urs || null,
                    };

                    this.messages.push(message);
                    const messageElement = MessageComponent.render(message);
                    this.messagesContainer.appendChild(messageElement);
                }

                // Scroll to bottom after loading all messages
                this.scrollToBottom();

                console.log(`✅ Loaded ${conversation.messages.length} messages`);
            } else {
                console.warn('⚠️ Conversation has no messages');
            }

            // Update persona if available
            if (conversation.persona && this.personaSelector) {
                this.personaSelector.value = conversation.persona;
                this.persona = conversation.persona;
            }

        } catch (error) {
            console.error('❌ Error loading conversation:', error);
            const errorMessage: Message = {
                id: this.generateId(),
                role: 'assistant',
                content: `Errore nel caricamento della conversazione: ${error instanceof Error ? error.message : 'Errore sconosciuto'}`,
                timestamp: new Date(),
            };
            this.addMessage(errorMessage);
        } finally {
            this.setLoading(false);
        }
    }

    private scrollToBottom(): void {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    /**
     * Set loading state (mobile-friendly: show icon + text)
     */
    private setLoading(loading: boolean): void {
        this.isLoading = loading;
        this.sendButton.disabled = loading;
        this.inputField.disabled = loading;

        // Update button content (preserve icon structure from Blade)
        const iconSpan = this.sendButton.querySelector('span');
        const iconSvg = this.sendButton.querySelector('svg');

        if (loading) {
            if (iconSpan) {
                iconSpan.textContent = 'Invio...';
            } else {
                // Mobile: button has only icon, show spinner
                this.sendButton.innerHTML = `
                    <svg class="animate-spin w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            }
        } else {
            // Restore original button content
            if (iconSpan && iconSvg) {
                iconSpan.textContent = 'Invia';
            } else if (iconSvg) {
                // Restore icon-only button (mobile)
                this.sendButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                `;
            }
        }
    }

    private generateId(): string {
        return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    private currentConversationId: string | null = null;

    /**
     * Get current conversation ID (if loaded)
     */
    public getCurrentConversationId(): string | null {
        return this.currentConversationId;
    }

    /**
     * Set current conversation ID
     */
    public setCurrentConversationId(conversationId: string | null): void {
        this.currentConversationId = conversationId;
    }

    /**
     * Clear all messages and reset chat interface
     */
    public clearMessages(): void {
        this.messages = [];
        if (this.messagesContainer) {
            this.messagesContainer.innerHTML = '';
        }
        if (this.welcomeMessage) {
            this.welcomeMessage.classList.remove('hidden');
        }
    }
}

/**
 * Initialize right panel functionality (tabs, collapse, persona selection)
 */
export function initRightPanel(): void {
    // Panel collapse toggle
    const panelToggle = document.querySelector('#right-panel-toggle');
    const rightPanel = document.querySelector('#right-panel');
    const panelContent = document.querySelector('#right-panel-content');
    const panelTabs = document.querySelector('#right-panel-tabs');
    const chevron = document.querySelector('#right-panel-chevron');

    if (panelToggle && rightPanel && panelContent && panelTabs && chevron) {
        panelToggle.addEventListener('click', () => {
            const isCollapsed = rightPanel.getAttribute('data-collapsed') === 'true';
            
            if (isCollapsed) {
                // Expand
                rightPanel.setAttribute('data-collapsed', 'false');
                panelToggle.setAttribute('aria-expanded', 'true');
                rightPanel.classList.remove('xl:w-0', 'xl:overflow-hidden');
                rightPanel.classList.add('xl:w-80');
                chevron.classList.remove('rotate-180');
            } else {
                // Collapse
                rightPanel.setAttribute('data-collapsed', 'true');
                panelToggle.setAttribute('aria-expanded', 'false');
                rightPanel.classList.remove('xl:w-80');
                rightPanel.classList.add('xl:w-0', 'xl:overflow-hidden');
                chevron.classList.add('rotate-180');
            }
        });
    }

    // Tab switching
    const tabButtons = document.querySelectorAll('#right-panel-tabs [data-tab]');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            if (!tabName) return;

            // Update active tab button
            tabButtons.forEach(btn => {
                btn.classList.remove('text-natan-blue-dark', 'border-natan-blue-dark', 'tab-active');
                btn.classList.add('text-natan-gray-500', 'border-transparent');
            });
            button.classList.remove('text-natan-gray-500', 'border-transparent');
            button.classList.add('text-natan-blue-dark', 'border-natan-blue-dark', 'tab-active');

            // Show corresponding tab content
            const allTabContents = document.querySelectorAll('.tab-content');
            allTabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            const activeTabContent = document.querySelector(`#tab-content-${tabName}`);
            if (activeTabContent) {
                activeTabContent.classList.remove('hidden');
                activeTabContent.classList.add('active');
            }
        });
    });

    // Note: Persona selection is handled by the persona-selector component above chat input
    // No need for duplicate persona selection in right panel
}






