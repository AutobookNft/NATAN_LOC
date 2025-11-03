<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @package App\Console\Commands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Comando Artisan per eseguire migration condivise tramite orchestrator
 * 
 * CONTESTO: Database EGI condiviso tra FlorenceEGI e NATAN_LOC
 * PERCORSO FILE: app/Console/Commands/SharedMigrateCommand.php
 * 
 * Wrapper per migrate_shared.php orchestrator che previene operazioni distruttive accidentali.
 */
class SharedMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:shared {command : Comando migration (es: migrate, status, rollback)} 
                                        {--project=NATAN : Progetto (EGI o NATAN)}
                                        {--args=* : Argomenti aggiuntivi per il comando}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue migration usando orchestrator condiviso (previene operazioni distruttive accidentali)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = $this->argument('command');
        $project = $this->option('project') ?? 'NATAN';
        
        $orchestratorPath = '/home/fabio/migration_orchestrator/migrate_shared.php';
        
        if (!file_exists($orchestratorPath)) {
            $this->error("Orchestrator non trovato: $orchestratorPath");
            return 1;
        }
        
        $fullCommand = "php $orchestratorPath $project $command";
        
        $this->info("Eseguendo migration condivisa per progetto: $project");
        $this->line("");
        
        passthru($fullCommand, $returnCode);
        
        return $returnCode;
    }
}
