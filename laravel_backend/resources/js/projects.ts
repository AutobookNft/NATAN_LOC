/**
 * @Oracode Entry Point: Projects & Documents
 * 🎯 Purpose: Main entry for projects frontend components
 * 
 * @package resources/js
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 */

// SweetAlert2 CSS
import 'sweetalert2/dist/sweetalert2.min.css';

import { ProjectList } from './natan/components/ProjectList';
import { ProjectDetail } from './natan/components/ProjectDetail';

// Export for global access
(window as any).ProjectList = ProjectList;
(window as any).ProjectDetail = ProjectDetail;

console.log('[NATAN][Projects] Entry point loaded successfully');

// Auto-initialize components based on DOM elements
document.addEventListener('DOMContentLoaded', () => {
    console.log('[NATAN][Projects] DOMContentLoaded - initializing components');

    // ProjectList component
    const projectListContainer = document.getElementById('project-list-container');
    if (projectListContainer) {
        const tenantId = parseInt(projectListContainer.dataset.tenantId || '0');
        console.log('[NATAN][Projects] Initializing ProjectList component with tenant:', tenantId);
        new ProjectList('project-list-container', tenantId);
    }

    // ProjectDetail component
    const projectDetailContainer = document.getElementById('project-detail-container');
    if (projectDetailContainer) {
        const projectId = parseInt(projectDetailContainer.dataset.projectId || '0');
        if (projectId) {
            console.log('[NATAN][Project] Initializing ProjectDetail component for project:', projectId);
            new ProjectDetail('project-detail-container', projectId);
        } else {
            console.error('[NATAN][Project] Project ID not found for ProjectDetail component.');
        }
    }
});

