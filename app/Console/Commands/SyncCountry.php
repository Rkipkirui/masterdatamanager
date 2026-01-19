<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync-country';

    /**
     * The console command description.
     *
     * @var string
     */
   protected $description = 'Sync country from SAP to local database';

    protected $sapService;

    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    public function handle()
    {
        $this->info('Starting SAP country sync...');
        if ($this->sapService->syncCountry()) {
            $this->info('SAP country synced successfully.');
        } else {
            $this->error('Failed to sync SAP country. Check logs for details.');
        }
    }
}