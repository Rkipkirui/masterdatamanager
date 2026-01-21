<?php

namespace App\Services;

use App\Models\SapUser;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Illuminate\Support\Arr;

class SapService
{
    private $response = null;
    private $sessionId;

    public function login()
    {
        $sapServiceLayer = config('sap.base_url');
        if (!$sapServiceLayer) {
            throw new \Exception('SAP_SERVICE_LAYER not set in .env or config');
        }

        $response = Http::withoutVerifying()->post($sapServiceLayer . '/Login', [
            'UserName'    => config('sap.username'),
            'Password'    => config('sap.password'),
            'CompanyDB'   => config('sap.company_db'),
        ]);

        if ($response->failed()) {
            Log::error('SAP Login Failed: ' . $response->body());
            throw new \Exception('SAP Login Failed: ' . $response->body());
        }

        $this->sessionId = $response->json()['SessionId'] ?? null;
        return $this->sessionId;
    }

    protected function call($method, $endpoint, $body = [])
    {
        if (!$this->sessionId) {
            $this->login();
        }

        $headers = [
            'Cookie' => "B1SESSION=$this->sessionId",
            'Content-Type' => 'application/json',
        ];

        $url = rtrim(config('sap.base_url'), '/') . '/' . ltrim($endpoint, '/');

        $response = Http::timeout(2400) // ← Add this line
            ->withoutVerifying()
            ->withHeaders($headers)
            ->send(strtoupper($method), $url, ['json' => $body]);

        if ($response->failed()) {
            Log::error("SAP API call to [$endpoint] failed: " . $response->body());
            throw new \Exception("SAP Service Layer call to [$endpoint] failed: " . $response->body());
        }

        return $response->json();
    }

    public function syncUsers()
    {
        try {
            $totalSynced = 0;

            // 🔁 Replace with your actual CompanyDB/schema name
            //$databaseName = 'TEST_CGTL_JUL2024';SAP_COMPANY_DB
            $databaseName = env("SAP_COMPANY_DB");

            $query = "SELECT T0.\"USER_CODE\", T0.\"U_NAME\", T0.\"E_Mail\", T0.\"USERID\"
                      FROM \"{$databaseName}\".\"OUSR\" T0
                      ORDER BY T0.\"USERID\" DESC";

            $users = DB::connection('sap_hana')->select($query);

            foreach ($users as $user) {
                Log::info("Saving user: " . $user->USER_CODE);
                Log::info("Saving email: " . $user->E_Mail);
                //Log::info("Saving password: " . $user->password);
                //dd($user);
                SapUser::updateOrCreate(
                    ['sap_user_code' => $user->USER_CODE],
                    [
                        'sap_user_name' => $user->U_NAME ?? 'Unknown User',
                        // if (empty($user->U_NAME)) {
                        //         continue; // skip users without name
                        //     }
                        'email'         => $user->E_Mail,
                        'user_code'     => $user->USER_CODE,
                        'is_active'     => 1,
                        'password'      => Hash::make('Trading@1'),
                    ]
                );

                $totalSynced++;
            }

            Log::info("✅ Successfully synced {$totalSynced} SAP users");
            return true;
        } catch (\Exception $ex) {
            Log::error('❌ Error syncing SAP users: ' . $ex->getMessage());
            return false;
        }
    }
    public function getUsers($onlyApprovers = false)
    {
        try {
            $query = SapUser::query();
            if ($onlyApprovers) {
                $query->where('is_active', true);
            }

            return $query->get()->map(function ($user) {
                return [
                    'sap_user_code' => $user->sap_user_code,
                    'sap_user_name' => $user->sap_user_name,
                    'email'         => $user->email,
                    'user_code'     => $user->user_code,
                    'InternalKey'   => $user->sap_user_code,
                    'UserName'      => $user->sap_user_name,
                    'eMail'         => $user->email,
                    'UserCode'      => $user->user_code,
                ];
            })->toArray();
        } catch (\Exception $ex) {
            Log::error('Error fetching users from database: ' . $ex->getMessage());
            return [];
        }
    }

