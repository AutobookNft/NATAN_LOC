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
 * @purpose Creazione tabella pa_entities (tenants - PA & Enterprises)
 * 
 * Tabella per tenant multi-tenant. Supporta sia enti PA che aziende private.
 * Il campo entity_type distingue tra: 'pa', 'company', 'public_entity', 'other'
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pa_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome dell'entità (es: "Comune di Firenze" o "Acme Corporation S.r.l.")
            $table->string('slug')->unique(); // Slug per subdomain/route
            $table->string('code')->unique()->nullable(); // Codice identificativo univoco
            $table->enum('entity_type', ['pa', 'company', 'public_entity', 'other'])->default('pa');

            // Contatti e info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('vat_number')->nullable(); // P.IVA / CF

            // Configurazione tenant
            $table->json('settings')->nullable(); // Configurazioni specifiche tenant (JSON)
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // Metadata
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indici
            $table->index('slug');
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pa_entities');
    }
};
