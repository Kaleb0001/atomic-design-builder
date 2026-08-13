<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class CodeEditorField extends Field
{
    protected string $view = 'filament.forms.components.code-editor-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'html' => '',
            'css' => '',
            'js' => '',
        ]);
    }
}
