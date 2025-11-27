/**
 * @Oracode Component: Project Detail with Document Management
 * 🎯 Purpose: Display project details and manage documents (upload, list, delete)
 * 
 * @package resources/js/natan/components
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 */

import type { NatanProject, NatanDocument, DocumentUploadFormData } from '../types/projects';
import { projectService } from '../services/ProjectService';
import Swal from 'sweetalert2';

export class ProjectDetail {
    private container: HTMLElement;
    private projectId: number;
    private project: NatanProject | null = null;
    private documents: NatanDocument[] = [];
    private loading: boolean = false;
    private uploading: boolean = false;
    private uploadProgress: number = 0;
    private uploadQueue: File[] = [];
    private currentUploadFile: string | null = null;
    private completedUploads: number = 0;
    private failedUploads: number = 0;
    private uploadResults: Map<string, 'pending' | 'uploading' | 'success' | 'failed'> = new Map();

    constructor(containerId: string, projectId: number) {
        const element = document.getElementById(containerId);
        if (!element) {
            throw new Error(`Container #${containerId} not found`);
        }
        this.container = element;
        this.projectId = projectId;
        this.init();
    }

    private async init(): Promise<void> {
        await this.loadProject();
        
        // Don't proceed if project failed to load
        if (!this.project) {
            console.error('[ProjectDetail] Project not loaded, cannot initialize component');
            return;
        }
        
        await this.loadDocuments();
        this.render();
        this.attachEventListeners();
    }

    /**
     * Load project details
     */
    private async loadProject(): Promise<void> {
        this.loading = true;

        try {
            console.log('[ProjectDetail] Loading project:', this.projectId);
            this.project = await projectService.getProject(this.projectId);
            console.log('[ProjectDetail] Project loaded:', this.project);
        } catch (error) {
            console.error('[ProjectDetail] Failed to load project:', error);
            this.showError(error instanceof Error ? error.message : 'Failed to load project');
            this.project = null; // Ensure it's null
        } finally {
            this.loading = false;
        }
    }

    /**
     * Load documents
     */
    private async loadDocuments(): Promise<void> {
        try {
            console.log('[ProjectDetail] Fetching documents for project:', this.projectId);
            this.documents = await projectService.getDocuments(this.projectId);
            console.log('[ProjectDetail] Documents loaded:', this.documents);
            // Don't render here - init() will call render() after both loads complete
        } catch (error) {
            console.error('[ProjectDetail] Failed to load documents:', error);
            this.showError(error instanceof Error ? error.message : 'Failed to load documents');
            // Set empty array on error
            this.documents = [];
        }
    }

    /**
     * Render component
     */
    private render(): void {
        if (this.loading || !this.project) {
            this.container.innerHTML = this.renderLoading();
            return;
        }

        this.container.innerHTML = `
            <div class="project-detail-container">
                <!-- Header -->
                ${this.renderHeader()}

                <!-- Upload Zone -->
                ${this.renderUploadZone()}

                <!-- Upload Queue (if files are being processed) -->
                ${this.renderUploadQueue()}

                <!-- Documents List -->
                ${this.renderDocumentsList()}
            </div>
        `;

        this.attachEventListeners();
    }

