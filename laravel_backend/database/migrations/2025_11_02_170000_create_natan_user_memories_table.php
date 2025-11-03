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
 * @purpose Creare tabella natan_user_memories per sistema memoria utente con isolamento multi-tenant
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: database/migrations/2025_11_02_170000_create_natan_user_memories_table.php
 * 
 * Tabella per memorizzare ricordi personalizzati dell'utente, con tenant_id per isolamento dati.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Determina quale tabella usare per la foreign key (pa_entities se esiste, altrimenti tenants)
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $tenantTableName = Schema::hasTable('pa_entities') ? 'pa_entities' : 'tenants';
        
        // Se la tabella esiste già, verifica se ha tenant_id e aggiungilo se manca
        if (Schema::hasTable('natan_user_memories')) {
            Schema::table('natan_user_memories', function (Blueprint $table) use ($tenantTableName) {
                // Controlla se tenant_id esiste già
                if (!Schema::hasColumn('natan_user_memories', 'tenant_id')) {
                    // Aggiungi tenant_id dopo id
                    $table->unsignedBigInteger('tenant_id')
                        ->after('id')
                        ->nullable(false)
                        ->index();
                    
                    // Foreign key su pa_entities o tenants (sarà aggiornata dalla migration rename)
                    $table->foreign('tenant_id')
                        ->references('id')
                        ->on($tenantTableName)
                        ->onDelete('cascade');
                    
                    // Aggiungi indici compositi se non esistono
                    if (!$this->indexExists('natan_user_memories', 'natan_user_memories_tenant_id_user_id_is_active_index')) {
                        $table->index(['tenant_id', 'user_id', 'is_active']);
                    }
                    if (!$this->indexExists('natan_user_memories', 'natan_user_memories_tenant_id_memory_type_is_active_index')) {
                        $table->index(['tenant_id', 'memory_type', 'is_active']);
                    }
                }
            });
        } else {
            // Crea tabella se non esiste
            Schema::create('natan_user_memories', function (Blueprint $table) use ($tenantTableName) {
                $table->id();
                
                // Tenant isolation - required per isolamento dati
                $table->unsignedBigInteger('tenant_id')
                    ->nullable(false)
                    ->index();
                
                // Foreign key su pa_entities o tenants (sarà aggiornata dalla migration rename)
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on($tenantTableName)
                    ->onDelete('cascade');
                
                // User reference
                $table->unsignedBigInteger('user_id')
                    ->index();
                
                // Foreign key su users (tabella condivisa EGI)
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                
                // Content fields
                $table->text('memory_content');
                $table->string('memory_type', 50)
                    ->default('general')
                    ->index();
                $table->text('keywords')
                    ->nullable();
                
                // Usage tracking
                $table->unsignedInteger('usage_count')
                    ->default(0)
                    ->index();
                $table->timestamp('last_used_at')
                    ->nullable();
                
                // Status
                $table->boolean('is_active')
                    ->default(true)
                    ->index();
                
                $table->timestamps();
                
                // Composite index per query comuni
                $table->index(['tenant_id', 'user_id', 'is_active']);
                $table->index(['tenant_id', 'memory_type', 'is_active']);
            });
        }
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        try {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$database, $table, $indexName]
            );
            
            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('natan_user_memories');
    }
};
