<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Purchase Order';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Purchase Order';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('customer_id')
                                ->label('Customer')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->required(),
                            DatePicker::make('trxdate')->label('Tanggal')->required(),
                            TextInput::make('po_no')->label('No. Purchase Order')->required(),
                            Placeholder::make('')->content(''),

                            TextInput::make('ph_no')->label('No. Penawaran Harga')->required(),

                            TextInput::make('phd')->numeric()->label('Penawaran Harga & Delivery')->nullable(),


                            TextInput::make('rfq_number')->label('RFQ No')->nullable(),
                            TextInput::make('rfq_duration')->label('Delivery Time')->nullable(),

                        ]),

                        Repeater::make('items')
                            ->relationship()
                            ->columnSpanFull()

                            ->label('Daftar Item')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('item_id')
                                            ->label('Nama')
                                            ->relationship('item', 'name')
                                            ->searchable()
                                            ->placeholder('Pilih Item')
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('purchase_price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('selling_price')
                                            ->label('Harga Jual')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('discount')
                                            ->label('Diskon')
                                            ->numeric()
                                            ->nullable(),

                                        Select::make('supplier_id')
                                            ->label('Supplier')
                                            ->relationship('supplier', 'name')

                                            ->placeholder('Pilih Supplier')
                                            ->searchable()
                                            ->required(),

                                        // Textarea::make('dnotes')
                                        //     ->label('Keterangan')
                                        //     ->rows(1),

                                        // FileUpload::make('attachment')
                                        //     ->label('Attachment')
                                        //     ->disk('public') // adjust disk as needed
                                        //     ->directory('attachments')
                                        //     ->preserveFilenames()
                                        //     ->nullable(),

                                    ])
                                    ->columns(6)
                                    ->extraAttributes(['class' => 'min-w-[1100px]'])
                            ])
                            ->columns(1)
                            ->addActionLabel('Add Item'),

                        Grid::make(4)->schema([
                            Textarea::make('notes')
                                ->label('Keterangan')
                                ->columnSpan(2)
                                ->rows(4),
                            FileUpload::make('attachments')
                                ->label('Lampiran')
                                ->required()
                                ->disk('public')
                                ->directory('housing-units')
                                ->openable()
                                ->columnSpan(2)
                                ->panelLayout('grid')
                                ->multiple()
                                ->moveFiles(),
                        ])
                    ]),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_no')
                    ->label('No. PO')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phd')
                    ->label('Harga & Delivery')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name') // Better to show customer name instead of ID
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trxdate')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rfq_number')
                    ->label('No. RFQ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rfq_duration')
                    ->label('Waktu Pengiriman')
                    ->searchable(),

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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
