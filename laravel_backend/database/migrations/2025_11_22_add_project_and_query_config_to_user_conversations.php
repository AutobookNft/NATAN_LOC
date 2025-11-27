<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.5) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Ultra Excellence Enterprise)
 * @date 2025-11-22
 * @purpose Add project_id and query_config to user_conversations (Multi-tenant query support)
 * 
 * CONTEXT: Implementing OS3.5 Multi-Tenant Chat Architecture
 * ADR: docs/adr/0001-multi-tenant-query-architecture.md
 * 
 * CHANGES:
 * - Add project_id FK to collections (NATAN Projects)
 * - Add query_config JSON for multi-tenant query configuration
 * - Add generated_apps JSON for AI-generated applications
 * - Add indexes for performance
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_conversations', function (Blueprint $table) {
            // Project context (NULL = chat sciolta, not null = chat con progetto)
            // NOTA: Usiamo unsignedBigInteger invece di foreignId perché collections è in EGI
            //       e non possiamo creare FK cross-app. L'integrità è gestita a livello applicativo.
            if (!Schema::hasColumn('user_conversations', 'project_id')) {
                $table->unsignedBigInteger('project_id')
                    ->nullable()
                    ->after('user_id')
                    ->comment('ID progetto NATAN (collections in EGI). NULL = general chat, NOT NULL = project chat');
            }
            
            // Query configuration for multi-tenant queries
            if (!Schema::hasColumn('user_conversations', 'query_config')) {
                $table->json('query_config')
                    ->nullable()
                    ->after('config')
                    ->comment('Multi-tenant query config: target_mode, tenant_ids, boost_config');
            }
            
            // Generated apps storage
            if (!Schema::hasColumn('user_conversations', 'generated_apps')) {
                $table->json('generated_apps')
                    ->nullable()
                    ->after('metadata')
                    ->comment('Array of AI-generated apps metadata (HTML/JS apps)');
            }
        });
        
        // Indexes in separate Schema::table call (Laravel 11 best practice)
        Schema::table('user_conversations', function (Blueprint $table) {
            // Check and create indexes only if they don't exist
            $indexesToCreate = [
                'idx_conversations_project' => ['project_id'],
                'idx_conversations_tenant_project' => ['tenant_id', 'project_id'],
                'idx_conversations_user_project_last' => ['user_id', 'project_id', 'last_message_at'],
            ];
            
            foreach ($indexesToCreate as $indexName => $columns) {
                $indexExists = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'user_conversations' 
                    AND INDEX_NAME = ?
                ", [$indexName]);
                
                if (empty($indexExists)) {
                    $table->index($columns, $indexName);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_conversations', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_conversations_project');
            $table->dropIndex('idx_conversations_tenant_project');
            $table->dropIndex('idx_conversations_user_project_last');
            
            // Drop columns (no FK to drop since project_id is not a foreign key)
            $table->dropColumn(['project_id', 'query_config', 'generated_apps']);
        });
    }
};

