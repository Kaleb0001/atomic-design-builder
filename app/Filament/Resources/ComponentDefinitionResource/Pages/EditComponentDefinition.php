<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Pages;

use App\Filament\Resources\ComponentDefinitionResource;
use App\Filament\Resources\ComponentDefinitionResource\Concerns\ResolvesPreview;
use Filament\Resources\Pages\EditRecord;

class EditComponentDefinition extends EditRecord
{
    use ResolvesPreview;

    protected static string $resource = ComponentDefinitionResource::class;

    protected array $pendingVersionData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $currentVersion = $this->record->currentVersion;

        $data['code'] = [
            'html' => $currentVersion?->code_html,
            'css' => $currentVersion?->code_css,
            'js' => $currentVersion?->code_js,
        ];
        $data['message'] = null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $code = $data['code'] ?? [];

        $this->pendingVersionData = [
            'code_html' => $code['html'] ?? null,
            'code_css' => $code['css'] ?? null,
            'code_js' => $code['js'] ?? null,
            'message' => $data['message'] ?? null,
        ];

        unset($data['code'], $data['message']);

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
