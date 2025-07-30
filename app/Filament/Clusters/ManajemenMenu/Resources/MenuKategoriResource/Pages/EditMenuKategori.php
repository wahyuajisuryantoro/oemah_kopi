<?php

namespace App\Filament\Clusters\ManajemenMenu\Resources\MenuKategoriResource\Pages;

use App\Filament\Clusters\ManajemenMenu\Resources\MenuKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuKategori extends EditRecord
{
    protected static string $resource = MenuKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
