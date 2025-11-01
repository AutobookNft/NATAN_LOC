/**
 * Main App Component
 * Initializes NATAN chat interface (integrated with Blade components)
 * Mobile-first: assumes DOM structure is provided by Laravel Blade
 */

import { ChatInterface } from './components/ChatInterface';
import { initRightPanel } from './components/ChatInterface';

export class App {
    private chatInterface!: ChatInterface;

    constructor(tenantId: number = 1) {
        // DOM is already rendered by Blade components (natan/chat.blade.php)
        // Just initialize ChatInterface to bind to existing elements
        this.chatInterface = new ChatInterface(tenantId);
        
        // Initialize right panel functionality (tabs, collapse, persona selection)
        initRightPanel();
        
        // Listen for persona changes from right panel
        document.addEventListener('persona-changed', ((e: CustomEvent<{ persona: string }>) => {
            // TODO: Update ChatInterface persona
            console.log('Persona changed to:', e.detail.persona);
        }) as EventListener);
    }
}






