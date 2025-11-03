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
 * @purpose Fix colonna id in tenants per assicurarsi che sia AUTO_INCREMENT
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_190600_fix_tenants_id_column.php
 * 
 * Assicura che la colonna id sia PRIMARY KEY AUTO_INCREMENT.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // Verifica se id è AUTO_INCREMENT
        $result = DB::select(
            "SELECT EXTRA, COLUMN_DEFAULT FROM information_schema.COLUMNS 
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id'",
            [$database, 'tenants']
        );
        
        if (!empty($result)) {
            $extra = $result[0]->EXTRA ?? '';
            $isAutoIncrement = strpos($extra, 'auto_increment') !== false;
            
            if (!$isAutoIncrement) {
                // La colonna esiste ma non è AUTO_INCREMENT
                // Devo rimuovere temporaneamente le foreign key, modificare id, e ricrearle
                
                // 1. Trova tutte le foreign key che referenziano tenants
                $foreignKeys = DB::select(
                    "SELECT TABLE_NAME, CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE CONSTRAINT_SCHEMA = ? 
                       AND REFERENCED_TABLE_NAME = 'tenants' 
                       AND REFERENCED_COLUMN_NAME = 'id'",
                    [$database]
                );
                
                // 2. Rimuovi temporaneamente le foreign key
                foreach ($foreignKeys as $fk) {
                    try {
                        DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    } catch (\Exception $e) {
                        // Ignora se la foreign key non esiste
                    }
                }
                
                // 3. Modifica la colonna id per essere AUTO_INCREMENT
                try {
                    DB::statement("ALTER TABLE `tenants` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                } catch (\Exception $e) {
                    \Log::error("Failed to modify tenants.id: " . $e->getMessage());
                    throw $e;
                }
                
                // 4. Ricrea le foreign key
                foreach ($foreignKeys as $fk) {
                    try {
                        DB::statement(
                            "ALTER TABLE `{$fk->TABLE_NAME}` 
                             ADD CONSTRAINT `{$fk->CONSTRAINT_NAME}` 
                             FOREIGN KEY (`tenant_id`) 
                             REFERENCES `tenants` (`id`) 
                             ON DELETE CASCADE"
                        );
                    } catch (\Exception $e) {
                        \Log::warning("Failed to recreate foreign key {$fk->CONSTRAINT_NAME}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non revertiamo - la colonna id deve sempre essere AUTO_INCREMENT
    }
};