    /**
     * Render header
     */
    private renderHeader(): string {
        if (!this.project) return '';

        return `
            <div class="mb-8">
                <!-- Breadcrumb -->
                <nav class="text-sm mb-4">
                    <a href="/natan/projects" class="text-natan-blue hover:underline">Progetti</a>
                    <span class="text-gray-400 mx-2">/</span>
                    <span class="text-gray-700">${this.escapeHtml(this.project.collection_name || this.project.name || '')}</span>
                </nav>

                <!-- Project Info -->
                <div class="flex items-start gap-6">
                    <div 
                        class="flex-shrink-0 w-20 h-20 rounded-xl flex items-center justify-center"
                        style="background-color: ${this.project.color}20"
                    >
                        <span class="material-icons text-4xl" style="color: ${this.project.color}">
                            ${this.project.icon}
                        </span>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-natan-dark mb-2">
                            ${this.escapeHtml(this.project.collection_name || this.project.name || '')}
                        </h1>
                        <div class="mb-2">
                            ${this.project.description ? `
                                <p class="text-gray-600 mb-2" id="project-description-text">${this.escapeHtml(this.project.description)}</p>
                            ` : `
                                <p class="text-gray-400 italic mb-2" id="project-description-text">Nessuna descrizione</p>
                            `}
                            <button 
                                type="button" 
                                id="edit-description-btn"
                                class="text-sm text-natan-blue hover:underline"
                            >
                                ${this.project.description ? 'Modifica descrizione' : 'Aggiungi descrizione'}
                            </button>
                        </div>
                        <div class="flex items-center gap-6 mt-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <span class="material-icons text-base">description</span>
                                <span>${this.documents.length} documenti</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-icons text-base">check_circle</span>
                                <span>${this.documents.filter(d => d.document_status === 'ready').length} pronti</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render upload zone
     */
    private renderUploadZone(): string {
        if (this.uploading) {
            return this.renderUploadProgress();
        }

        const maxDocuments = this.project?.settings?.max_documents || 50;
        const canUpload = this.documents.length < maxDocuments;

        if (!canUpload) {
            return `
                <div class="mb-8 p-6 bg-orange-50 border border-orange-200 rounded-lg text-center">
                    <span class="material-icons text-orange-500 text-4xl mb-2">warning</span>
                    <p class="text-orange-800 font-medium">Limite documenti raggiunto</p>
                    <p class="text-orange-600 text-sm mt-1">Massimo ${maxDocuments} documenti per progetto</p>
                </div>
            `;
        }

        return `
            <div class="mb-8">
                <div 
                    id="upload-dropzone"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-natan-blue hover:bg-natan-blue-light transition-all cursor-pointer mb-4"
                >
                    <input 
                        type="file" 
                        id="file-input" 
                        class="hidden"
                        accept=".pdf,.docx,.txt,.csv,.xlsx,.md"
                        multiple
                    />
                    <span class="material-icons text-6xl text-gray-400 mb-4">cloud_upload</span>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Carica Documenti</h3>
                    <p class="text-gray-500 mb-4">
                        Trascina i file qui o <button type="button" id="browse-btn" class="text-natan-blue hover:underline font-medium">sfoglia</button>
                    </p>
                    <p class="text-sm text-gray-400">
                        Formati supportati: PDF, DOCX, TXT, CSV, XLSX, MD • Max ${this.project?.settings?.max_size_mb || 10}MB per file
                    </p>
                </div>
                
                <button 
                    type="button"
                    id="add-text-note-btn"
                    class="w-full px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 hover:border-natan-blue hover:bg-natan-blue-light rounded-lg transition-all flex items-center justify-center gap-2"
                >
                    <span class="material-icons">note_add</span>
                    <span class="font-medium">Aggiungi Nota Testuale</span>
                </button>
            </div>
        `;
    }

    /**
     * Render upload progress (legacy - now using renderUploadQueue)
     */
    private renderUploadProgress(): string {
        const totalFiles = this.completedUploads + this.failedUploads + this.uploadQueue.length + (this.uploading ? 1 : 0);
        const currentFileNumber = this.completedUploads + this.failedUploads + 1;
        
        return `
            <div class="mb-8 p-6 bg-white border border-gray-200 rounded-lg">
                <div class="flex items-center gap-4 mb-3">
                    <div class="flex-shrink-0">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-natan-blue"></div>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-900 font-medium">Caricamento ${currentFileNumber} di ${totalFiles}</p>
                        <p class="text-sm text-gray-500">
                            ${this.currentUploadFile || 'Attendere...'}
                            ${this.uploadQueue.length > 0 ? ` • ${this.uploadQueue.length} in coda` : ''}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-natan-blue">${Math.round(this.uploadProgress)}%</p>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                    <div 
                        class="bg-natan-blue h-2 rounded-full transition-all duration-300"
                        style="width: ${this.uploadProgress}%"
                    ></div>
                </div>
                ${this.completedUploads > 0 || this.failedUploads > 0 ? `
                    <div class="flex gap-4 text-sm mt-3">
                        ${this.completedUploads > 0 ? `<span class="text-green-600">✓ ${this.completedUploads} completati</span>` : ''}
                        ${this.failedUploads > 0 ? `<span class="text-red-600">✗ ${this.failedUploads} falliti</span>` : ''}
                    </div>
                ` : ''}
            </div>
        `;
    }

    /**
     * Render upload queue with file-by-file status
     */
    private renderUploadQueue(): string {
        // Only show if there are files in process or results to show
        if (this.uploadResults.size === 0) {
            return '';
        }

        const allFiles = Array.from(this.uploadResults.entries());
        const pending = allFiles.filter(([_, status]) => status === 'pending');
        const uploading = allFiles.filter(([_, status]) => status === 'uploading');
        const success = allFiles.filter(([_, status]) => status === 'success');
        const failed = allFiles.filter(([_, status]) => status === 'failed');

        return `
            <div class="mb-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Caricamento File (${allFiles.length})
                        </h3>
                        <div class="flex items-center gap-4 text-sm">
                            ${success.length > 0 ? `<span class="text-green-600 font-medium">✓ ${success.length} completati</span>` : ''}
                            ${uploading.length > 0 ? `<span class="text-blue-600 font-medium">⟳ ${uploading.length} in corso</span>` : ''}
                            ${pending.length > 0 ? `<span class="text-gray-600 font-medium">◷ ${pending.length} in attesa</span>` : ''}
                            ${failed.length > 0 ? `<span class="text-red-600 font-medium">✗ ${failed.length} falliti</span>` : ''}
                        </div>
                    </div>
                </div>

                <!-- File List -->
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    ${allFiles.map(([fileName, status]) => this.renderQueueFileItem(fileName, status)).join('')}
                </div>

                <!-- Footer Actions -->
                ${(success.length > 0 || failed.length > 0) && pending.length === 0 && uploading.length === 0 ? `
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button 
                            id="clear-queue-btn"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Chiudi
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    /**
     * Render single file item in queue
     */
    private renderQueueFileItem(fileName: string, status: 'pending' | 'uploading' | 'success' | 'failed'): string {
        let statusIcon = '';
        let statusClass = '';
        let statusLabel = '';

        switch (status) {
            case 'pending':
                statusIcon = 'schedule';
                statusClass = 'text-gray-500';
                statusLabel = 'In attesa';
                break;
            case 'uploading':
                statusIcon = 'cloud_upload';
                statusClass = 'text-blue-600';
                statusLabel = 'Caricamento...';
                break;
            case 'success':
                statusIcon = 'check_circle';
                statusClass = 'text-green-600';
                statusLabel = 'Completato';
                break;
            case 'failed':
                statusIcon = 'error';
                statusClass = 'text-red-600';
                statusLabel = 'Errore';
                break;
        }

        return `
            <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-4">
                    <!-- Status Icon -->
                    <div class="flex-shrink-0">
                        <span class="material-icons ${statusClass} ${status === 'uploading' ? 'animate-pulse' : ''}">
                            ${statusIcon}
                        </span>
                    </div>

                    <!-- File Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            ${this.escapeHtml(fileName)}
                        </p>
                        <p class="text-xs ${statusClass} mt-0.5">
                            ${statusLabel}
                        </p>
                    </div>

                    <!-- Progress Bar (only for uploading) -->
                    ${status === 'uploading' ? `
                        <div class="flex-shrink-0 w-32">
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div 
                                    class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                    style="width: ${this.uploadProgress}%"
                                ></div>
                            </div>
                            <p class="text-xs text-gray-500 text-right mt-1">${Math.round(this.uploadProgress)}%</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Render documents list
     */
    private renderDocumentsList(): string {
        if (this.documents.length === 0) {
            return `
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <span class="material-icons text-6xl text-gray-400 mb-4">description</span>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessun documento</h3>
                    <p class="text-gray-500">Carica il primo documento per iniziare</p>
                </div>
            `;
        }

        return `
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Documenti (${this.documents.length})</h2>
                <div class="space-y-3">
                    ${this.documents.map(doc => this.renderDocumentCard(doc)).join('')}
                </div>
            </div>
        `;
    }

    /**
     * Render single document card
     */
    private renderDocumentCard(doc: NatanDocument): string {
        const statusClass = projectService.getStatusClass(doc.document_status);
        const statusIcon = projectService.getStatusIcon(doc.document_status);
        const fileSize = projectService.formatFileSize(doc.size_bytes);

        return `
            <div class="document-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-natan-blue-light rounded-lg flex items-center justify-center">
                            <span class="material-icons text-natan-blue">description</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-lg font-semibold text-gray-900 truncate">
                            ${this.escapeHtml(doc.title)}
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">
                            ${this.escapeHtml(doc.original_filename)} • ${fileSize}
                        </p>
                        
                        <!-- Status -->
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                                <span class="material-icons text-xs">${statusIcon}</span>
                                ${this.getStatusLabel(doc.document_status)}
                            </span>
                            ${doc.chunks_count ? `
                                <span class="text-xs text-gray-500">
                                    ${doc.chunks_count} chunks
                                </span>
                            ` : ''}
                        </div>

                        ${doc.document_error_message ? `
                            <p class="text-sm text-red-600 mt-2">
                                <span class="material-icons text-xs align-middle">error</span>
                                ${this.escapeHtml(doc.document_error_message)}
                            </p>
                        ` : ''}
                    </div>

                    <!-- Actions -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <button 
                            class="btn-icon-sm delete-document-btn"
                            data-document-id="${doc.id}"
                            title="Elimina documento"
                        >
                            <span class="material-icons text-base">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render loading state
     */
    private renderLoading(): string {
        return `
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-natan-blue"></div>
                    <p class="mt-4 text-gray-600">Caricamento...</p>
                </div>
            </div>
        `;
    }

    /**
     * Attach event listeners
     */
    private attachEventListeners(): void {
        // Upload dropzone
        const dropzone = document.getElementById('upload-dropzone');
        const fileInput = document.getElementById('file-input') as HTMLInputElement;
        const browseBtn = document.getElementById('browse-btn');

        if (dropzone && fileInput) {
            // Click to browse
            dropzone.addEventListener('click', (e) => {
                // Don't open file picker if clicking on browse button (it has its own handler)
                if (e.target === browseBtn) {
                    return;
                }
                fileInput.click();
            });

            if (browseBtn) {
                browseBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    fileInput.click();
                });
            }

            // File input change
            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files.length > 0) {
                    const files = Array.from(fileInput.files);
                    fileInput.value = ''; // Reset immediately
                    
                    // Process files with slight delay to avoid re-trigger
                    this.handleFileUpload(files);
                }
            });

