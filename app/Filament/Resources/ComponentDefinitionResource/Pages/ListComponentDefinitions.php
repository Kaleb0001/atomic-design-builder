<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Pages;

use App\Filament\Resources\ComponentDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentDefinitions extends ListRecords
{
    protected static string $resource = ComponentDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
