<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncPriceLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync-price-lists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync pricelist from SAP';

    /**
     * Execute the console command.
     */
    protected $sapService;

    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    public function handle()
    {
        $this->info('Starting SAP pricelist sync...');
        if ($this->sapService->syncPriceLists()) {
            $this->info('SAP pricelist synced successfully.');
        } else {
            $this->error('Failed to sync SAP pricelist. Check logs for details.');
        }
    }
}