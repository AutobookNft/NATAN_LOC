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
        // Verifica se la colonna esiste già
        if (!Schema::hasColumn('natan_chat_messages', 'tenant_id')) {
            Schema::table('natan_chat_messages', function (Blueprint $table) {
                // Aggiungi tenant_id dopo id, nullable per compatibilità con dati esistenti
                $table->unsignedBigInteger('tenant_id')
                    ->after('id')
                    ->nullable()
                    ->index();
            });
        }
        
        // Verifica se la foreign key esiste già e se punta alla tabella corretta
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $foreignKeys = \DB::select("
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'natan_chat_messages'
                AND CONSTRAINT_NAME = 'natan_chat_messages_tenant_id_foreign'
        ", [$database]);
        
        // Se la foreign key esiste ma punta a pa_entities, droppala
        if (!empty($foreignKeys) && $foreignKeys[0]->REFERENCED_TABLE_NAME === 'pa_entities') {
            \DB::statement("ALTER TABLE `natan_chat_messages` DROP FOREIGN KEY `natan_chat_messages_tenant_id_foreign`");
            $foreignKeys = [];
        }
        
        if (empty($foreignKeys)) {
            // Foreign key: usa 'pa_entities' se esiste (avrà id bigint unsigned)
            // Se esiste solo 'tenants' con id string, usa quello temporaneamente
            // La migration rename_pa_entities_to_tenants_table aggiornerà poi la foreign key
            if (Schema::hasTable('pa_entities')) {
                $tenantTableName = 'pa_entities';
            } else if (Schema::hasTable('tenants')) {
                // Verifica se tenants.id è string o bigint
                $idType = \DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'id'", [$database]);
                if (!empty($idType) && $idType[0]->DATA_TYPE === 'varchar') {
                    // tenants ha id string, non possiamo usarlo - usa pa_entities se esiste
                    $tenantTableName = Schema::hasTable('pa_entities') ? 'pa_entities' : 'tenants';
                } else {
                    $tenantTableName = 'tenants';
                }
            } else {
                throw new \Exception('Neither tenants nor pa_entities table exists');
            }
            
            Schema::table('natan_chat_messages', function (Blueprint $table) use ($tenantTableName) {
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on($tenantTableName)
                    ->onDelete('cascade');
            });
        }
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
