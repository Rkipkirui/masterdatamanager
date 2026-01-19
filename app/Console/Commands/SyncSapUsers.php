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
            $totalSynced = 0;
            $databaseName = env('SAP_COMPANY_DB');

            $query = "SELECT T0.\"USER_CODE\", T0.\"U_NAME\", T0.\"E_Mail\", T0.\"USERID\"
                      FROM \"{$databaseName}\".\"OUSR\" T0
                      ORDER BY T0.\"USERID\" DESC";

            $users = \DB::connection('sap_hana')->select($query);

            foreach ($users as $user) {
                Log::info("Saving user: {$user->USER_CODE} | Email: {$user->E_Mail}");

                SapUser::updateOrCreate(
                    ['sap_user_code' => $user->USER_CODE],
                    [
                        'sap_user_name' => $user->U_NAME ?? 'Unknown User',
                        'email'         => $user->E_Mail,
                        'user_code'     => $user->USER_CODE,
                        'is_active'     => 1,
                        'password'      => Hash::make('Trading@1'),
                    ]
                );

                $totalSynced++;
            }

            $this->info("✅ Successfully synced {$totalSynced} SAP users");
            Log::info("✅ Successfully synced {$totalSynced} SAP users");

        } catch (\Exception $ex) {
            $this->error('Failed to sync SAP users. Check logs for details.');
            Log::error('❌ Error syncing SAP users: ' . $ex->getMessage());
        }
    }
}