<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncCustomerSeries extends Command
{
    protected $signature = 'sap:sync-customer-series';
    protected $description = 'Sync customer series from SAP to local database';

    protected $sapService;

    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    public function handle()
    {
        $this->info('Starting SAP customer series sync...');
        if ($this->sapService->syncCustomerSeries()) {
            $this->info('SAP customer series synced successfully.');
        } else {
            $this->error('Failed to sync SAP customer series. Check logs for details.');
        }
    }
}