<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-05
 * @purpose Creazione tabella user_activities (audit trail GDPR-compliant per azioni utente)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_activities')) {
            return; // Table already exists
        }
        
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            
            // User reference (tabella condivisa EGI)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            // Action details
            $table->string('action'); // Action name (e.g., 'scraper_executed', 'profile_updated')
            $table->string('category'); // Category from GdprActivityCategory enum value
            $table->json('context')->nullable(); // Sanitized context data
            $table->json('metadata')->nullable(); // Request metadata (IP, user agent, etc.)
            
            // Request metadata (also stored in metadata JSON, but indexed here for queries)
            $table->string('ip_address', 45)->nullable(); // Masked IP address
            $table->text('user_agent')->nullable(); // User agent string
            $table->string('session_id')->nullable(); // Session ID
            
            // GDPR compliance
            $table->enum('privacy_level', ['standard', 'high', 'critical'])->default('standard');
            $table->timestamp('expires_at')->nullable(); // Auto-delete after retention period
            
            // Timestamps
            $table->timestamps();
            
            // Indici
            $table->index('user_id');
            $table->index('category');
            $table->index('action');
            $table->index('created_at');
            $table->index('expires_at');
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'created_at']);
            $table->index(['privacy_level', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
