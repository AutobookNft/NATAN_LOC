<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Creazione tabella user_conversations (sessioni conversazione NATAN multi-tenant)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_conversations', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('tenant_id')
                ->constrained('pa_entities')
                ->onDelete('cascade');
            
            // Utente e sessione
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->string('conversation_id')->unique(); // UUID sessione
            $table->string('title')->nullable(); // Titolo conversazione (generato o manuale)
            
            // Tipo conversazione
            $table->enum('type', ['natan_chat', 'use_query', 'rag_search', 'other'])->default('natan_chat');
            
            // Persona/Configurazione
            $table->string('persona')->nullable(); // Persona NATAN usata (strategic, financial, etc.)
            $table->json('config')->nullable(); // Configurazione conversazione (JSON)
            
            // Statistiche
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('total_tokens_used')->default(0);
            $table->decimal('total_cost_eur', 10, 4)->default(0); // Costo totale conversazione in EUR
            $table->unsignedInteger('total_latency_ms')->default(0); // Latency totale in millisecondi
            
            // Metadata
            $table->json('metadata')->nullable(); // Metadata flessibile
            
            // Timestamps
            $table->timestamp('last_message_at')->nullable(); // Timestamp ultimo messaggio
            $table->timestamps();
            $table->softDeletes();
            
            // Indici
            $table->index('tenant_id');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'type']);
            $table->index('conversation_id');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_conversations');
    }
};
