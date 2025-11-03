<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * @package Database\Seeders
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Seeder per ruoli specifici NATAN_LOC (PA Entities)
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/seeders/RoleSeeder.php
 *
 * Crea ruoli specifici per PA entities (Pubblica Amministrazione):
 * - pa_entity_admin: Admin del tenant PA, può gestire utenti e configurazioni del proprio tenant
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ruolo specifico per PA Entity Admin
        // Questo ruolo permette di gestire utenti e configurazioni del proprio tenant PA
        Role::firstOrCreate(
            ['name' => 'pa_entity_admin', 'guard_name' => 'web'],
            ['name' => 'pa_entity_admin', 'guard_name' => 'web']
        );

        $this->command->info('✅ Ruolo pa_entity_admin creato/verificato');
    }
}
