<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Pages;

use App\Filament\Resources\ComponentDefinitionResource;
use Filament\Resources\Pages\EditRecord;

class EditComponentDefinition extends EditRecord
{
    protected static string $resource = ComponentDefinitionResource::class;

    protected array $pendingVersionData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $currentVersion = $this->record->currentVersion;

        $data['code_html'] = $currentVersion?->code_html;
        $data['code_css'] = $currentVersion?->code_css;
        $data['code_js'] = $currentVersion?->code_js;
        $data['message'] = null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingVersionData = [
            'code_html' => $data['code_html'] ?? null,
            'code_css' => $data['code_css'] ?? null,
            'code_js' => $data['code_js'] ?? null,
            'message' => $data['message'] ?? null,
        ];

        unset($data['code_html'], $data['code_css'], $data['code_js'], $data['message']);

        return $data;
    }

    protected function afterSave(): void
    {
        $version = $this->record->versions()->create([
            ...$this->pendingVersionData,
            'auteur_id' => auth()->id(),
        ]);

        $this->record->update(['version_courante_id' => $version->id]);
    }
}
