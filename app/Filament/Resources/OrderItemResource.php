<?php

namespace App\Filament\Resources;

use App\Models\OrderItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderItemResource\Pages;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;


    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Item Per PO';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Laporan Item Per PO';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.code')->label('Kode Item'),
                Tables\Columns\TextColumn::make('item.name')->label('Nama Item'),
                Tables\Columns\TextColumn::make('quantity')->label('Qty'),
                Tables\Columns\TextColumn::make('selling_price')->money('IDR', true)->label('Selling'),
                Tables\Columns\TextColumn::make('purchase_price')->money('IDR', true)->label('Purchase'),

                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),

                // Order Details
                Tables\Columns\TextColumn::make('order.po_no')->label('No. Purchase Order'),
                Tables\Columns\TextColumn::make('order.ph_no')->label('No. Penawaran Harga'),
                Tables\Columns\TextColumn::make('order.phd')->label('PHD'),
                Tables\Columns\TextColumn::make('order.customer.name')->label('Customer'),
                Tables\Columns\TextColumn::make('order.trxdate')->date()->label('Order Date'),

            ])
            ->filters([
                // 🔍 Filter by Product
                Filter::make('item_name')
                    ->form([
                        TextInput::make('value')->label('Product Name'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'],
                            fn($q, $value) => $q->whereHas('item', fn($q2) => $q2->where('name', 'like', "%{$value}%"))
                        );
                    }),

                // 📦 Filter by Supplier
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->multiple()
                    ->relationship('supplier', 'name')
                    ->searchable(),

                // 👤 Filter by Customer (via order relation)
                SelectFilter::make('order.customer_id')
                    ->label('Customer')
                    ->multiple()
                    ->relationship('order.customer', 'name')
                    ->searchable(),

                // 📅 Filter by Order Date Range
                Filter::make('order_date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('To Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereHas('order', fn($q2) => $q2->whereDate('trxdate', '>=', $date)))
                            ->when($data['until'], fn($q, $date) => $q->whereHas('order', fn($q2) => $q2->whereDate('trxdate', '<=', $date)));
                    }),
            ])
            ->actions([]) // Removes View/Edit/Delete buttons
            ->bulkActions([]); // Removes checkboxes & bulk actions
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderItems::route('/'),
        ];
    }
}
