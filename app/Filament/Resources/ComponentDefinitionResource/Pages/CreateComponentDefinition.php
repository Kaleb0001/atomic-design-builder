<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Pages;

use App\Filament\Resources\ComponentDefinitionResource;
use App\Filament\Resources\ComponentDefinitionResource\Concerns\ResolvesPreview;
use Filament\Resources\Pages\CreateRecord;

class CreateComponentDefinition extends CreateRecord
{
    use ResolvesPreview;

    protected static string $resource = ComponentDefinitionResource::class;

    protected array $pendingVersionData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $code = $data['code'] ?? [];

        $this->pendingVersionData = [
            'code_html' => $code['html'] ?? null,
            'code_css' => $code['css'] ?? null,
            'code_js' => $code['js'] ?? null,
            'message' => $data['message'] ?? 'Version initiale',
        ];

        unset($data['code'], $data['message']);

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
