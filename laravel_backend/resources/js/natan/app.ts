/**
 * Main App Component
 * Initializes NATAN chat interface (integrated with Blade components)
 * Mobile-first: assumes DOM structure is provided by Laravel Blade
 */

import { ChatInterface } from './components/ChatInterface';
import { initRightPanel } from './components/ChatInterface';

export class App {
    private chatInterface?: ChatInterface;

    constructor() {
        // Initialize mobile drawer (hamburger menu) - ALWAYS needed for all NATAN pages
        initMobileDrawer();
        
        // Initialize ChatInterface only if chat elements are present (chat page)
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            // Load tenant_id from /api/session instead of hardcoded
            this.initChatInterface();
        }
    }

    /**
     * Initialize ChatInterface with tenant_id loaded from /api/session
     */
    private async initChatInterface(): Promise<void> {
        try {
            // Load tenant_id from session API
            const response = await fetch('/api/session');
            const data = await response.json();
            
            // Use tenant_id from session or default to null (will be resolved by backend)
            const tenantId = data.tenant?.id ?? null;
            
            // Initialize ChatInterface with resolved tenant_id
            this.chatInterface = new ChatInterface(tenantId);
            
            // Initialize right panel functionality (tabs, collapse, persona selection)
            initRightPanel();
            
            // Initialize questions modal (icona [🧠] nell'header)
            initQuestionsModal();
            
            // Listen for persona changes from right panel
            document.addEventListener('persona-changed', ((e: CustomEvent<{ persona: string }>) => {
                // TODO: Update ChatInterface persona
                console.log('Persona changed to:', e.detail.persona);
            }) as EventListener);
        } catch (error) {
            console.error('Failed to load session:', error);
            // Fallback: initialize with null tenantId (backend will resolve it)
            this.chatInterface = new ChatInterface(null);
            initRightPanel();
            initQuestionsModal();
        }
    }
}

/**
 * Initialize questions modal (si apre con icona [🧠] nell'header)
 * Mobile: full-screen modal bottom slide
 * Desktop: controlla right panel (toggle)
 */
function initQuestionsModal(): void {
    const toggleBtn = document.getElementById('questions-toggle-btn');
    const modal = document.getElementById('questions-modal');
    const overlay = document.getElementById('questions-modal-overlay');
    const closeBtn = document.getElementById('questions-modal-close');
    const rightPanel = document.getElementById('right-panel');
    const rightPanelToggle = document.getElementById('right-panel-toggle');
    
    if (!toggleBtn) {
        return; // Button not present
    }
    
    // Desktop: toggle right panel
    const handleDesktop = (): void => {
        if (window.innerWidth >= 1280 && rightPanel) {
            const isCollapsed = rightPanel.getAttribute('data-collapsed') === 'true';
            rightPanel.setAttribute('data-collapsed', (!isCollapsed).toString());
            if (rightPanelToggle) {
                rightPanelToggle.setAttribute('aria-expanded', (!isCollapsed).toString());
            }
            // Animate panel
            if (isCollapsed) {
                rightPanel.classList.remove('xl:w-0');
                rightPanel.classList.add('xl:w-80');
            } else {
                rightPanel.classList.remove('xl:w-80');
                rightPanel.classList.add('xl:w-0');
            }
        }
    };
    
    // Mobile: open modal
    const handleMobile = (): void => {
        if (window.innerWidth < 1280 && modal && overlay) {
            modal.classList.remove('translate-y-full', 'hidden');
            modal.setAttribute('aria-hidden', 'false');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            toggleBtn.setAttribute('aria-expanded', 'true');
        }
    };
    
    const openModal = (): void => {
        if (window.innerWidth >= 1280) {
            handleDesktop();
        } else {
            handleMobile();
        }
    };
    
    const closeModal = (): void => {
        if (modal && overlay) {
            modal.classList.add('translate-y-full');
            modal.setAttribute('aria-hidden', 'true');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    };
    
    // Event listeners
    toggleBtn.addEventListener('click', openModal);
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    if (overlay) {
        overlay.addEventListener('click', closeModal);
    }
    
    // Tab switching in modal
    if (modal) {
        const tabButtons = modal.querySelectorAll('[data-modal-tab]');
        const tabContents = modal.querySelectorAll('.tab-content');
        
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.getAttribute('data-modal-tab');
                if (!tabName) return;
                
                // Update active tab button
                tabButtons.forEach(b => {
                    b.classList.remove('border-natan-blue-dark', 'text-natan-blue-dark', 'tab-active');
                    b.classList.add('border-transparent', 'text-natan-gray-500');
                });
                btn.classList.remove('border-transparent', 'text-natan-gray-500');
                btn.classList.add('border-natan-blue-dark', 'text-natan-blue-dark', 'tab-active');
                
                // Update active tab content
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                const targetContent = modal.querySelector(`#modal-tab-${tabName}`);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                    targetContent.classList.add('active');
                }
            });
        });
        
        // Handle question clicks (event delegation)
        modal.addEventListener('click', (e) => {
            const btn = (e.target as HTMLElement).closest('[data-question]');
            if (btn) {
                const question = btn.getAttribute('data-question');
                if (question) {
                    // Dispatch event to ChatInterface to send the question
                    const event = new CustomEvent('suggestion-clicked', {
                        detail: { question }
                    });
                    document.dispatchEvent(event);
                    closeModal();
                }
            }
        });
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('translate-y-full')) {
            closeModal();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1280 && modal && !modal.classList.contains('translate-y-full')) {
            closeModal();
        }
    });
}

