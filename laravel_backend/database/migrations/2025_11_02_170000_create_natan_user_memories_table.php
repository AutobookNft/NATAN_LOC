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
 * @purpose Creare tabella natan_user_memories per sistema memoria utente con isolamento multi-tenant
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_170000_create_natan_user_memories_table.php
 * 
 * Tabella per memorizzare ricordi personalizzati dell'utente, con tenant_id per isolamento dati.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('natan_user_memories', function (Blueprint $table) {
            $table->id();
            
            // Tenant isolation - required per isolamento dati
            $table->unsignedBigInteger('tenant_id')
                ->nullable(false)
                ->index();
            
            // Foreign key su pa_entities
            $table->foreign('tenant_id')
                ->references('id')
                ->on('pa_entities')
                ->onDelete('cascade');
            
            // User reference
            $table->unsignedBigInteger('user_id')
                ->index();
            
            // Foreign key su users (tabella condivisa EGI)
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            // Content fields
            $table->text('memory_content');
            $table->string('memory_type', 50)
                ->default('general')
                ->index();
            $table->text('keywords')
                ->nullable();
            
            // Usage tracking
            $table->unsignedInteger('usage_count')
                ->default(0)
                ->index();
            $table->timestamp('last_used_at')
                ->nullable();
            
            // Status
            $table->boolean('is_active')
                ->default(true)
                ->index();
            
            $table->timestamps();
            
            // Composite index per query comuni
            $table->index(['tenant_id', 'user_id', 'is_active']);
            $table->index(['tenant_id', 'memory_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('natan_user_memories');
    }
};
