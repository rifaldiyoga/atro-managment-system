<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferRequestResource\Pages;
use App\Filament\Resources\OfferRequestResource\RelationManagers;
use App\Models\OfferRequest;
use App\Models\SalesmanGroup;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class OfferRequestResource extends Resource
{
    protected static ?string $model = OfferRequest::class;

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Permintaan Penawaran';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Permintaan Penawaran';
    protected static ?string $modelLabel = 'Permintaan Penawaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('customer_id')
                                ->label('Customer')

                                ->placeholder('Pilih Customer')
                                // Menambahkan query untuk memfilter berdasarkan partner_type
                                ->relationship(
                                    name: 'customer',
                                    titleAttribute: 'name',
                                    // Menghapus type hint Builder untuk memperbaiki error
                                    modifyQueryUsing: fn($query) => $query->where('partner_type', 'customer')
                                )
                                ->searchable()
                                ->required(),
                            DatePicker::make('trxdate')->label('Tanggal')->required()->default(Carbon::now()),
                            TextInput::make('ph_no')->label('No. Penawaran Harga')->required()->disabled()->default('AUTO'),

                            Placeholder::make('')->content(''),

                            TextInput::make('rfq_number')->label('No. RFQ')->nullable(),
                            TextInput::make('validity')->label('Validity')->nullable(),
                            TextInput::make('payment_deadline')->label('Pembayaran')->nullable()->default('1 Bulan'),

                            TextInput::make('rfq_duration')->label('Delivery Time')->nullable(),

                            Select::make('salesman_id')
                                ->label('Salesmen')

                                ->placeholder('Pilih Salesmen')
                                // Menambahkan query untuk memfilter berdasarkan partner_type
                                ->relationship(
                                    name: 'salesman',
                                    titleAttribute: 'name',
                                )
                                ->getOptionLabelFromRecordUsing(function (SalesmanGroup $record) {
                                    $salesmenNames = $record->salesmen->pluck('name')->implode(', ');
                                    return "{$record->name} ({$salesmenNames})";
                                })
                                ->searchable(),



                        ]),
                        Forms\Components\Group::make([
                            TableRepeater::make('items')
                                ->label('')
                                ->relationship()
                                ->columnSpan('full')
                                ->headers([
                                    Header::make('Item')->width('200px')->markAsRequired(),
                                    Header::make('Qty')->width('100px')->markAsRequired(),
                                    Header::make('Harga Beli')->width('200px')->markAsRequired(),
                                    Header::make('Harga Jual')->width('200px')->markAsRequired(),
                                    Header::make('Diskon')->width('100px'),
                                    Header::make('Supplier')->width('150px')->markAsRequired(),
                                    // Header::make('Keterangan')->width('250px'),
                                ])
                                ->schema([
                                    Select::make('item_id')
                                        ->label('Product')
                                        ->searchable()
                                        ->required()
                                        ->relationship('item', 'name')
                                        ->placeholder('Pilih Item'),

                                    TextInput::make('quantity')
                                        ->label('Qty')
                                        ->numeric()
                                        ->required(),

                                    TextInput::make('purchase_price')
                                        ->label('Purchase Price')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('selling_price')
                                        ->label('Selling Price')
                                        ->numeric()
                                        ->required(),



                                    TextInput::make('discount')
                                        ->label('Discount')
                                        ->numeric()
                                        ->nullable(),

                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->placeholder('Pilih Supplier')
                                        ->relationship(
                                            name: 'supplier',
                                            titleAttribute: 'name',
                                            // Menghapus type hint Builder untuk memperbaiki error
                                            modifyQueryUsing: fn($query) => $query->where('partner_type', 'supplier')
                                        )
                                        ->searchable()
                                        ->required(),
                                    // Textarea::make('dnotes')
                                    //     ->hiddenLabel()
                                    //     ->rows(1),
                                ])
                                ->columnSpan('full')
                        ]),

                        Grid::make(4)->schema([
                            Textarea::make('notes')
                                ->label('Keterangan')
                                ->columnSpan(1)
                                ->rows(4),
                            // Placeholder::make('')->content(''),
                            // Placeholder::make('')->content(''),
                            FileUpload::make('attachment')
                                ->label('Lampiran')
                                ->disk('public')
                                ->multiple()
                                ->openable()
                                ->panelLayout('grid')
                                ->columnSpan(2)
                                ->moveFiles(),
                        ])
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ph_no')
                    ->label('No Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trxdate')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('create_po')
                    ->label('Buat PO')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    // Mengubah action menjadi URL redirect
                    ->url(function (OfferRequest $record): string {
                        // Mengarahkan ke halaman 'create' Order dengan membawa ID OfferRequest
                        return OrderResource::getUrl('create', ['offer_request_id' => $record->id]);
                    }),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListOfferRequests::route('/'),
            'create' => Pages\CreateOfferRequest::route('/create'),
            'edit' => Pages\EditOfferRequest::route('/{record}/edit'),
        ];
    }
}
