<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Creare tabella natan_chat_messages se non esiste (compatibilità con EGI database condiviso)
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_163857_create_natan_chat_messages_table_if_not_exists.php
 * 
 * Crea la tabella natan_chat_messages se non esiste già nel database EGI condiviso.
 * Questa tabella è usata sia da EGI che da NATAN_LOC per memorizzare i messaggi della chat.
 * La colonna tenant_id verrà aggiunta successivamente dalla migrazione add_tenant_id_to_natan_chat_messages_table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crea la tabella solo se non esiste già
        if (!Schema::hasTable('natan_chat_messages')) {
            Schema::create('natan_chat_messages', function (Blueprint $table) {
                $table->id();
                
                // tenant_id verrà aggiunto dalla migrazione successiva (add_tenant_id_to_natan_chat_messages_table)
                // Non lo aggiungiamo qui per compatibilità con schema esistente EGI
                
                // User and session
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('session_id')->index();
                
                // Message content
                $table->enum('role', ['user', 'assistant', 'system'])->default('user');
                $table->text('content');
                $table->unsignedBigInteger('reference_message_id')->nullable();
                
                // Persona information
                $table->string('persona_id')->nullable();
                $table->string('persona_name')->nullable();
                $table->decimal('persona_confidence', 5, 4)->nullable();
                $table->string('persona_selection_method')->nullable();
                $table->text('persona_reasoning')->nullable();
                $table->json('persona_alternatives')->nullable();
                
                // RAG information
                $table->json('rag_sources')->nullable();
                $table->unsignedInteger('rag_acts_count')->nullable();
                $table->string('rag_method')->nullable();
                
                // Web search information
                $table->boolean('web_search_enabled')->default(false);
                $table->string('web_search_provider')->nullable();
                $table->json('web_search_results')->nullable();
                $table->unsignedInteger('web_search_count')->nullable();
                $table->boolean('web_search_from_cache')->default(false);
                
                // AI model information
                $table->string('ai_model')->nullable();
                $table->unsignedInteger('tokens_input')->nullable();
                $table->unsignedInteger('tokens_output')->nullable();
                $table->unsignedInteger('response_time_ms')->nullable();
                
                // User feedback
                $table->boolean('was_helpful')->nullable();
                $table->text('user_feedback')->nullable();
                
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['session_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['role', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non droppiamo la tabella perché potrebbe essere condivisa con EGI
        // Solo se siamo sicuri che non è usata da EGI, possiamo dropparla
        // Schema::dropIfExists('natan_chat_messages');
    }
};
