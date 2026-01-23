<?php

namespace App\Console\Commands;

use App\Services\SapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\SapUser;

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

    try {
        $count = $this->sapService->syncUsers();

        $this->info("✅ Successfully synced {$count} SAP users");

    } catch (\Throwable $e) {

        $this->error('❌ Failed to sync SAP users.');
        Log::error('SAP sync failed: ' . $e->getMessage());
    }
}

}