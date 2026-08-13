<?php

namespace App\Services;

use App\Models\ComponentDefinition;
use DOMComment;
use DOMDocument;
use DOMXPath;

class ComponentResolver
{
    protected const MAX_DEPTH = 8;

    /**
     * Résout récursivement les références vivantes (data-atomic-ref="ID") présentes
     * dans du HTML : chaque référence est remplacée par le HTML actuellement publié
     * du composant visé, et son CSS est concaténé au CSS résolu. Protégé contre les
     * références circulaires et les chaînes de références trop profondes.
     *
     * @param  array<int>  $visited
     * @return array{0: string, 1: string} [html résolu, css résolu]
     */
    public function resolve(string $html, string $css = '', array $visited = [], int $depth = 0): array
    {
        if (trim($html) === '' || $depth >= self::MAX_DEPTH) {
            return [$html, $css];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="__root__">'.$html.'</div>');
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $resolvedCss = $css;
        $nodes = iterator_to_array($xpath->query('//*[@data-atomic-ref]'));

        foreach ($nodes as $node) {
            $id = (int) $node->getAttribute('data-atomic-ref');

            if (in_array($id, $visited, true)) {
                $node->parentNode->replaceChild(
                    new DOMComment(" référence circulaire ignorée (composant #{$id}) "),
                    $node
                );

                continue;
            }

            $component = ComponentDefinition::with('currentVersion')->find($id);

            if (! $component || $component->statut !== 'publie' || ! $component->currentVersion) {
                $node->parentNode->replaceChild(
                    new DOMComment(" composant #{$id} introuvable ou non publié "),
                    $node
                );

                continue;
            }

            [$childHtml, $childCss] = $this->resolve(
                $component->currentVersion->code_html ?? '',
                $component->currentVersion->code_css ?? '',
                [...$visited, $id],
                $depth + 1
            );

            $resolvedCss .= "\n".$childCss;

            $fragment = $dom->createDocumentFragment();
            $childDom = new DOMDocument();
            libxml_use_internal_errors(true);
            $childDom->loadHTML('<?xml encoding="utf-8" ?><div id="__child__">'.$childHtml.'</div>');
            libxml_clear_errors();

            $childRoot = $childDom->getElementById('__child__');

            if ($childRoot) {
                foreach (iterator_to_array($childRoot->childNodes) as $childNode) {
                    $fragment->appendChild($dom->importNode($childNode, true));
                }
            }

            $node->parentNode->replaceChild($fragment, $node);
        }

        $root = $dom->getElementById('__root__');
        $resolvedHtml = '';

        if ($root) {
            foreach (iterator_to_array($root->childNodes) as $child) {
                $resolvedHtml .= $dom->saveHTML($child);
            }
        }

        return [$resolvedHtml, $resolvedCss];
    }
}
