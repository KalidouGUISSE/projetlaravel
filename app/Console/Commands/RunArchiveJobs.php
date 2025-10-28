<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunArchiveJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:run-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécuter manuellement les jobs d\'archivage et désarchivage des comptes bloqués';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Exécution du job d\'archivage des comptes bloqués...');
        \App\Jobs\ArchiveBlockedAccounts::dispatch();

        $this->info('Exécution du job de désarchivage des comptes bloqués...');
        \App\Jobs\UnarchiveBlockedAccounts::dispatch();

        $this->info('Jobs lancés avec succès. Vérifiez les logs pour les résultats.');
    }
}
