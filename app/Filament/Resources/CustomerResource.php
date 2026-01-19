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
use Filament\Forms\Components\{
    Section,
    Grid,
    Toggle
};

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

                            Select::make('series_id')
                                ->label('Series')
                                ->relationship('customerSeries', 'series_name')
                                ->disabled()           // read-only - user cannot change it
                                ->required()
                                ->placeholder('Auto-filled based on Branch + Name'),

                            TextInput::make('code')
                                ->label('Customer Code*')
                                ->required()
                                ->disabled()
                                ->maxLength(50)
                                ->unique(table: 'customers', ignoreRecord: true),

                            Select::make('group_id')
                                ->label('Group')
                                ->options(function () {
                                    return DB::table('customer_groups')
                                        ->where('group_type', 'C') // Only customer groups
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->preload()
                                ->searchable(),


                            Select::make('currency_id')
                                ->label('Currency')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                                ->relationship('currency', 'code')
                                ->preload()
                                ->default('KES - Kenya Shillings')
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

                            TextInput::make('email')->email()
                                ->required()
                                ->label('Email'),

                            /*   TextInput::make('contact_id')->label('Contact ID'),*/

                            TextInput::make('id_staff_no_2')->label('ID / Staff No. 2'),
                            TextInput::make('contact_id')->label('Contact ID'),

                        ])
                    ]),

                Section::make('Address')
                    ->schema([
                        Grid::make(3)->schema([

                            TextInput::make('address_id')->label('Address ID'),
                            TextInput::make('po_box')->label('P.O. Box'),
                            TextInput::make('city')->label('City'),

                            Select::make('country_id')
                                ->label('Country')
                                ->preload()
                                ->relationship('country', 'name')
                                ->default('Kenya')
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
                                ->default('30 Days')
                                ->searchable(),


                            Select::make('price_list_name')
                                ->label('Price List')
                                ->options(
                                    \App\Models\PriceList::orderBy('name')->pluck('name', 'name')->toArray()
                                )
                                ->default('Default Price list')
                                ->searchable(),


                            Select::make('account_payable') // the column in customers table
                                ->label('Account Payable')
                                ->options(
                                    AccountPayable::orderBy('account_name')
                                        ->pluck('account_name', 'account_name')
                                        ->toArray()
                                )
                                ->default('Debtors control Account - Local debtors')
                                ->searchable(),



                            Select::make('dealer_category_id')
                                ->label('Dealer Category')
                                ->options(
                                    DealerCategory::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->default('-No Industry-')
                                ->searchable(),

                            Select::make('dealer_type_id')
                                ->label('Dealer Type')
                                ->preload()
                                ->relationship('dealerType', 'name')
                                ->searchable(),

                            Select::make('territory_id')
                                ->label('Territory')
                                ->preload()
                                ->default('-No Territory-')
                                ->relationship('territory', 'name')
                                ->searchable(),

                            TextInput::make('dealer_discount')
                                ->label('Dealer Discount (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),

                        ])
                    ]),

                Select::make('properties')
                    ->label('Properties')
                    //->multiple()
                    ->relationship('properties', 'name')
                    ->preload()
                    ->searchable(),


                Section::make('Attachments')
                    ->schema([
                        FileUpload::make('attachments')
                            ->multiple()
                            ->directory('customer-attachments')
                            ->preserveFilenames()
                            ->label('Attachments'),
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

        $set('series_id', $series ? $series->id : null);
    }
}
