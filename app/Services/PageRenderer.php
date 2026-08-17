<?php

namespace App\Services;

use App\Models\ComponentDefinition;
use App\Models\Page;

class PageRenderer
{
    public function __construct(protected ComponentResolver $componentResolver) {}

    /**
     * Assemble une page déjà enregistrée à partir de ses blocs actifs, dans l'ordre.
     *
     * @return array{0: string, 1: string} [html, css]
     */
    public function renderPage(Page $page): array
    {
        $blocks = $page->pageBlocks()
            ->where('actif', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn ($block) => [
                'component_definition_id' => $block->component_definition_id,
                'contenu_json' => $block->contenu_json ?? [],
            ])
            ->all();

        return $this->renderBlocks($blocks);
    }

    /**
     * Assemble à partir d'un tableau brut de blocs (utilisé aussi pour l'aperçu live
     * avant sauvegarde, depuis l'état du formulaire Filament).
     *
     * @param  array<array{component_definition_id: int|null, contenu_json: array|null}>  $blocks
     * @return array{0: string, 1: string} [html, css]
     */
    public function renderBlocks(array $blocks): array
    {
        $html = '';
        $css = '';

        foreach ($blocks as $block) {
            $component = ComponentDefinition::with('currentVersion')
                ->find($block['component_definition_id'] ?? null);

            if (! $component || $component->statut !== 'publie' || ! $component->currentVersion) {
                continue;
            }

            $blockHtml = $component->currentVersion->code_html ?? '';
            $blockCss = $component->currentVersion->code_css ?? '';

            [$blockHtml, $blockCss] = $this->componentResolver->resolve($blockHtml, $blockCss);

            $blockHtml = $this->substitutePlaceholders($blockHtml, $block['contenu_json'] ?? []);

            $html .= $blockHtml;
            $css .= "\n".$blockCss;
        }

        return [$html, $css];
    }

    /**
     * Détecte les placeholders {{champ}} présents dans un HTML (utilisé pour générer
     * dynamiquement les champs de contenu d'un bloc dans le formulaire).
     *
     * @return array<int, string>
     */
    public function detectPlaceholders(string $html): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $html, $matches);

        return array_values(array_unique($matches[1]));
    }

    protected function substitutePlaceholders(string $html, array $values): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            fn (array $matches) => $values[$matches[1]] ?? $matches[0],
            $html
        );
    }
}
