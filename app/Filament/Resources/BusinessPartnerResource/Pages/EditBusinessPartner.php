<?php

namespace App\Filament\Resources\BusinessPartnerResource\Pages;

use App\Filament\Resources\BusinessPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessPartner extends EditRecord
{
    protected static string $resource = BusinessPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
