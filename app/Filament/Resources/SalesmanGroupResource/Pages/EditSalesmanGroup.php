<?php

namespace App\Filament\Resources\SalesmanGroupResource\Pages;

use App\Filament\Resources\SalesmanGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesmanGroup extends EditRecord
{
    protected static string $resource = SalesmanGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
