<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Services\PageRenderer;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public ?array $previewResult = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->refreshPreview();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['statut'] ?? null) === 'publie' && empty($data['publie_le'])) {
            $data['publie_le'] = now();
        }

        return $data;
    }

    public function refreshPreview(): void
    {
        $blocks = $this->form->getRawState()['pageBlocks'] ?? [];

        $activeBlocks = collect($blocks)
            ->filter(fn (array $block) => $block['actif'] ?? true)
            ->values()
            ->all();

        [$html, $css] = app(PageRenderer::class)->renderBlocks($activeBlocks);

        $this->previewResult = ['html' => $html, 'css' => $css];
    }
}