    public function createItem(array $itemData)
    {
        if (!$this->sessionId) {
            $this->login();
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Cookie' => "B1SESSION=$this->sessionId",
                'Content-Type' => 'application/json',
            ])
            ->post(config('sap.base_url') . '/Items', $itemData);

        if ($response->failed()) {
            throw new \Exception('SAP Item Creation Failed: ' . $response->body());
        }

        return $response->json();
    }

    public function getItemGroupCodes()
    {
        try {
            if (!$this->sessionId) {
                $this->login();
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Cookie' => "B1SESSION=$this->sessionId",
                    'Content-Type' => 'application/json',
                ])
                ->get(config('sap.base_url') . '/ItemGroups');

            if ($response->successful()) {
                $rawGroups = $response->json()['value'];
                $groupCodes = [];

                foreach ($rawGroups as $group) {
                    $groupCodes[] = [
                        'code' => (string) $group['GroupCode'],
                        'name' => $group['GroupName'],
                    ];
                }

                return $groupCodes;
            } else {
                throw new \Exception('Failed to fetch item group codes: ' . $response->body());
            }
        } catch (\Exception $ex) {
            Log::error('Error fetching item group codes: ' . $ex->getMessage());
            return [];
        }
    }

    public function uploadAttachment($data)
    {
        if (!$this->sessionId) {
            $this->login();
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Cookie' => "B1SESSION=$this->sessionId",
                'Content-Type' => 'application/json',
            ])
            ->post(config('sap.base_url') . '/Attachments2', $data);

        if ($response->failed()) {
            throw new \Exception('SAP Attachment Upload Failed: ' . $response->body());
        }

        return $response->json();
    }

    public function uploadAttachmentToSap($filePath)
    {
        if (!Storage::exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        $fileContents = Storage::get($filePath);
        $fileName = basename($filePath);
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $sourcePath = dirname($filePath);

        $attachmentData = [
            'Attachments2_Lines' => [
                [
                    'SourcePath'    => $sourcePath,
                    'FileName'      => $fileName,
                    'FileExtension' => $fileExtension,
                ],
            ],
        ];

        if (!$this->sessionId) {
            $this->login();
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Cookie' => "B1SESSION=$this->sessionId",
                'Content-Type' => 'application/json',
            ])
            ->post(config('sap.base_url') . '/Attachments2', $attachmentData);

        if ($response->failed()) {
            throw new \Exception('SAP Attachment Upload Failed: ' . $response->body());
        }

        $attachment = $response->json();
        return $attachment['Attachments2_Lines'][0]['AbsoluteEntry'] ?? null;
    }

    public function syncCustomerSeries()
    {
        $databaseName = env("SAP_COMPANY_DB");
        // 1. Get all customer series starting with C-
        $seriesData = DB::connection('sap_hana')->select("
        SELECT T0.\"Series\", T0.\"SeriesName\", T0.\"NextNumber\"
        FROM \"{$databaseName}\".\"NNM1\" T0
        WHERE T0.\"ObjectCode\" = '2'
          AND T0.\"SeriesName\" LIKE 'C-%'
    ");

        foreach ($seriesData as $series) {
            DB::table('customer_series')->updateOrInsert(
                ['series' => $series->Series],
                [
                    'series_name' => $series->SeriesName,
                    'next_number' => $series->NextNumber,
                    'updated_at' => now(),
                ]
            );
        }

        return count($seriesData);
    }

    public function syncCustomerGroup()
    {
        $databaseName = env("SAP_COMPANY_DB");
        // 1. Get all customer series starting with C-
        $groupData = DB::connection('sap_hana')->select("
        SELECT T0.\"GroupCode\", T0.\"GroupName\", T0.\"GroupType\"
        FROM \"{$databaseName}\".\"OCRG\" T0
        ");

        foreach ($groupData as $group) {
            DB::table('customer_groups')->updateOrInsert(
                ['code' => $group->GroupCode],  // match by SAP GroupCode
                [
                    'name'       => mb_convert_encoding($group->GroupName, 'UTF-8', 'UTF-8'),
                    'group_type' => $group->GroupType, // 'C' = Customer, 'S' = Supplier
                    'updated_at' => now(),
                ]
            );
        }

        return count($groupData);
    }

    public function syncCurrency()
    {
        $databaseName = env("SAP_COMPANY_DB");
        // 1. Get all customer series starting with C-
        $currencyData = DB::connection('sap_hana')->select("
        SELECT T0.\"CurrCode\", T0.\"CurrName\"
        FROM \"{$databaseName}\".\"OCRN\" T0
        ");

        foreach ($currencyData as $currency) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $currency->CurrCode],  // match by SAP GroupCode
                [
                    'name'       => $currency->CurrName,
                    'updated_at' => now(),
                ]
            );
        }

        return count($currencyData);
    }

    public function syncCountry()
    {
        $databaseName = env("SAP_COMPANY_DB");
        // 1. Get all customer series starting with C-
        $countryData = DB::connection('sap_hana')->select("
        SELECT T0.\"Code\", T0.\"Name\"
        FROM \"{$databaseName}\".\"OCRY\" T0
        ");

        foreach ($countryData as $country) {
            DB::table('countries')->updateOrInsert(
                ['code' => $country->Code],  // match by SAP GroupCode
                [
                    'name'       => mb_convert_encoding($country->Name, 'UTF-8', 'UTF-8'),
                    'updated_at' => now(),
                ]
            );
        }

        return count($countryData);
    }

    public function syncPaymentTerms()
    {
        $databaseName = env('SAP_COMPANY_DB');

        $terms = DB::connection('sap_hana')->select("
        SELECT 
            T0.\"GroupNum\"   AS \"sap_group_num\",
            T0.\"PymntGroup\" AS \"name\"
        FROM \"{$databaseName}\".\"OCTG\" T0
    ");

        foreach ($terms as $term) {
            DB::table('payment_terms')->updateOrInsert(
                ['sap_group_num' => $term->sap_group_num],
                [
                    'name'       => $term->name,
                    'updated_at' => now(),
                ]
            );
        }

        return count($terms);
    }

    public function syncPriceLists()
    {
        $databaseName = env('SAP_COMPANY_DB');

        $lists = DB::connection('sap_hana')->select("
        SELECT T0.\"ListNum\", T0.\"ListName\"
        FROM \"{$databaseName}\".\"OPLN\" T0
    ");

        foreach ($lists as $list) {
            // Convert stdClass to array to avoid case issues
            $arr = (array) $list;

            DB::table('price_lists')->updateOrInsert(
                ['code' => $arr['ListNum'] ?? $arr['LISTNUM']],   // whatever key exists
                [
                    'name'       => $arr['ListName'] ?? $arr['LISTNAME'], // whatever key exists
                    'updated_at' => now(),
                ]
            );
        }

        return count($lists);
    }

    public function syncDealerCategories()
    {
        $databaseName = env('SAP_COMPANY_DB');

        $categories = DB::connection('sap_hana')->select("
        SELECT 
            T0.\"IndCode\",
            T0.\"IndName\"
        FROM \"{$databaseName}\".\"OOND\" T0
    ");

        foreach ($categories as $cat) {
            DB::table('dealer_categories')->updateOrInsert(
                ['code' => $cat->IndCode],       // <-- use exact property
                [
                    'name'       => $cat->IndName, // <-- use exact property
                    'updated_at' => now(),
                ]
            );
        }

        return count($categories);
    }

    public function buildCustomerPayload(Customer $customer, string $cardCode): array
    {
        $payload = [
            "CardCode" => $cardCode,                     // ← Use the generated one
            "CardName" => $customer->name,
            "CardType" => "C",
            "Series"   => $customer->customerSeries?->series ?? 1,  // SAP numeric series ID

            "Currency" => $customer->currency?->code ?? "KES",

            "FederalTaxID" => $customer->pin,

            "Phone1" => $customer->tel1,

            "Phone2" => $customer->tel2,

            "Cellular" => $customer->mobile,

            "EmailAddress" => $customer->email,

            "ContactEmployees" => [
                [
                    "Name" => $customer->contact_id,
                ]
            ],

            // ✅ Addresses MUST be here
            "BPAddresses" => [
                [

                    "AddressName" => $customer->address_id ?? "Bill to",

                    "Street" => $customer->po_box,

                    "City" => $customer->city,

                    "Country" => $customer->country->code ?? "KE",

                    "AddressType" => "bo_BillTo",
                ]
            ],


            // Payment Terms (SAP field is GroupNum)
            "PayTermsGrpCode" => $customer->paymentTerm?->sap_group_num ?? 1,

            // Price List
            "PriceListNum"  => $customer->priceList?->code,

            // Industry (Dealer Category)
            "Industry" => $customer->dealerCategory?->code ?? '-1',

            // Territory
            "Territory" => $customer->territory?->code,

            "DebitorAccount" => $customer->accountPayable?->account_code,

            "SubjectToWithholdingTax" => "N",

            // UDF for Dealer Type
            "U_DealearType" => $customer->dealerType?->code,

            "U_DealerDiscount" => $customer->dealer_discount,

            // Properties (mapped to Properties1–29 as tYES/tNO)
            // Example: if customer has property_no = 5, set Properties5 = tYES
            // Add more logic here if you have multiple properties
        ];

        //dd($customer->property_no, $customer->property);

        // Map selected property to SAP Properties1…Properties29 (example)
        // --- SAP Properties mapping (1–64) ---
        $selected = (int) $customer->property?->code;

        for ($i = 1; $i <= 64; $i++) {
            $payload["Properties{$i}"] = ($i === $selected) ? 'tYES' : 'tNO';
        }
        // for ($i = 1; $i <= 29; $i++) {
        //     $payload["Properties{$i}"] = ($customer->property_no == $i) ? 'tYES' : 'tNO';
        // }
         //dd($payload);

        return $payload;
    }

    public function sendCustomerToSap(Customer $customer)
    {
        if (!$this->sessionId) {
            $this->login();
        }

        // Get series
        $series = $customer->customerSeries;
        if (!$series) {
            throw new \Exception('No series assigned to customer. Cannot generate CardCode.');
        }

        // Generate CardCode using series
        $nextNumber = str_pad($series->next_number, 5, '0', STR_PAD_LEFT);
        $cardCode = $series->series_name . $nextNumber;  // e.g. "C-KIT-T0003"

        // Optional: Check if CardCode already exists in SAP
        // try {
        //     $this->call('GET', "BusinessPartners('{$cardCode}')");
        //     throw new \Exception("CardCode '{$cardCode}' already exists in SAP");
        // } catch (\Exception $e) {
        //     if (!str_contains($e->getMessage(), '404')) {
        //         throw $e; // real error
        //     }
        //     // 404 = does not exist → good
        // }

        $payload = $this->buildCustomerPayload($customer, $cardCode);

        Log::info('Posting customer to SAP', [
            'card_code' => $cardCode,
            'payload'   => $payload,
        ]);

        $response = $this->call('POST', 'BusinessPartners', $payload);

        // SUCCESS: Increment series next_number
        $series->increment('next_number');
        $series->save();

        Log::info('SAP customer created successfully', [
            'card_code' => $cardCode,
            'sap_id'    => $response['Code'] ?? 'unknown'
        ]);

        // Update local customer with final CardCode (in case it was different)
        $customer->update(['code' => $cardCode]);

        return $response;
    }
}
