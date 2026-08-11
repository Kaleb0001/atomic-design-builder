<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Pages;

use App\Filament\Resources\ComponentDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComponentDefinition extends CreateRecord
{
    protected static string $resource = ComponentDefinitionResource::class;

    protected array $pendingVersionData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingVersionData = [
            'code_html' => $data['code_html'] ?? null,
            'code_css' => $data['code_css'] ?? null,
            'code_js' => $data['code_js'] ?? null,
            'message' => $data['message'] ?? 'Version initiale',
        ];

        unset($data['code_html'], $data['code_css'], $data['code_js'], $data['message']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $version = $this->record->versions()->create([
            ...$this->pendingVersionData,
            'auteur_id' => auth()->id(),
        ]);

        $this->record->update(['version_courante_id' => $version->id]);
    }
}
