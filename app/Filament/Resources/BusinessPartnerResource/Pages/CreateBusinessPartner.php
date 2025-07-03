<?php

namespace App\Filament\Resources\BusinessPartnerResource\Pages;

use App\Filament\Resources\BusinessPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessPartner extends CreateRecord
{
    protected static string $resource = BusinessPartnerResource::class;

    protected function getDefaultFormValues(): array
    {
        $partnerType = request()->query('activeTab', null);

        return [
            'partner_type' => $partnerType,
        ];
    }
}
