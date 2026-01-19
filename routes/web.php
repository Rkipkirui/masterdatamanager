<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('customers', CustomerController::class);
Route::post('/customers/{customer}/send-to-sap', [CustomerController::class, 'sendToSap'])
    ->name('customers.send-to-sap');
