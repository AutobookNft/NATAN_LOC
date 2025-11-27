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
 * @purpose Refactor natan_chat_messages to use proper FK to user_conversations
 * 
 * CONTEXT: OS3.5 Master-Detail Architecture for Chat
 * ADR: docs/adr/0005-chat-master-detail-architecture.md
 * 
 * CHANGES:
 * - Add conversation_id FK to user_conversations
 * - Keep session_id for backward compatibility (but deprecated)
 * - Add generated_app_id FK to generated_apps
 * - Update indexes for performance
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            // Add conversation FK (Master-Detail relationship)
            if (!Schema::hasColumn('natan_chat_messages', 'conversation_id')) {
                $table->foreignId('conversation_id')
                    ->nullable()
                    ->after('project_id')
                    ->comment('FK to user_conversations (Master). NULL for legacy messages.');
            }
            
            // Add generated_app FK
            if (!Schema::hasColumn('natan_chat_messages', 'generated_app_id')) {
                $table->foreignId('generated_app_id')
                    ->nullable()
                    ->after('rag_method')
                    ->comment('FK to generated_apps if this message generated an app');
            }
        });
        
        // Add indexes in separate statement (Laravel 11 best practice)
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            $indexesToCreate = [
                'idx_chat_messages_conversation' => ['conversation_id'],
                'idx_chat_messages_generated_app' => ['generated_app_id'],
                'idx_chat_messages_conv_time' => ['conversation_id', 'created_at'],
                'idx_chat_messages_user_conv' => ['user_id', 'conversation_id'],
            ];
            
            foreach ($indexesToCreate as $indexName => $columns) {
                $indexExists = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'natan_chat_messages' 
                    AND INDEX_NAME = ?
                ", [$indexName]);
                
                if (empty($indexExists)) {
                    $table->index($columns, $indexName);
                }
            }
        });
        
        // Add foreign keys in separate statement
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            // Check if FK exists before creating
            $fks = [
                'fk_chat_messages_conversation' => ['conversation_id', 'user_conversations'],
                'fk_chat_messages_generated_app' => ['generated_app_id', 'generated_apps'],
            ];
            
            foreach ($fks as $fkName => [$column, $refTable]) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'natan_chat_messages' 
                    AND CONSTRAINT_NAME = ?
                ", [$fkName]);
                
                if (empty($fkExists)) {
                    if ($column === 'conversation_id') {
                        $table->foreign($column)
                            ->references('id')
                            ->on($refTable)
                            ->onDelete('cascade')
                            ->name($fkName);
                    } else {
                        $table->foreign($column)
                            ->references('id')
                            ->on($refTable)
                            ->onDelete('set null')
                            ->name($fkName);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign('fk_chat_messages_conversation');
            $table->dropForeign('fk_chat_messages_generated_app');
            
            // Drop indexes
            $table->dropIndex('idx_chat_messages_conversation');
            $table->dropIndex('idx_chat_messages_generated_app');
            $table->dropIndex('idx_chat_messages_conv_time');
            $table->dropIndex('idx_chat_messages_user_conv');
            
            // Drop columns
            $table->dropColumn(['conversation_id', 'generated_app_id']);
        });
    }
};

