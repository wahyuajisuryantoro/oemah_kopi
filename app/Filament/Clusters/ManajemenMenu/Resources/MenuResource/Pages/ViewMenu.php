<?php

namespace App\Filament\Clusters\ManajemenMenu\Resources\MenuResource\Pages;

use App\Filament\Clusters\ManajemenMenu\Resources\MenuResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMenu extends ViewRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}