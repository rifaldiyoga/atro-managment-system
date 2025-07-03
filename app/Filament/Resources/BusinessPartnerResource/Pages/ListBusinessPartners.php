<?php

namespace App\Filament\Resources\BusinessPartnerResource\Pages;

use App\Filament\Resources\BusinessPartnerResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBusinessPartners extends ListRecords
{
    protected static string $resource = BusinessPartnerResource::class;


    public function getTabs(): array
    {
        return [
            'customer' => Tab::make('Customer')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('partner_type', 'customer')),
            'supplier' => Tab::make('Supplier')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('partner_type', 'supplier')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
