/**
 * @Oracode Component: Project List
 * 🎯 Purpose: Display and manage list of NATAN projects
 * 
 * @package resources/js/natan/components
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 */

import type { NatanProject, ProjectFormData } from '../types/projects';
import { projectService } from '../services/ProjectService';
import Swal from 'sweetalert2';

export class ProjectList {
    private container: HTMLElement;
    private projects: NatanProject[] = [];
    private loading: boolean = false;
    private createModalOpen: boolean = false;

    constructor(containerId: string) {
        const element = document.getElementById(containerId);
        if (!element) {
            throw new Error(`Container #${containerId} not found`);
        }
        this.container = element;
        this.init();
    }

    private async init(): Promise<void> {
        await this.loadProjects();
        this.attachEventListeners();
    }

    /**
     * Load projects from API
     */
    private async loadProjects(): Promise<void> {
        this.loading = true;
        this.render();

        try {
            console.log('[ProjectList] Fetching projects...');
            this.projects = await projectService.getProjects();
            console.log('[ProjectList] Projects loaded:', this.projects);
        } catch (error) {
            console.error('[ProjectList] Failed to load projects:', error);
            this.showError(error instanceof Error ? error.message : 'Failed to load projects');
        } finally {
            this.loading = false;
            this.render(); // CRITICAL: Always re-render after loading completes
        }
    }

    /**
     * Render component
     */
    private render(): void {
        if (this.loading) {
            this.container.innerHTML = this.renderLoading();
            return;
        }

        this.container.innerHTML = `
            <div class="projects-container">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-natan-dark">I Miei Progetti</h1>
                        <p class="text-gray-600 mt-1">Organizza i tuoi documenti per argomento o attività</p>
                    </div>
                    <button 
                        id="create-project-btn"
                        class="btn btn-primary flex items-center gap-2"
                    >
                        <span class="material-icons text-lg">add</span>
                        Nuovo Progetto
                    </button>
                </div>

                <!-- Projects Grid -->
                ${this.projects.length === 0 ? this.renderEmpty() : this.renderProjectsGrid()}
            </div>

            <!-- Create Project Modal -->
            ${this.createModalOpen ? this.renderCreateModal() : ''}
        `;

        this.attachEventListeners();
    }

    /**
     * Render loading state
     */
    private renderLoading(): string {
        return `
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-natan-blue"></div>
                    <p class="mt-4 text-gray-600">Caricamento progetti...</p>
                </div>
            </div>
        `;
    }

    /**
     * Render empty state
     */
    private renderEmpty(): string {
        return `
            <div class="text-center py-12 bg-white rounded-lg border-2 border-dashed border-gray-300">
                <span class="material-icons text-6xl text-gray-400 mb-4">folder_open</span>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessun progetto</h3>
                <p class="text-gray-500 mb-6">Crea il tuo primo progetto per iniziare a organizzare i documenti</p>
                <button 
                    id="create-first-project-btn"
                    class="btn btn-primary inline-flex items-center gap-2"
                >
                    <span class="material-icons">add</span>
                    Crea Primo Progetto
                </button>
            </div>
        `;
    }

    /**
     * Render projects grid
     */
    private renderProjectsGrid(): string {
        return `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${this.projects.map(project => this.renderProjectCard(project)).join('')}
            </div>
        `;
    }

