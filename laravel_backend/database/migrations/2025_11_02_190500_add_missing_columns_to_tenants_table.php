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
 * @purpose Aggiungere colonne mancanti a tenants se non esistono
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_190500_add_missing_columns_to_tenants_table.php
 * 
 * Aggiunge tutte le colonne che potrebbero mancare dopo il rename da pa_entities.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aggiungi colonne una alla volta in ordine logico
        Schema::table('tenants', function (Blueprint $table) {
            // Base columns (dopo id)
            if (!Schema::hasColumn('tenants', 'name')) {
                $table->string('name')->after('id');
            }
            
            if (!Schema::hasColumn('tenants', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
            
            if (!Schema::hasColumn('tenants', 'code')) {
                $table->string('code')->unique()->nullable()->after('slug');
            }
            
            if (!Schema::hasColumn('tenants', 'entity_type')) {
                $table->enum('entity_type', ['pa', 'company', 'public_entity', 'other'])->default('pa')->after('code');
            }
            
            // Contact columns
            if (!Schema::hasColumn('tenants', 'email')) {
                $table->string('email')->nullable()->after('entity_type');
            }
            
            if (!Schema::hasColumn('tenants', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('tenants', 'vat_number')) {
                $table->string('vat_number')->nullable()->after('address');
            }
            
            // Settings column
            if (!Schema::hasColumn('tenants', 'settings')) {
                $table->json('settings')->nullable()->after('vat_number');
            }
            
            // Active status (dopo settings)
            if (!Schema::hasColumn('tenants', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('settings');
            }
            
            // Date columns
            if (!Schema::hasColumn('tenants', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('is_active');
            }
            
            if (!Schema::hasColumn('tenants', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }
            
            // Notes
            if (!Schema::hasColumn('tenants', 'notes')) {
                $table->text('notes')->nullable()->after('subscription_ends_at');
            }
            
            // Soft deletes (dopo timestamps se esistono)
            if (!Schema::hasColumn('tenants', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Aggiungi indici se mancano (in una seconda chiamata)
        Schema::table('tenants', function (Blueprint $table) {
            $connection = Schema::getConnection();
            $database = $connection->getDatabaseName();
            
            // Verifica e aggiungi indici
            $indexes = [
                'slug' => 'tenants_slug_unique',
                'code' => 'tenants_code_unique',
                'is_active' => 'tenants_is_active_index',
            ];
            
            foreach ($indexes as $column => $indexName) {
                if (Schema::hasColumn('tenants', $column)) {
                    $exists = DB::select(
                        "SELECT COUNT(*) as count 
                         FROM information_schema.statistics 
                         WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                        [$database, 'tenants', $indexName]
                    );
                    
                    if (!$exists || $exists[0]->count == 0) {
                        if ($column === 'slug' || $column === 'code') {
                            // Unique index già gestito da unique() sopra
                        } else {
                            $table->index($column);
                        }
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non rimuoviamo colonne per sicurezza - rollback manuale se necessario
    }
};
