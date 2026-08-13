<?php

namespace App\Filament\Resources\ComponentDefinitionResource\Concerns;

use App\Services\ComponentResolver;

trait ResolvesPreview
{
    /**
     * Appelée depuis le JS via $wire.call('resolvePreviewHtml', state) — que ce soit
     * depuis l'éditeur de code (Module 3) ou le WYSIWYG (Module 4), pour que les deux
     * modes affichent un aperçu cohérent, références vivantes comprises.
     */
    public function resolvePreviewHtml(array $code): array
    {
        $resolver = app(ComponentResolver::class);

        [$html, $css] = $resolver->resolve($code['html'] ?? '', $code['css'] ?? '');

        return [
            'html' => $html,
            'css' => $css,
            'js' => $code['js'] ?? '',
        ];
    }
}
