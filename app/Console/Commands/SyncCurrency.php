<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncCurrency extends Command
{
    protected $signature = 'sap:sync-currency';
    protected $description = 'Sync currency from SAP to local database';

    protected $sapService;

    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    public function handle()
    {
        $this->info('Starting SAP cureency sync...');
        if ($this->sapService->syncCurrency()) {
            $this->info('SAP currency synced successfully.');
        } else {
            $this->error('Failed to sync SAP currency. Check logs for details.');
        }
    }
}