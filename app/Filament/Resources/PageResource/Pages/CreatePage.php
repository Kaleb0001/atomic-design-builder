<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Services\PageRenderer;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public ?array $previewResult = null;

    public function mount(): void
    {
        parent::mount();

        $this->refreshPreview();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['statut'] ?? null) === 'publie' && empty($data['publie_le'])) {
            $data['publie_le'] = now();
        }

        return $data;
    }

    /**
     * Appelée automatiquement (afterStateUpdated sur le Repeater et ses champs live)
     * et depuis le bouton "Actualiser". getRawState() plutôt que getState() pour ne
     * pas déclencher la validation pendant la saisie.
     */
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
