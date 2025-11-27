/**
 * @Oracode Service: Project API Service
 * 🎯 Purpose: API calls for project management
 * 
 * @package resources/js/natan/services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 */

import type {
    NatanProject,
    NatanDocument,
    ProjectsListResponse,
    ProjectResponse,
    DocumentsListResponse,
    DocumentUploadResponse,
    ProjectFormData,
    DocumentUploadFormData
} from '../types/projects';

/**
 * ProjectService - Handles all project-related API calls
 */
export class ProjectService {
    private baseUrl: string = '/natan';

    /**
     * Get CSRF token from meta tag
     */
    private getCsrfToken(): string {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            throw new Error('CSRF token not found');
        }
        return token;
    }

    /**
     * Fetch all projects for current user
     */
    async getProjects(): Promise<NatanProject[]> {
        const response = await fetch(`${this.baseUrl}/projects`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Failed to fetch projects: ${response.statusText}`);
        }

        const data: ProjectsListResponse = await response.json();
        
        if (!data.success) {
            throw new Error('Failed to fetch projects');
        }

        return data.projects;
    }

    /**
     * Get single project details
     */
    async getProject(projectId: number): Promise<NatanProject> {
        const response = await fetch(`${this.baseUrl}/projects/${projectId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Project not found');
            }
            throw new Error(`Failed to fetch project: ${response.statusText}`);
        }

        const data: ProjectResponse = await response.json();
        
        if (!data.success || !data.project) {
            throw new Error('Failed to fetch project');
        }

        return data.project;
    }

    /**
     * Create new project
     */
    async createProject(formData: ProjectFormData): Promise<NatanProject> {
        const response = await fetch(`${this.baseUrl}/projects`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData),
        });

        if (!response.ok) {
            const error = await response.json();
            if (error.errors) {
                const firstError = Object.values(error.errors)[0] as string[];
                throw new Error(firstError[0]);
            }
            throw new Error(`Failed to create project: ${response.statusText}`);
        }

        const data: ProjectResponse = await response.json();
        
        if (!data.success || !data.project) {
            throw new Error(data.message || 'Failed to create project');
        }

        return data.project;
    }

    /**
     * Update existing project
     */
    async updateProject(projectId: number, formData: Partial<ProjectFormData>): Promise<NatanProject> {
        const response = await fetch(`${this.baseUrl}/projects/${projectId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData),
        });

        if (!response.ok) {
            const error = await response.json();
            if (error.errors) {
                const firstError = Object.values(error.errors)[0] as string[];
                throw new Error(firstError[0]);
            }
            throw new Error(`Failed to update project: ${response.statusText}`);
        }

        const data: ProjectResponse = await response.json();
        
        if (!data.success || !data.project) {
            throw new Error(data.message || 'Failed to update project');
        }

        return data.project;
    }

    /**
     * Delete project
     */
    async deleteProject(projectId: number): Promise<void> {
        const response = await fetch(`${this.baseUrl}/projects/${projectId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Project not found');
            }
            throw new Error(`Failed to delete project: ${response.statusText}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to delete project');
        }
    }

    /**
     * Get documents in project
     */
    async getDocuments(projectId: number): Promise<NatanDocument[]> {
        const response = await fetch(`${this.baseUrl}/projects/${projectId}/documents`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Project not found');
            }
            throw new Error(`Failed to fetch documents: ${response.statusText}`);
        }

        const data: DocumentsListResponse = await response.json();
        
        if (!data.success) {
            throw new Error('Failed to fetch documents');
        }

        return data.documents;
    }

    /**
     * Upload document to project
     */
    async uploadDocument(
        projectId: number,
        formData: DocumentUploadFormData,
        onProgress?: (progress: number) => void
    ): Promise<NatanDocument> {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            // Progress tracking
            if (onProgress) {
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        onProgress(percentComplete);
                    }
                });
            }

            // Response handling
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const data: DocumentUploadResponse = JSON.parse(xhr.responseText);
                        if (data.success && data.document) {
                            resolve(data.document);
                        } else {
                            reject(new Error(data.message || 'Upload failed'));
                        }
                    } catch (e) {
                        reject(new Error('Invalid response from server'));
                    }
                } else {
                    try {
                        const error = JSON.parse(xhr.responseText);
                        if (error.errors) {
                            const firstError = Object.values(error.errors)[0] as string[];
                            reject(new Error(firstError[0]));
                        } else {
                            reject(new Error(error.message || `Upload failed: ${xhr.statusText}`));
                        }
                    } catch (e) {
                        reject(new Error(`Upload failed: ${xhr.statusText}`));
                    }
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Network error during upload'));
            });

            xhr.addEventListener('abort', () => {
                reject(new Error('Upload cancelled'));
            });

            // Prepare form data
            const uploadFormData = new FormData();
            uploadFormData.append('file', formData.file);
            if (formData.title) {
                uploadFormData.append('title', formData.title);
            }
            if (formData.description) {
                uploadFormData.append('description', formData.description);
            }

            // Send request
            xhr.open('POST', `${this.baseUrl}/projects/${projectId}/documents`);
            xhr.setRequestHeader('X-CSRF-TOKEN', this.getCsrfToken());
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(uploadFormData);
        });
    }

    /**
     * Delete document
     */
    async deleteDocument(projectId: number, documentId: number): Promise<void> {
        const response = await fetch(`${this.baseUrl}/projects/${projectId}/documents/${documentId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Document not found');
            }
            throw new Error(`Failed to delete document: ${response.statusText}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to delete document');
        }
    }

    /**
     * Format file size for display
     */
    formatFileSize(bytes: number): string {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Get status badge class
     */
    getStatusClass(status: string): string {
        const classes: Record<string, string> = {
            'pending': 'bg-gray-100 text-gray-800',
            'processing': 'bg-blue-100 text-blue-800',
            'ready': 'bg-green-100 text-green-800',
            'failed': 'bg-red-100 text-red-800',
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    /**
     * Get status icon
     */
    getStatusIcon(status: string): string {
        const icons: Record<string, string> = {
            'pending': 'schedule',
            'processing': 'hourglass_empty',
            'ready': 'check_circle',
            'failed': 'error',
        };
        return icons[status] || 'help';
    }
}

// Export singleton instance
export const projectService = new ProjectService();

