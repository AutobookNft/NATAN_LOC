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
 * @purpose Creazione tabella pa_acts (documenti PA multi-tenant)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pa_acts', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('tenant_id')
                ->constrained('pa_entities')
                ->onDelete('cascade');
            
            // Identificazione documento
            $table->string('document_id')->unique(); // UUID universale
            $table->string('protocol_number')->nullable(); // Numero di protocollo (es: "DET-2024/123")
            $table->date('protocol_date')->nullable(); // Data protocollo
            
            // Metadati documento
            $table->string('title')->nullable(); // Titolo documento
            $table->text('description')->nullable(); // Descrizione
            $table->enum('document_type', ['pa_act', 'contract', 'report', 'image_scan', 'other'])->default('pa_act');
            
            // Info PA (se documento PA)
            $table->string('issuer')->nullable(); // Ente emittente (es: "Comune di Firenze")
            $table->string('department')->nullable(); // Ufficio/Dipartimento
            $table->string('responsible')->nullable(); // Responsabile
            $table->string('act_category')->nullable(); // Categoria (es: "urbanistica", "finanziaria")
            
            // File e hash
            $table->string('file_path')->nullable(); // Percorso file salvato
            $table->string('file_hash')->nullable(); // Hash SHA256 del file
            $table->string('file_mime')->nullable(); // MIME type
            $table->unsignedBigInteger('file_size_bytes')->nullable(); // Dimensione in byte
            $table->string('original_filename')->nullable(); // Nome file originale
            
            // Blockchain (notarizzazione)
            $table->boolean('blockchain_anchored')->default(false);
            $table->string('blockchain_txid')->nullable(); // Transaction ID su Algorand
            $table->string('blockchain_hash')->nullable(); // Hash ancorato su blockchain
            $table->string('blockchain_network')->nullable(); // Network (mainnet/testnet)
            $table->timestamp('blockchain_anchored_at')->nullable();
            
            // Metadata JSON (flessibile per tipi documento diversi)
            $table->json('metadata')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'processed', 'archived', 'deleted'])->default('draft');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indici
            $table->index('tenant_id');
            $table->index(['tenant_id', 'document_type']);
            $table->index(['tenant_id', 'protocol_date']);
            $table->index(['tenant_id', 'status']);
            $table->index('file_hash');
            $table->index('blockchain_txid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pa_acts');
    }
};
