<?php

namespace App\Http\Controllers;

use App\Models\NatanProject;
use App\Models\NatanDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * @Oracode Controller: NATAN Document Management
 * 🎯 Purpose: Upload and manage documents in NATAN projects
 * 🛡️ Privacy: User can only access documents in their own projects
 * 🧱 Core Logic: Upload, list, delete documents with processing queue
 * 
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Documents Integration FEGI)
 * @date 2025-11-21
 * @purpose Manage NATAN documents (unified with FEGI egis)
 */
class NatanDocumentController extends Controller
{
    /**
     * List documents in project
     * 
     * GET /natan/projects/{projectId}/documents
     */
    public function index(int $projectId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify project ownership
            $project = NatanProject::forUser($user)->findOrFail($projectId);

            // Get documents with chunks count
            $documents = NatanDocument::forProject($project)
                ->withCount('chunks')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('[NatanDocument] Documents list accessed', [
                'user_id' => $user->id,
                'project_id' => $projectId,
                'documents_count' => $documents->count(),
            ]);

            return response()->json([
                'success' => true,
                'documents' => $documents,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('[NatanDocument] Index error', [
                'user_id' => Auth::id(),
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load documents',
            ], 500);
        }
    }

    /**
     * Upload document to project
     * 
     * POST /natan/projects/{projectId}/documents
     */
    public function store(Request $request, int $projectId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify project ownership
            $project = NatanProject::forUser($user)->findOrFail($projectId);

            // Check if project can accept more documents
            if (!$project->canAddDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project has reached maximum documents limit',
                ], 422);
            }

            // Validate upload
            $maxSizeMb = $project->settings['max_size_mb'] ?? 10;
            $maxSizeKb = $maxSizeMb * 1024;
            $allowedTypes = implode(',', $project->getAllowedFileTypes());

            $validated = $request->validate([
                'file' => "required|file|max:{$maxSizeKb}|extensions:{$allowedTypes}",
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $file = $request->file('file');
            $originalFilename = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $sizeBytes = $file->getSize();

            // Store file
            $storagePath = $file->store('natan/projects/' . $projectId, 'private');

            // Create document record
            $document = NatanDocument::create([
                'collection_id' => $projectId,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'title' => $validated['title'] ?? pathinfo($originalFilename, PATHINFO_FILENAME),
                'description' => $validated['description'] ?? null,
                'original_filename' => $originalFilename,
                'mime_type' => $mimeType,
                'size_bytes' => $sizeBytes,
                'pa_file_path' => $storagePath,
                'document_status' => 'pending',
            ]);

            Log::info('[NatanDocument] Document uploaded', [
                'user_id' => $user->id,
                'project_id' => $projectId,
                'document_id' => $document->id,
                'filename' => $originalFilename,
                'size_bytes' => $sizeBytes,
            ]);

            // TODO: Dispatch job for processing (PDF extraction → chunking → embedding)
            // ProcessNatanDocumentJob::dispatch($document);

            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Document uploaded successfully',
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanDocument] Upload error', [
                'user_id' => Auth::id(),
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
            ], 500);
        }
    }

    /**
     * Store text note (text-based document)
     * 
     * POST /natan/projects/{projectId}/text-notes
     */
    public function storeTextNote(Request $request, int $projectId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify project ownership
            $project = NatanProject::forUser($user)->findOrFail($projectId);

            // Check if project can accept more documents
            if (!$project->canAddDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project has reached maximum documents limit',
                ], 422);
            }

            // Validate input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:50000', // 50KB max for text notes
            ]);

            // Create a temporary text file for processing
            $filename = 'note-' . time() . '-' . uniqid() . '.txt';
            $storagePath = 'natan/projects/' . $projectId . '/' . $filename;
            
            // Store text content as file
            Storage::disk('private')->put($storagePath, $validated['content']);

            // Get file size
            $sizeBytes = Storage::disk('private')->size($storagePath);

            // Create document record
            $document = NatanDocument::create([
                'collection_id' => $projectId,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'title' => $validated['title'],
                'description' => 'Nota testuale',
                'original_filename' => $filename,
                'mime_type' => 'text/plain',
                'size_bytes' => $sizeBytes,
                'pa_file_path' => $storagePath,
                'document_status' => 'pending',
            ]);

            Log::info('[NatanDocument] Text note created', [
                'user_id' => $user->id,
                'project_id' => $projectId,
                'document_id' => $document->id,
                'title' => $validated['title'],
                'content_length' => strlen($validated['content']),
            ]);

            // TODO: Dispatch job for processing (chunking → embedding)
            // ProcessNatanDocumentJob::dispatch($document);

            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Text note created successfully',
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanDocument] Text note creation error', [
                'user_id' => Auth::id(),
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create text note',
            ], 500);
        }
    }

    /**
     * Delete document
     * 
     * DELETE /natan/projects/{projectId}/documents/{documentId}
     */
    public function destroy(int $projectId, int $documentId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify project ownership
            $project = NatanProject::forUser($user)->findOrFail($projectId);

            // Get document
            $document = NatanDocument::forProject($project)->findOrFail($documentId);

            // Delete file from storage
            if ($document->pa_file_path && Storage::disk('private')->exists($document->pa_file_path)) {
                Storage::disk('private')->delete($document->pa_file_path);
            }

            $documentTitle = $document->title;

            // Delete document (will cascade delete chunks via FK)
            $document->delete();

            Log::info('[NatanDocument] Document deleted', [
                'user_id' => $user->id,
                'project_id' => $projectId,
                'document_id' => $documentId,
                'document_title' => $documentTitle,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('[NatanDocument] Delete error', [
                'user_id' => Auth::id(),
                'project_id' => $projectId,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
            ], 500);
        }
    }
}
