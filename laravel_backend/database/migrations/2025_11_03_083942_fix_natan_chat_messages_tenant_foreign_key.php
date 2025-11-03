<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Correggere foreign key natan_chat_messages_tenant_id_foreign che ancora referenzia pa_entities
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_03_083942_fix_natan_chat_messages_tenant_foreign_key.php
 * 
 * La foreign key natan_chat_messages_tenant_id_foreign è stata creata prima del rename
 * da pa_entities a tenants, quindi ancora referenzia pa_entities. Questa migrazione
 * la corregge per referenziare tenants.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se la tabella esiste
        if (!Schema::hasTable('natan_chat_messages')) {
            return;
        }

        // Verifica se la foreign key esiste e referenzia ancora pa_entities
        $foreignKeys = DB::select("
            SELECT 
                CONSTRAINT_NAME,
                TABLE_NAME,
                REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'natan_chat_messages'
                AND CONSTRAINT_NAME = 'natan_chat_messages_tenant_id_foreign'
        ");

        foreach ($foreignKeys as $fk) {
            // Se la foreign key referenzia ancora pa_entities, correggila
            if ($fk->REFERENCED_TABLE_NAME === 'pa_entities') {
                // Droppa la foreign key esistente
                DB::statement("ALTER TABLE `natan_chat_messages` DROP FOREIGN KEY `natan_chat_messages_tenant_id_foreign`");
                
                // Ricrea la foreign key che referenzia tenants
                Schema::table('natan_chat_messages', function (Blueprint $table) {
                    $table->foreign('tenant_id')
                        ->references('id')
                        ->on('tenants')
                        ->onDelete('cascade');
                });
                
                break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se la tabella esiste
        if (!Schema::hasTable('natan_chat_messages')) {
            return;
        }

        // Verifica se la foreign key esiste e referenzia tenants
        $foreignKeys = DB::select("
            SELECT 
                CONSTRAINT_NAME,
                TABLE_NAME,
                REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'natan_chat_messages'
                AND CONSTRAINT_NAME = 'natan_chat_messages_tenant_id_foreign'
        ");

        foreach ($foreignKeys as $fk) {
            // Se la foreign key referenzia tenants, ripristina pa_entities
            if ($fk->REFERENCED_TABLE_NAME === 'tenants') {
                // Droppa la foreign key esistente
                DB::statement("ALTER TABLE `natan_chat_messages` DROP FOREIGN KEY `natan_chat_messages_tenant_id_foreign`");
                
                // Se pa_entities esiste, ricrea la foreign key
                if (Schema::hasTable('pa_entities')) {
                    Schema::table('natan_chat_messages', function (Blueprint $table) {
                        $table->foreign('tenant_id')
                            ->references('id')
                            ->on('pa_entities')
                            ->onDelete('cascade');
                    });
                }
                
                break;
            }
        }
    }
};
