// Import styles are handled by Laravel app.css (natan.css)
import { App } from './app';

// Initialize app (integrated with Blade components)
// DOM structure is provided by Laravel Blade (natan/chat.blade.php)
// tenant_id viene caricato da /api/session in App.initChatInterface()
document.addEventListener('DOMContentLoaded', () => {
    console.log('[NATAN][main] DOMContentLoaded: Initializing App');
    try {
        // Initialize App (will bind to existing DOM elements from Blade)
        // tenant_id sarà caricato automaticamente da /api/session endpoint
        new App();
        console.log('[NATAN][main] App initialized successfully');
    } catch (error) {
        console.error('[NATAN][main] Failed to initialize App:', error);
    }
});







