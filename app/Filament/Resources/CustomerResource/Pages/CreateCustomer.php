<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\SapService;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('create_and_send_to_sap')
                ->label('Create Customer & Send to SAP')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->submit('create') // ✅ IMPORTANT
                ->requiresConfirmation()
                ->modalHeading('Create Customer & Send to SAP')
                ->modalDescription('This will save the customer and post it to SAP. Are you sure?')
                ->modalSubmitActionLabel('Yes, proceed')
                ->after(function () {   // ✅ Runs AFTER customer is created

                    $customer = $this->record;

                    try {
                        app(SapService::class)->sendCustomerToSap($customer);

                        Notification::make()
                            ->success()
                            ->title('Customer created & posted to SAP')
                            ->send();

                    } catch (\Exception $e) {

                        Notification::make()
                            ->danger()
                            ->title('SAP Error')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}

