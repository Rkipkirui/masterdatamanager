<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SapService;

class SyncPaymentTerms extends Command
{
    protected $signature = 'sap:sync-payment-terms';
    protected $description = 'Sync payment terms from SAP';

    public function handle(SapService $sapService)
    {
        $this->info('Syncing payment terms...');

        $count = $sapService->syncPaymentTerms();

        $this->info("✅ Synced {$count} payment terms.");
        return Command::SUCCESS;
    }
}