            // Drag and drop
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('border-natan-blue', 'bg-natan-blue-light');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('border-natan-blue', 'bg-natan-blue-light');
            });

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-natan-blue', 'bg-natan-blue-light');

                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    this.handleFileUpload(Array.from(files));
                }
            });
        }

        // Add text note button
        const addTextNoteBtn = document.getElementById('add-text-note-btn');
        if (addTextNoteBtn) {
            addTextNoteBtn.addEventListener('click', () => this.handleAddTextNote());
        }

        // Edit description button
        const editDescBtn = document.getElementById('edit-description-btn');
        if (editDescBtn) {
            editDescBtn.addEventListener('click', () => this.handleEditDescription());
        }

        // Delete buttons
        const deleteButtons = document.querySelectorAll('.delete-document-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const documentId = (btn as HTMLElement).dataset.documentId;
                if (documentId) {
                    this.handleDeleteDocument(parseInt(documentId));
                }
            });
        });

        // Clear queue button
        const clearQueueBtn = document.getElementById('clear-queue-btn');
        if (clearQueueBtn) {
            clearQueueBtn.addEventListener('click', () => {
                this.clearUploadResults();
            });
        }
    }

    /**
     * Clear upload results and reset counters
     */
    private clearUploadResults(): void {
        this.uploadResults.clear();
        this.completedUploads = 0;
        this.failedUploads = 0;
        this.render();
    }

    /**
     * Handle file upload - add files to queue
     */
    private async handleFileUpload(files: File[]): Promise<void> {
        console.log(`[ProjectDetail] Adding ${files.length} file(s) to upload queue`);
        
        // Initialize upload results for each file
        files.forEach(file => {
            this.uploadResults.set(file.name, 'pending');
        });
        
        // Add files to queue
        this.uploadQueue.push(...files);
        
        // Start processing if not already uploading
        if (!this.uploading) {
            // Use setTimeout to avoid immediate re-render that could re-trigger file picker
            setTimeout(() => {
                this.processUploadQueue();
            }, 100);
        }
        // If already uploading, files are added to queue and will be processed automatically
    }
    
    /**
     * Process upload queue sequentially
     */
    private async processUploadQueue(): Promise<void> {
        // Initial render to show queue
        this.render();
        
        while (this.uploadQueue.length > 0) {
            const file = this.uploadQueue.shift()!;
            await this.uploadSingleFile(file);
        }
        
        // All uploads complete
        console.log(`[ProjectDetail] Upload queue complete: ${this.completedUploads} successful, ${this.failedUploads} failed`);
        
        this.currentUploadFile = null;
        
        // Final refresh to show results
        await this.loadDocuments();
        this.render();
        
        // Don't auto-clear results - let user close manually
        // They can review what succeeded/failed
    }

    /**
     * Upload single file
     */
    private async uploadSingleFile(file: File): Promise<void> {
        this.uploading = true;
        this.uploadProgress = 0;
        this.currentUploadFile = file.name;
        
        // Mark as uploading
        this.uploadResults.set(file.name, 'uploading');
        this.render();

        const formData: DocumentUploadFormData = {
            file,
            title: file.name.replace(/\.[^/.]+$/, ''), // Remove extension
        };

        try {
            const document = await projectService.uploadDocument(
                this.projectId,
                formData,
                (progress) => {
                    this.uploadProgress = progress;
                    this.render();
                }
            );

            console.log(`[ProjectDetail] Document "${file.name}" uploaded successfully:`, document);
            this.completedUploads++;
            this.uploadResults.set(file.name, 'success');
            
        } catch (error) {
            console.error(`[ProjectDetail] Upload failed for "${file.name}":`, error);
            this.failedUploads++;
            this.uploadResults.set(file.name, 'failed');
            // Don't show error alert for each file - just count it
        } finally {
            this.uploading = false;
            this.uploadProgress = 0;
        }
    }

    /**
     * Handle delete document
     */
    private async handleDeleteDocument(documentId: number): Promise<void> {
        const result = await Swal.fire({
            title: 'Elimina documento',
            text: 'Sei sicuro di voler eliminare questo documento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sì, elimina',
            cancelButtonText: 'Annulla',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            await projectService.deleteDocument(this.projectId, documentId);
            console.log('[ProjectDetail] Document deleted:', documentId);
            
            await this.loadDocuments();
            this.render();
            
            this.showSuccess('Documento eliminato con successo!');
        } catch (error) {
            console.error('[ProjectDetail] Delete failed:', error);
            this.showError(error instanceof Error ? error.message : 'Errore durante l\'eliminazione');
        }
    }

    /**
     * Handle edit description
     */
    private async handleEditDescription(): Promise<void> {
        if (!this.project) return;

        const result = await Swal.fire({
            title: this.project.description ? 'Modifica Descrizione' : 'Aggiungi Descrizione',
            html: `
                <p class="text-gray-600 text-sm mb-4">
                    Descrivi il progetto e il suo scopo. Questa informazione sarà disponibile durante le conversazioni.
                </p>
                <textarea 
                    id="description-input" 
                    class="swal2-textarea w-full"
                    rows="6"
                    placeholder="Es: Questo progetto contiene documenti legali relativi al contratto X..."
                >${this.project.description || ''}</textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Salva',
            cancelButtonText: 'Annulla',
            preConfirm: () => {
                const input = document.getElementById('description-input') as HTMLTextAreaElement;
                const value = input.value.trim();
                
                if (value.length > 2000) {
                    Swal.showValidationMessage('La descrizione non può superare 2000 caratteri');
                    return false;
                }
                
                return value;
            }
        });

        if (!result.isConfirmed || result.value === undefined) return;

        try {
            const response = await fetch(`/natan/projects/${this.projectId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    description: result.value
                })
            });

            if (!response.ok) {
                throw new Error('Errore durante il salvataggio della descrizione');
            }

            const data = await response.json();
            
            // Update local project data
            if (this.project) {
                this.project.description = result.value;
            }

            // Update UI
            const descElement = document.getElementById('project-description-text');
            if (descElement) {
                descElement.textContent = result.value || 'Nessuna descrizione disponibile';
            }
            
            const editBtn = document.getElementById('edit-description-btn');
            if (editBtn) {
                editBtn.textContent = result.value ? 'Modifica descrizione' : 'Aggiungi descrizione';
            }

            this.showSuccess('Descrizione salvata con successo!');
        } catch (error) {
            console.error('[ProjectDetail] Failed to update description:', error);
            this.showError(error instanceof Error ? error.message : 'Errore durante il salvataggio');
        }
    }

    /**
     * Handle add text note
     */
    private async handleAddTextNote(): Promise<void> {
        const result = await Swal.fire({
            title: 'Aggiungi Nota Testuale',
            html: `
                <div class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titolo</label>
                        <input 
                            id="note-title-input" 
                            type="text"
                            class="swal2-input w-full"
                            placeholder="Es: Note riunione 12/01/2024"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contenuto</label>
                        <textarea 
                            id="note-content-input" 
                            class="swal2-textarea w-full"
                            rows="8"
                            placeholder="Scrivi il contenuto della nota..."
                            style="resize: both; min-height: 150px; max-height: 600px;"
                        ></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Salva',
            cancelButtonText: 'Annulla',
            width: '700px',
            preConfirm: () => {
                const titleInput = document.getElementById('note-title-input') as HTMLInputElement;
                const contentInput = document.getElementById('note-content-input') as HTMLTextAreaElement;
                
                const title = titleInput.value.trim();
                const content = contentInput.value.trim();
                
                if (!title) {
                    Swal.showValidationMessage('Il titolo è obbligatorio');
                    return false;
                }
                
                if (!content) {
                    Swal.showValidationMessage('Il contenuto è obbligatorio');
                    return false;
                }
                
                if (title.length > 255) {
                    Swal.showValidationMessage('Il titolo non può superare 255 caratteri');
                    return false;
                }
                
                return { title, content };
            }
        });

        if (!result.isConfirmed || !result.value) return;

        try {
            const response = await fetch(`/natan/projects/${this.projectId}/text-notes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    title: result.value.title,
                    content: result.value.content
                })
            });

            if (!response.ok) {
                throw new Error('Errore durante il salvataggio della nota');
            }

            // Reload documents to show the new text note
            await this.loadDocuments();
            this.render();

            this.showSuccess('Nota testuale salvata con successo!');
        } catch (error) {
            console.error('[ProjectDetail] Failed to save text note:', error);
            this.showError(error instanceof Error ? error.message : 'Errore durante il salvataggio');
        }
    }

    /**
     * Get status label
     */
    private getStatusLabel(status: string): string {
        const labels: Record<string, string> = {
            'pending': 'In attesa',
            'processing': 'Elaborazione',
            'ready': 'Pronto',
            'failed': 'Errore',
        };
        return labels[status] || status;
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

