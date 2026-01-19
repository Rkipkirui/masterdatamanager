<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCustomerGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can call it like: php artisan customer-groups:sync
     */
    protected $signature = 'customer-groups:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync customer and supplier groups from SAP OCRG into local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting customer group sync...');

        $databaseName = env('SAP_COMPANY_DB');

        // Fetch all groups from SAP
        $groups = DB::connection('sap_hana')->select("
            SELECT T0.\"GroupCode\", T0.\"GroupName\", T0.\"GroupType\"
            FROM \"{$databaseName}\".\"OCRG\" T0
        ");

        $count = 0;

        foreach ($groups as $group) {
            DB::table('customer_groups')->updateOrInsert(
                ['code' => $group->GroupCode],
                [
                    'name' => mb_convert_encoding($group->GroupName, 'UTF-8', 'UTF-8'),
                    'group_type' => $group->GroupType,
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->info("✅ Finished syncing {$count} customer groups.");
    }
}
