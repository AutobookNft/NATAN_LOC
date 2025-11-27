/**
 * @Oracode Types: NATAN Projects & Documents
 * 🎯 Purpose: TypeScript interfaces for project management
 * 
 * @package resources/js/natan/types
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 */

/**
 * Project settings configuration
 */
export interface ProjectSettings {
    max_documents: number;
    max_size_mb: number;
    auto_embed: boolean;
    priority_rag: boolean;
    allowed_types: string[];
}

/**
 * NATAN Project
 */
export interface NatanProject {
    id: number;
    creator_id: number;
    collection_name: string;
    name?: string; // Accessor alias
    description: string | null;
    icon: string;
    color: string;
    settings: ProjectSettings;
    is_active: boolean;
    context: 'pa_project';
    created_at: string;
    updated_at: string;
    
    // Relationships (when loaded)
    documents?: NatanDocument[];
    documents_count?: number;
    ready_documents_count?: number;
}

/**
 * Document processing metadata
 */
export interface DocumentProcessingMetadata {
    pages?: number;
    words?: number;
    tokens_count?: number;
    chunks_count?: number;
    embedding_model?: string;
    processed_at?: string;
}

/**
 * NATAN Document (PA document in project)
 */
export interface NatanDocument {
    id: number;
    collection_id: number;
    user_id: number | null;
    tenant_id: number | null;
    title: string;
    description: string | null;
    original_filename: string;
    mime_type: string;
    size_bytes: number;
    pa_file_path: string;
    document_status: 'pending' | 'processing' | 'ready' | 'failed';
    document_error_message: string | null;
    processing_metadata: DocumentProcessingMetadata | null;
    document_processed_at: string | null;
    context: 'pa_document';
    created_at: string;
    updated_at: string;
    
    // Relationships (when loaded)
    chunks_count?: number;
    
    // Computed
    file_size_formatted?: string;
}

/**
 * API Response wrappers
 */
export interface ApiResponse<T> {
    success: boolean;
    message?: string;
    errors?: Record<string, string[]>;
    data?: T;
}

export interface ProjectsListResponse {
    success: boolean;
    projects: NatanProject[];
}

export interface ProjectResponse {
    success: boolean;
    project: NatanProject;
    message?: string;
}

export interface DocumentsListResponse {
    success: boolean;
    documents: NatanDocument[];
}

export interface DocumentUploadResponse {
    success: boolean;
    document: NatanDocument;
    message?: string;
}

/**
 * Form data interfaces
 */
export interface ProjectFormData {
    name: string;
    description?: string;
    icon?: string;
    color?: string;
    settings?: Partial<ProjectSettings>;
}

export interface DocumentUploadFormData {
    file: File;
    title?: string;
    description?: string;
}

/**
 * UI State
 */
export interface ProjectsUIState {
    loading: boolean;
    error: string | null;
    projects: NatanProject[];
    selectedProject: NatanProject | null;
}

export interface DocumentsUIState {
    loading: boolean;
    uploading: boolean;
    uploadProgress: number;
    error: string | null;
    documents: NatanDocument[];
}

