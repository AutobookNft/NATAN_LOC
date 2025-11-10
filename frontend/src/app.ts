/**
 * Main App Component
 * Initializes NATAN chat interface (integrated with Blade components)
 * Mobile-first: assumes DOM structure is provided by Laravel Blade
 */

import { ChatInterface } from './components/ChatInterface';

export class App {
    constructor(tenantId: number = 1) {
        // DOM is already rendered by Blade components (natan/chat.blade.php)
        // Just initialize ChatInterface to bind to existing elements
        // L'istanza viene creata per side-effect (binding eventi)
        new ChatInterface(tenantId);
    }
}






