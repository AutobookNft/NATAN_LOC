<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Rinominare tabella pa_entities in tenants per supporto PA e Aziende
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_183354_rename_pa_entities_to_tenants_table.php
 * 
 * La tabella pa_entities viene rinominata in tenants perché NATAN_LOC
 * supporta sia Pubblica Amministrazione che Aziende Private.
 * Il nome "tenants" è più generico e corretto per un sistema multi-tenant.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Se tenants esiste già, salta il rename e aggiorna solo le foreign key
        if (Schema::hasTable('tenants') && !Schema::hasTable('pa_entities')) {
            // Tabella già rinominata, aggiorna solo foreign key
            $this->updateForeignKeyReferences();
            return;
        }
        
        // Se pa_entities esiste e tenants non esiste, fai il rename
        if (Schema::hasTable('pa_entities') && !Schema::hasTable('tenants')) {
            // 1. Rinomina la tabella
            Schema::rename('pa_entities', 'tenants');
            
            // 2. Aggiorna tutte le foreign key che referenziano pa_entities
            $this->updateForeignKeyReferences();
        } else if (Schema::hasTable('tenants')) {
            // Entrambe esistono o solo tenants - aggiorna solo foreign key
            $this->updateForeignKeyReferences();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Aggiorna le foreign key per referenziare pa_entities
        $this->revertForeignKeyReferences();
        
        // 2. Rinomina la tabella
        Schema::rename('tenants', 'pa_entities');
    }

    /**
     * Aggiorna tutte le foreign key che referenziano pa_entities
     */
    private function updateForeignKeyReferences(): void
    {
        // Questa funzione è commentata per ora - le foreign key sono già corrette
        // dopo che la tabella è stata rinominata. Le foreign key vengono create
        // dalle migration specifiche di ogni tabella e referenziano già tenants.
        
        // Se necessario aggiornare foreign key esistenti, verificare prima quali esistono
        // e aggiornarle solo se puntano ancora a pa_entities (non dovrebbe essere necessario)
        return;
    }

    /**
     * Ripristina le foreign key per referenziare pa_entities
     */
    private function revertForeignKeyReferences(): void
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // Lista delle tabelle con foreign key su tenants
        $tablesWithForeignKeys = [
            'users' => 'users_tenant_id_foreign',
            'pa_acts' => 'pa_acts_tenant_id_foreign',
            'user_conversations' => 'user_conversations_tenant_id_foreign',
            'natan_chat_messages' => 'natan_chat_messages_tenant_id_foreign',
            'natan_user_memories' => 'natan_user_memories_tenant_id_foreign',
        ];
        
        foreach ($tablesWithForeignKeys as $table => $foreignKeyName) {
            // Verifica se la foreign key su tenants esiste
            $newForeignKeyName = str_replace('pa_entities', 'tenants', $foreignKeyName);
            
            $exists = DB::select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE CONSTRAINT_SCHEMA = ? 
                   AND TABLE_NAME = ? 
                   AND CONSTRAINT_NAME = ?",
                [$database, $table, $newForeignKeyName]
            );
            
            if ($exists && $exists[0]->count > 0) {
                // Elimina la foreign key su tenants
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$newForeignKeyName}`");
                
                // Ricrea la foreign key che referenzia pa_entities
                DB::statement(
                    "ALTER TABLE `{$table}` 
                     ADD CONSTRAINT `{$foreignKeyName}` 
                     FOREIGN KEY (`tenant_id`) 
                     REFERENCES `pa_entities` (`id`) 
                     ON DELETE CASCADE"
                );
            }
        }
    }
};