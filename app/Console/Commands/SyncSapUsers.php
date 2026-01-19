<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;

class SyncSapUsers extends Command
{
    protected $signature = 'sap:sync-users';
    protected $description = 'Sync users from SAP to local database';

    protected $sapService;

    public function __construct(SapService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    public function handle()
    {
        $this->info('Starting SAP user sync...');
        if ($this->sapService->syncUsers()) {
            $this->info('SAP users synced successfully.');
        } else {
            $this->error('Failed to sync SAP users. Check logs for details.');
        }
    }
}