<?php

namespace App\Filament\Forms\Components;

use App\Models\ComponentDefinition;
use Filament\Forms\Components\Field;

class HybridEditorField extends Field
{
    protected string $view = 'filament.forms.components.hybrid-editor-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'html' => '',
            'css' => '',
            'js' => '',
        ]);
    }

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

    public function canWriteCode(): bool
    {
        return auth()->user()?->can('components.write_code') ?? false;
    }
}
