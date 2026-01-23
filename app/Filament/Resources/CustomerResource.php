<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\AccountPayable;
use App\Models\DealerCategory;
use App\Models\CustomerSeries;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\CustomerPaymentTerm;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Models\PriceList;
//use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Actions\Action;
use App\Services\SapService;
use Filament\Forms\Components\{
    Section,
    Grid,
    Toggle
};

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected function afterCreate(): void
    {
        $customer = $this->record; // The newly created customer

        try {
            $sapService = app(SapService::class);
            $sapService->sendCustomerToSap($customer);

            \Filament\Notifications\Notification::make()
                ->title('Customer posted to SAP successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Failed to post customer to SAP')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('General')
                    ->schema([
                        Grid::make(3)->schema([

                            Select::make('branch_id')
                                ->label('Branch')
                                ->relationship('branch', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    self::autoFillSeries($set, $get);
                                }),

                            TextInput::make('name')
                                ->label('Customer Name*')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    self::autoFillSeries($set, $get);
                                }),

                            // TextInput::make('series_id')
                            //     ->label('Series')
                            //     ->options(\App\Models\CustomerSeries::pluck('series', 'id')->toArray()) // key = id
                            //     ->searchable()
                            //     ->preload()
                            //     ->dehydrated(true)
                            //     //->disabled()           // read-only
                            //     ->required()
                            //     ->getOptionLabelUsing(fn($value) => \App\Models\CustomerSeries::find($value)?->series_name ?? $value)
                            //     ->default(fn($record) => $record?->series_id)
                            //     ->afterStateHydrated(function ($component, $state, $record) {
                            //         if ($record && $record->series_id) {
                            //             $component->state($record->series_id);
                            //         }
                            //     })
                            //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            //         if ($state) {
                            //             $series = \App\Models\CustomerSeries::find($state);
                            //             if ($series && $series->next_number !== null) {
                            //                 $nextNum = str_pad($series->next_number, 4, '0', STR_PAD_LEFT);
                            //                 $set('code', $series->series_name . $nextNum);
                            //             }
                            //         } else {
                            //             $set('code', null);
                            //         }
                            //     })
                            //     ->placeholder('Auto-filled based on Branch + Name'),
                            //     //->hint('Stored value = SAP series number (e.g. 1142)'),

                            TextInput::make('series_id')
                                ->label('Series Code')
                                ->disabled()             // Prevents manual selection
                                ->dehydrated(true)       // Ensures it is sent to the database on save
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        // State is the SAP code (e.g., 1141). We fetch the record to update the display and code.
                                        $series = \App\Models\CustomerSeries::where('series', $state)->first();

                                        if ($series) {
                                            $nextNum = str_pad($series->next_number, 4, '0', STR_PAD_LEFT);
                                            $set('code', $series->series_name . $nextNum);
                                            $set('series_display', $series->series_name);
                                        }
                                    }
                                }),

                            // TextInput::make('series_display')
                            //     ->label('Series Name')
                            //     ->disabled()
                            //     ->dehydrated(false), // Only for UI display



                            TextInput::make('code')
                                ->label('Customer Code')
                                //->required()
                                ->disabled()  // prevent manual edit
                                ->maxLength(50)
                                ->unique(table: 'customers', ignoreRecord: true)
                                ->placeholder('Auto-generated from Series (e.g. C-KIT-T0003)')
                                ->dehydrated(fn($state) => !empty($state)),

                            Select::make('group_id')
                                ->label('Group')
                                ->options(
                                    DB::table('customer_groups')->pluck('name', 'id')->toArray()
                                )
                                ->searchable()
                                ->preload()
                                //->default('NORMAL DEBTOR (LA)')
                                ->default(function () {
                                    // default to "NORMAL DEBTOR (LA)" by finding its ID
                                    return DB::table('customer_groups')
                                        ->where('name', 'NORMAL DEBTOR (LA)')
                                        ->value('id');
                                }),

                            // Select::make('group_id')
                            //     ->label('Group')
                            //     ->options(function () {
                            //         return DB::table('customer_groups')
                            //             ->where('group_type', 'C') // Only customer groups
                            //             ->pluck('name', 'id')
                            //             ->toArray();
                            //     })
                            //     ->preload()
                            //     ->default('NORMAL DEBTOR (LA)')
                            //     ->searchable(),


                            Select::make('currency_id')
                                ->label('Currency')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                                ->relationship('currency', 'code')
                                ->preload()
                                //->default('KES - Kenya Shillings')
                                ->searchable(),

                            TextInput::make('pin')
                                ->label('PIN')
                                ->maxLength(20),

                        ])
                    ]),

                Section::make('Contact Details')
                    ->schema([
                        Grid::make(3)->schema([

                            TextInput::make('tel1')
                                ->label('Tel 1'),
                            TextInput::make('tel2')->label('Tel 2'),
                            TextInput::make('mobile')->label('Mobile'),

                            TextInput::make('email')
                                ->label('Email')
                                ->required()
                                ->rule(fn($get) => function ($attribute, $value, $fail) {
                                    if ($value === 'N/A') return; // allow "N/A"
                                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                        $fail('The ' . $attribute . ' must be a valid email address or N/A.');
                                    }
                                }),

                            /*   TextInput::make('contact_id')->label('Contact ID'),*/

                            TextInput::make('id_staff_no_2')->label('ID / Staff No. 2'),
                            TextInput::make('contact_id')->label('Contact Person'),

                        ])
                    ]),

                Section::make('Address')
                    ->schema([
                        Grid::make(3)->schema([

                            TextInput::make('address_id')
                                ->placeholder('Bill to')
                                ->label('Address ID'),
                            TextInput::make('po_box')
                                ->placeholder('P.O. Box')
                                ->label('P.O. Box'),
                            TextInput::make('city')->label('City'),

                            Select::make('country_id')
                                ->label('Country')
                                ->preload()
                                ->relationship('country', 'name')
                                //->default('Kenya')
                                ->searchable(),

                        ])
                    ]),

                Section::make('Sales & Finance')
                    ->schema([
                        Grid::make(3)->schema([

                            Select::make('payment_term_id')
                                ->label('Payment Terms')
                                ->preload()
                                ->relationship('paymentTerm', 'name')  // <-- method name here
                                //->default('30 Days')
                                ->searchable(),


                            // Price List
                            // Price List
                            Select::make('price_list_id')
                                ->label('Price List')
                                ->relationship('priceList', 'name') // Uses ID internally, shows name
                                ->preload()
                                ->searchable()
                                ->default(fn($record) => $record?->price_list_id ?? \App\Models\PriceList::first()?->id)
                                ->required(),


                            Select::make('account_payable_id') // matches DB column
                                ->label('Account Payable')
                                ->relationship('accountPayable', 'account_name') // Uses the relationship
                                ->preload()
                                ->searchable()
                                ->default(fn($record) => $record?->account_payable_id ?? \App\Models\AccountPayable::first()?->id)
                                ->required(),

                            Select::make('dealer_category_id')
                                ->label('Dealer Category')
                                ->options(
                                    DealerCategory::orderBy('name')
                                        ->pluck('name', 'id') // key = id, value = name
                                        ->toArray()
                                )
                                ->default(function () {
                                    // default to "-No Industry-" ID
                                    return DealerCategory::where('name', '-No Industry-')->value('id');
                                })
                                ->searchable(),

                            Select::make('dealer_type_id')
                                ->label('Dealer Type')
                                ->preload()
                                ->relationship('dealerType', 'name')
                                ->searchable(),

                            Select::make('territory_id')
                                ->label('Territory')
                                ->preload()
                                //->default('-No Territory-')
                                ->relationship('territory', 'name')
                                ->searchable(),

                            TextInput::make('dealer_discount')
                                ->label('Dealer Discount (%)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),

                        ])

                    ]),

                Select::make('property_no')
                    ->label('Property')
                    ->options(\App\Models\Property::pluck('name', 'code'))
                    ->searchable()
                    ->preload(),

                Section::make('Attachments')
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('attachments')           // files go to storage/app/attachments
                            ->preserveFilenames()                // keep original names (careful with security/duplicates)
                            ->enableDownload()
                            ->enableOpen()
                            ->disk('public')                      // optional – 'local' is default anyway
                            ->visibility('public')              // or 'public' – your choice
                            ->afterStateHydrated(function (FileUpload $component, $record) {
                                // Important for edit: load existing paths from DB
                                if ($record) {
                                    $component->state($record->attachments ?? []);
                                }
                            })
                        // No need for getStateForStorageUsing() – Filament already stores array of paths
                        // No need for getUploadedFileNameForStorageUsing() unless you want to rename files
                        // (you already have preserveFilenames() which achieves similar result)
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('paymentTerm.term_name')->label('Payment Term'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    protected static function autoFillSeries(callable $set, callable $get): void
    {
        $branchId     = $get('branch_id');
        $customerName = trim($get('name') ?? '');

        // Early exit if missing data
        if (!$branchId || !$customerName) {
            $set('series_id', null);
            return;
        }

        $branch = \App\Models\Branch::find($branchId);
        if (!$branch || empty($branch->code)) {
            $set('series_id', null);
            return;
        }

        $branchCode   = strtoupper(trim($branch->code));  // e.g. "KIT"
        $firstLetter  = strtoupper(substr($customerName, 0, 1));  // e.g. "T"

        // Adjusted pattern: 'C-KIT-T%'
        $pattern = "C-{$branchCode}-{$firstLetter}%";

        $series = DB::table('customer_series')
            ->where('series_name', 'like', $pattern)
            ->first();

        $set('series_id', $series ? $series->series : null);
    }

    public static function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('send_to_sap')
                ->label('Send to SAP')
                ->button()
                ->color('success')
                ->action(function () {
                    // This runs on the list page — usually not useful for create
                    // Better to use form actions instead
                }),
        ];
    }
}
