<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Services\PageRenderer;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['statut'] ?? null) === 'publie' && empty($data['publie_le'])) {
            $data['publie_le'] = now();
        }

        return $data;
    }

    /**
     * Appelée depuis le JS (aperçu) via $wire.call. getRawState() est utilisé plutôt
     * que getState() pour ne pas déclencher la validation pendant qu'on tape.
     */
    public function renderPagePreview(): array
    {
        $blocks = $this->form->getRawState()['pageBlocks'] ?? [];

        $activeBlocks = collect($blocks)
            ->filter(fn (array $block) => $block['actif'] ?? true)
            ->values()
            ->all();

        [$html, $css] = app(PageRenderer::class)->renderBlocks($activeBlocks);

        return ['html' => $html, 'css' => $css];
    }
}
