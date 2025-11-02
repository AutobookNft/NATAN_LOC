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
 * @purpose Aggiungere tenant_id alla tabella natan_chat_messages per isolamento multi-tenant
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_163858_add_tenant_id_to_natan_chat_messages_table.php
 * 
 * Aggiunge colonna tenant_id con foreign key su pa_entities per isolamento dati per tenant.
 * La tabella è condivisa con EGI, quindi la colonna è nullable per compatibilità iniziale.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            // Aggiungi tenant_id dopo id, nullable per compatibilità con dati esistenti
            $table->unsignedBigInteger('tenant_id')
                ->after('id')
                ->nullable()
                ->index();
            
            // Foreign key su pa_entities
            $table->foreign('tenant_id')
                ->references('id')
                ->on('pa_entities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('natan_chat_messages', function (Blueprint $table) {
            // Rimuovi foreign key e colonna
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
