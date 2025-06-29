<?php

namespace App\Filament\Resources\OfferRequestResource\Pages;

use App\Filament\Resources\OfferRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfferRequests extends ListRecords
{
    protected static string $resource = OfferRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
