<?php

namespace App\Filament\Forms\Components;

use App\Models\ComponentDefinition;
use Filament\Forms\Components\Field;

class WysiwygEditorField extends Field
{
    protected string $view = 'filament.forms.components.wysiwyg-editor-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'html' => '',
            'css' => '',
            'js' => '',
        ]);
    }

    /**
     * Composants publiés utilisables comme blocs, en excluant le composant en cours
     * d'édition lui-même (une auto-référence directe n'a pas de sens). Les cycles plus
     * profonds passant par d'autres composants restent possibles et sont gérés par
     * ComponentResolver, pas ici.
     */
    public function getBlocks(): array
    {
        $currentId = $this->getRecord()?->id;

        return ComponentDefinition::query()
            ->where('statut', 'publie')
            ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
            ->orderBy('type')
            ->orderBy('nom')
            ->get()
            ->map(fn (ComponentDefinition $component) => [
                'id' => $component->id,
                'label' => $component->nom.($component->variant_nom ? ' ('.$component->variant_nom.')' : ''),
                'category' => ucfirst($component->type),
            ])
            ->values()
            ->all();
    }
}
