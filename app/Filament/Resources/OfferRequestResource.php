<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferRequestResource\Pages;
use App\Filament\Resources\OfferRequestResource\RelationManagers;
use App\Models\OfferRequest;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfferRequestResource extends Resource
{
    protected static ?string $model = OfferRequest::class;

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Penawaran Permintaan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Penawaran Permintaan';

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
                            DatePicker::make('date')->label('Tanggal')->required(),

                            Placeholder::make('')->content(''),
                            Placeholder::make('')->content(''),

                            TextInput::make('ph_no')->label('No. Penawaran Harga')->required(),

                            TextInput::make('phd')->numeric()->label('Penawaran Harga & Delivery')->nullable(),


                            TextInput::make('rfq_number')->label('RFQ No')->nullable(),
                            TextInput::make('rfq_duration')->label('Delivery Time')->nullable(),

                        ]),


                        Repeater::make('items')
                            ->relationship()
                            ->columnSpanFull()
                            ->label('Order Items')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('item_id')
                                            ->label('Product')
                                            ->relationship('item', 'name')
                                            ->placeholder('Pilih Item')
                                            ->searchable()
                                            ->required(),

                                        Select::make('supplier_id')
                                            ->label('Supplier')
                                            ->relationship('supplier', 'name')
                                            ->searchable()
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('selling_price')
                                            ->label('Selling Price')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('purchase_price')
                                            ->label('Purchase Price')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('discount')
                                            ->label('Discount %')
                                            ->suffix('%')
                                            ->numeric()
                                            ->nullable(),

                                        Textarea::make('notes')
                                            ->label('Notes')
                                            ->rows(1),
                                    ])
                                    ->columns(7)
                                    ->extraAttributes(['class' => 'min-w-[1000px]'])
                            ])
                            ->columns(1)
                            ->deletable(false)
                            ->addActionLabel('Add Item'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('offer_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phd')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rfq_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rfq_duration')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListOfferRequests::route('/'),
            'create' => Pages\CreateOfferRequest::route('/create'),
            'edit' => Pages\EditOfferRequest::route('/{record}/edit'),
        ];
    }
}
