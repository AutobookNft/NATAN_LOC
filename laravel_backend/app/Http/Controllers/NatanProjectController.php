<?php

namespace App\Http\Controllers;

use App\Models\NatanProject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @Oracode Controller: NATAN Project Management
 * 🎯 Purpose: Manage PA projects for document organization
 * 🛡️ Privacy: User can only access their own projects
 * 🧱 Core Logic: CRUD operations for NATAN projects
 * 
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Integration FEGI)
 * @date 2025-11-21
 * @purpose Manage NATAN projects (unified with FEGI collections)
 */
class NatanProjectController extends Controller
{
    /**
     * Display projects list (API)
     * 
     * GET /natan/projects
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get user projects with documents count
            $projects = NatanProject::forUser($user)
                ->withCount('documents')
                ->orderBy('updated_at', 'desc')
                ->get();

            Log::info('[NatanProject] Projects index accessed', [
                'user_id' => $user->id,
                'projects_count' => $projects->count(),
            ]);

            return response()->json([
                'success' => true,
                'projects' => $projects,
            ]);
        } catch (\Exception $e) {
            Log::error('[NatanProject] Index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load projects',
            ], 500);
        }
    }

    /**
     * Store new project
     * 
     * POST /natan/projects
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'settings' => 'nullable|array',
            ]);

            // Map 'name' to 'collection_name' (database column)
            $createData = [
                'creator_id' => $user->id,
                'collection_name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? 'folder_open',
                'color' => $validated['color'] ?? '#1B365D',
                'settings' => $validated['settings'] ?? null,
                'context' => 'pa_project',
            ];

            // Create project
            $project = NatanProject::create($createData);

            Log::info('[NatanProject] Project created', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]);

            return response()->json([
                'success' => true,
                'project' => $project->load('documents'),
                'message' => 'Project created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanProject] Create error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
            ], 500);
        }
    }

    /**
     * Show project details
     * 
     * GET /natan/projects/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $project = NatanProject::forUser($user)
                ->with(['documents.chunks'])
                ->findOrFail($id);

            Log::info('[NatanProject] Project details accessed', [
                'user_id' => $user->id,
                'project_id' => $project->id,
            ]);

            return response()->json([
                'success' => true,
                'project' => $project,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('[NatanProject] Show error', [
                'user_id' => Auth::id(),
                'project_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project',
            ], 500);
        }
    }

    /**
     * Update project
     * 
     * PUT /natan/projects/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $project = NatanProject::forUser($user)->findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'settings' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);

            $project->update($validated);

            Log::info('[NatanProject] Project updated', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'updated_fields' => array_keys($validated),
            ]);

            return response()->json([
                'success' => true,
                'project' => $project->fresh()->load('documents'),
                'message' => 'Project updated successfully',
            ]);
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
            Log::error('[NatanProject] Update error', [
                'user_id' => Auth::id(),
                'project_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
            ], 500);
        }
    }

    /**
     * Delete project
     * 
     * DELETE /natan/projects/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $project = NatanProject::forUser($user)->findOrFail($id);
            
            $projectName = $project->name;

            // Delete project (will cascade delete documents and chunks via FK)
            $project->delete();

            Log::info('[NatanProject] Project deleted', [
                'user_id' => $user->id,
                'project_id' => $id,
                'project_name' => $projectName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('[NatanProject] Delete error', [
                'user_id' => Auth::id(),
                'project_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
            ], 500);
        }
    }
}
