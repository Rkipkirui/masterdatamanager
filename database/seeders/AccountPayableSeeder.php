<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountPayableSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['account_code' => '152101', 'account_name' => 'Debtors control Account - Local debtors'],
            ['account_code' => '152103', 'account_name' => 'Debtors control - Overseas Debtors'],
            ['account_code' => '152105', 'account_name' => 'Debtors Control - HP Debtors'],
            ['account_code' => '153101', 'account_name' => 'Intercompany debtors control Account'],
            ['account_code' => '154205', 'account_name' => 'Staff debtors Control'],
            ['account_code' => '154501', 'account_name' => 'Warranty Claim'],
            ['account_code' => '154503', 'account_name' => 'WIP Debtors Control Account'],
            ['account_code' => '161503', 'account_name' => 'Cash Sales Control Account'],
        ];

        foreach ($accounts as $account) {
            DB::table('account_payables')->updateOrInsert(
                ['account_code' => $account['account_code']],
                ['account_name' => $account['account_name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