/**
 * Initialize mobile drawer (hamburger menu)
 * MOBILE-FIRST: opens sidebar on mobile devices
 */
function initMobileDrawer(): void {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const drawer = document.getElementById('mobile-drawer');
    const overlay = document.getElementById('mobile-drawer-overlay');
    const closeBtn = document.getElementById('mobile-drawer-close');
    
    if (!toggleBtn || !drawer || !overlay) {
        return; // Elements not present
    }
    
    const openDrawer = (): void => {
        if (window.innerWidth >= 1024) return; // Only on mobile (< lg breakpoint)
        drawer.classList.remove('-translate-x-full');
        drawer.classList.add('translate-x-0');
        drawer.setAttribute('aria-hidden', 'false');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        toggleBtn.setAttribute('aria-expanded', 'true');
    };
    
    const closeDrawer = (): void => {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('-translate-x-full');
        drawer.setAttribute('aria-hidden', 'true');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        toggleBtn.setAttribute('aria-expanded', 'false');
    };
    
    // Event listeners
    toggleBtn.addEventListener('click', openDrawer);
    if (closeBtn) {
        closeBtn.addEventListener('click', closeDrawer);
    }
    overlay.addEventListener('click', closeDrawer);
    
    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !drawer.classList.contains('-translate-x-full')) {
            closeDrawer();
        }
    });
    
    // Close drawer if window is resized to desktop size
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            closeDrawer();
        }
    });
}

/**
 * Initialize mobile questions & explanations drawer
 * CRITICAL: Only works on mobile (< 1280px), NEVER on desktop
 * @deprecated - Replaced by initQuestionsModal()
 */
function initMobileQuestionsDrawer(): void {
    const toggleBtn = document.getElementById('mobile-questions-toggle');
    const drawer = document.getElementById('mobile-questions-drawer');
    const overlay = document.getElementById('mobile-questions-overlay');
    const closeBtn = document.getElementById('mobile-questions-close');
    
    if (!toggleBtn || !drawer || !overlay || !closeBtn) {
        return;
    }
    
    // CRITICAL: Force close drawer immediately - NEVER open on desktop
    const isDesktop = window.innerWidth >= 1280;
    drawer.style.display = isDesktop ? 'none' : 'none'; // Always hidden initially
    drawer.classList.add('translate-x-full', 'hidden');
    drawer.setAttribute('aria-hidden', 'true');
    overlay.classList.add('hidden');
    
    if (isDesktop) {
        // Don't initialize ANY functionality on desktop - drawer must stay hidden
        return;
    }
    
    // Only on mobile: show toggle button, but drawer starts CLOSED
    toggleBtn.style.display = '';
    drawer.style.display = ''; // Visible in DOM but closed (translate-x-full)
    drawer.classList.remove('hidden'); // Remove hidden class on mobile only
    
    const openDrawer = (): void => {
        if (window.innerWidth >= 1280) return; // Safety check
        drawer.classList.remove('translate-x-full', 'hidden');
        drawer.setAttribute('aria-hidden', 'false');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    
    const closeDrawer = (): void => {
        drawer.classList.add('translate-x-full');
        drawer.setAttribute('aria-hidden', 'true');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };
    
    toggleBtn.addEventListener('click', openDrawer);
    closeBtn.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    
    // Tab switching
    const tabButtons = drawer.querySelectorAll('[data-mobile-tab]');
    const tabContents = drawer.querySelectorAll('.mobile-tab-content');
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.getAttribute('data-mobile-tab');
            if (!tabName) return;
            
            // Update active tab button
            tabButtons.forEach(b => {
                b.classList.remove('border-natan-blue-dark', 'text-natan-blue-dark', 'mobile-tab-active');
                b.classList.add('border-transparent', 'text-natan-gray-500');
            });
            btn.classList.remove('border-transparent', 'text-natan-gray-500');
            btn.classList.add('border-natan-blue-dark', 'text-natan-blue-dark', 'mobile-tab-active');
            
            // Update active tab content
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            const targetContent = drawer.querySelector(`#mobile-tab-${tabName}`);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                targetContent.classList.add('active');
            }
        });
    });
    
    // Handle question clicks (mobile) - use event delegation for dynamic content
    drawer.addEventListener('click', (e) => {
        const btn = (e.target as HTMLElement).closest('[data-mobile-question]');
        if (btn) {
            const question = btn.getAttribute('data-mobile-question');
            if (question) {
                // Dispatch event to ChatInterface to send the question
                const event = new CustomEvent('suggestion-clicked', {
                    detail: { question }
                });
                document.dispatchEvent(event);
                closeDrawer();
            }
        }
    });
    
    // Update chevron rotation on details open/close (mobile)
    drawer.querySelectorAll('details').forEach(detail => {
        detail.addEventListener('toggle', () => {
            const chevron = detail.querySelector('.details-chevron');
            if (chevron) {
                if (detail.open) {
                    chevron.classList.add('rotate-180');
                } else {
                    chevron.classList.remove('rotate-180');
                }
            }
        });
    });
    
    // CRITICAL: Close drawer if window is resized to desktop size
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1280) {
            closeDrawer();
        }
    });
}






