<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncDealerCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync-dealer-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync dealer categories from SAP to local database';

    protected $sapService;

    /**
     * Create a new command instance.
     */
    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting dealer categories sync...');

        $count = $this->sapService->syncDealerCategories();

        $this->info("Dealer categories synced successfully. Total: {$count}");
    }
}
