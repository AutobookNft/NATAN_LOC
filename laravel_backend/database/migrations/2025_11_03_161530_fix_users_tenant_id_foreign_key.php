<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @package Database\Migrations
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Correggere foreign key users_tenant_id_foreign che ancora referenzia pa_entities
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_03_161530_fix_users_tenant_id_foreign_key.php
 *
 * PROBLEMA:
 * La foreign key users_tenant_id_foreign è stata creata prima del rename da pa_entities a tenants,
 * quindi ancora referenzia la tabella pa_entities invece di tenants.
 *
 * SOLUZIONE:
 * 1. Verifica se esiste la foreign key
 * 2. Se esiste e referenzia pa_entities, la elimina
 * 3. Ricrea la foreign key che referenzia tenants
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se la tabella tenants esiste (dopo il rename)
        $tenantsTableExists = Schema::hasTable('tenants');
        
        // Verifica se esiste la foreign key
        $constraintExists = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'users' 
             AND COLUMN_NAME = 'tenant_id'
             AND CONSTRAINT_NAME = 'users_tenant_id_foreign'"
        );

        if (!empty($constraintExists)) {
            // La foreign key esiste, verifica a cosa referenzia
            $refTable = DB::selectOne(
                "SELECT REFERENCED_TABLE_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'users' 
                 AND COLUMN_NAME = 'tenant_id'
                 AND CONSTRAINT_NAME = 'users_tenant_id_foreign'"
            );

            // Se referenzia pa_entities invece di tenants, correggi
            if ($refTable && $refTable->REFERENCED_TABLE_NAME === 'pa_entities') {
                // Elimina la foreign key errata
                DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `users_tenant_id_foreign`");

                // Ricrea la foreign key corretta su tenants (se esiste)
                if ($tenantsTableExists) {
                    DB::statement(
                        "ALTER TABLE `users` 
                         ADD CONSTRAINT `users_tenant_id_foreign` 
                         FOREIGN KEY (`tenant_id`) 
                         REFERENCES `tenants` (`id`) 
                         ON DELETE CASCADE"
                    );
                }
            }
        } else {
            // La foreign key non esiste, creala se tenants esiste
            if ($tenantsTableExists && Schema::hasColumn('users', 'tenant_id')) {
                DB::statement(
                    "ALTER TABLE `users` 
                     ADD CONSTRAINT `users_tenant_id_foreign` 
                     FOREIGN KEY (`tenant_id`) 
                     REFERENCES `tenants` (`id`) 
                     ON DELETE CASCADE"
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se esiste la foreign key
        $constraintExists = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'users' 
             AND COLUMN_NAME = 'tenant_id'
             AND CONSTRAINT_NAME = 'users_tenant_id_foreign'"
        );

        if (!empty($constraintExists)) {
            // Elimina la foreign key
            DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `users_tenant_id_foreign`");

            // Se pa_entities esiste, ricrea la foreign key su pa_entities
            if (Schema::hasTable('pa_entities')) {
                DB::statement(
                    "ALTER TABLE `users` 
                     ADD CONSTRAINT `users_tenant_id_foreign` 
                     FOREIGN KEY (`tenant_id`) 
                     REFERENCES `pa_entities` (`id`) 
                     ON DELETE CASCADE"
                );
            }
        }
    }
};

