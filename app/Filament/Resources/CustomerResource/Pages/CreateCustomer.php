<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CustomerResource;
use App\Services\SapService;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Customer')
                ->submit('create'),

            Action::make('send_to_sap')
                ->label('Send to SAP')
                ->button()
                ->color('success')
                ->icon('heroicon-o-cloud-arrow-up')
                ->action(function () {
                    if (!$this->record) {
                        Notification::make()
                            ->warning()
                            ->title('Please save first')
                            ->body('Save the customer before sending to SAP.')
                            ->send();
                        return;
                    }

                    app(SapService::class)->sendCustomerToSap($this->record);

                    Notification::make()
                        ->success()
                        ->title('Success')
                        ->body('Customer successfully sent to SAP!')
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Send to SAP')
                ->modalDescription('This will post the customer to SAP. Are you sure?')
                ->modalSubmitActionLabel('Yes, send now'),
        ];
    }
}