    /**
     * Render single project card
     */
    private renderProjectCard(project: NatanProject): string {
        const documentsCount = project.documents_count || 0;
        const readyCount = project.ready_documents_count || 0;

        return `
            <div 
                class="project-card bg-white rounded-lg border border-gray-200 hover:border-natan-blue hover:shadow-lg transition-all cursor-pointer"
                data-project-id="${project.id}"
                style="border-left: 4px solid ${project.color}"
            >
                <div class="p-6">
                    <!-- Icon & Title -->
                    <div class="flex items-start gap-4 mb-4">
                        <div 
                            class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center"
                            style="background-color: ${project.color}20"
                        >
                            <span class="material-icons text-2xl" style="color: ${project.color}">
                                ${project.icon}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">
                                ${this.escapeHtml(project.collection_name || project.name || 'Progetto')}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                ${project.description ? this.escapeHtml(project.description) : 'Nessuna descrizione'}
                            </p>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-1 text-gray-600">
                                <span class="material-icons text-base">description</span>
                                <span>${documentsCount}</span>
                            </div>
                            <div class="flex items-center gap-1 text-green-600">
                                <span class="material-icons text-base">check_circle</span>
                                <span>${readyCount}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                class="btn-icon-sm delete-project-btn"
                                data-project-id="${project.id}"
                                title="Elimina progetto"
                                onclick="event.stopPropagation()"
                            >
                                <span class="material-icons text-base">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render create project modal
     */
    private renderCreateModal(): string {
        return `
            <div 
                id="create-project-modal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900">Nuovo Progetto</h3>
                        <button 
                            id="close-modal-btn"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <form id="create-project-form" class="p-6 space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Progetto <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text"
                                name="name"
                                id="project-name"
                                required
                                maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-natan-blue"
                                placeholder="es. Mobilità Sostenibile 2024"
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descrizione
                            </label>
                            <textarea 
                                name="description"
                                id="project-description"
                                rows="3"
                                maxlength="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-natan-blue"
                                placeholder="Descrizione del progetto..."
                            ></textarea>
                        </div>

                        <!-- Icon & Color -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Icona
                                </label>
                                <select 
                                    name="icon"
                                    id="project-icon"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-natan-blue"
                                >
                                    <option value="folder_open">Cartella</option>
                                    <option value="description">Documento</option>
                                    <option value="business">Business</option>
                                    <option value="engineering">Ingegneria</option>
                                    <option value="gavel">Legale</option>
                                    <option value="account_balance">Finanza</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Colore
                                </label>
                                <select 
                                    name="color"
                                    id="project-color"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-natan-blue"
                                >
                                    <option value="#1B365D">Blu NATAN</option>
                                    <option value="#10B981">Verde</option>
                                    <option value="#F59E0B">Arancione</option>
                                    <option value="#EF4444">Rosso</option>
                                    <option value="#8B5CF6">Viola</option>
                                    <option value="#06B6D4">Celeste</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-3 mt-6">
                            <button 
                                type="button"
                                id="cancel-create-btn"
                                class="btn btn-secondary"
                            >
                                Annulla
                            </button>
                            <button 
                                type="submit"
                                class="btn btn-primary"
                            >
                                Crea Progetto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    /**
     * Attach event listeners
     */
    private attachEventListeners(): void {
        // Create project button
        const createBtn = document.getElementById('create-project-btn');
        const createFirstBtn = document.getElementById('create-first-project-btn');
        
        if (createBtn) {
            createBtn.addEventListener('click', () => this.openCreateModal());
        }
        
        if (createFirstBtn) {
            createFirstBtn.addEventListener('click', () => this.openCreateModal());
        }

        // Modal controls
        const closeModalBtn = document.getElementById('close-modal-btn');
        const cancelBtn = document.getElementById('cancel-create-btn');
        
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.closeCreateModal());
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeCreateModal());
        }

        // Create form submit
        const createForm = document.getElementById('create-project-form');
        if (createForm) {
            createForm.addEventListener('submit', (e) => this.handleCreateProject(e));
        }

        // Project cards click
        const projectCards = document.querySelectorAll('.project-card');
        projectCards.forEach(card => {
            card.addEventListener('click', (e) => {
                const projectId = (card as HTMLElement).dataset.projectId;
                if (projectId) {
                    window.location.href = `/natan/ui/projects/${projectId}`;
                }
            });
        });

        // Delete buttons
        const deleteButtons = document.querySelectorAll('.delete-project-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const projectId = (btn as HTMLElement).dataset.projectId;
                if (projectId) {
                    this.handleDeleteProject(parseInt(projectId));
                }
            });
        });
    }

    /**
     * Open create modal
     */
    private openCreateModal(): void {
        this.createModalOpen = true;
        this.render();
    }

    /**
     * Close create modal
     */
    private closeCreateModal(): void {
        this.createModalOpen = false;
        this.render();
    }

    /**
     * Handle create project form submit
     */
    private async handleCreateProject(e: Event): Promise<void> {
        e.preventDefault();

        const form = e.target as HTMLFormElement;
        const formData = new FormData(form);

        const projectData: ProjectFormData = {
            name: formData.get('name') as string,
            description: formData.get('description') as string || undefined,
            icon: formData.get('icon') as string || 'folder_open',
            color: formData.get('color') as string || '#1B365D',
        };

        try {
            const newProject = await projectService.createProject(projectData);
            console.log('[ProjectList] Project created:', newProject);
            
            this.closeCreateModal();
            await this.loadProjects();
            
            this.showSuccess('Progetto creato con successo!');
        } catch (error) {
            console.error('[ProjectList] Failed to create project:', error);
            this.showError(error instanceof Error ? error.message : 'Errore durante la creazione');
        }
    }

    /**
     * Handle delete project
     */
    private async handleDeleteProject(projectId: number): Promise<void> {
        const result = await Swal.fire({
            title: 'Elimina progetto',
            text: 'Sei sicuro di voler eliminare questo progetto? Tutti i documenti saranno eliminati.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sì, elimina tutto',
            cancelButtonText: 'Annulla',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            await projectService.deleteProject(projectId);
            console.log('[ProjectList] Project deleted:', projectId);
            
            await this.loadProjects();
            this.showSuccess('Progetto eliminato con successo!');
        } catch (error) {
            console.error('[ProjectList] Failed to delete project:', error);
            this.showError(error instanceof Error ? error.message : 'Errore durante l\'eliminazione');
        }
    }

    /**
     * Show success message
     */
    private showSuccess(message: string): void {
        Swal.fire({
            icon: 'success',
            title: 'Operazione completata',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    /**
     * Show error message
     */
    private showError(message: string): void {
        Swal.fire({
            icon: 'error',
            title: 'Errore',
            text: message,
            confirmButtonText: 'Chiudi',
            confirmButtonColor: '#3b82f6'
        });
    }

    /**
     * Escape HTML
     */
    private escapeHtml(text: string): string {
        const map: Record<string, string> = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

