import './styles.css';
import { App } from './app';

// Initialize app (integrated with Blade components)
// DOM structure is provided by Laravel Blade (natan/chat.blade.php)
document.addEventListener('DOMContentLoaded', () => {
    // Get tenant_id from meta tag or default to 1
    const tenantIdMeta = document.querySelector('meta[name="tenant-id"]');
    const tenantId = tenantIdMeta 
        ? parseInt(tenantIdMeta.getAttribute('content') || '1', 10)
        : 1;
    
    // Initialize App (will bind to existing DOM elements from Blade)
    new App(tenantId);
});







