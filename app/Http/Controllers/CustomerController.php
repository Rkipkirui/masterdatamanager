<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\SapService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // existing methods...

    public function sendToSap(Customer $customer, SapService $sapService)
    {
        // Optional: dd payload to debug
        // dd($sapService->buildCustomerPayload($customer));

        $response = $sapService->sendCustomerToSap($customer);

        return redirect()->back()->with('success', 'Customer sent to SAP successfully!');
    }
}
