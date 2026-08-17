<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    protected $fillable = [
        'page_id',
        'component_definition_id',
        'ordre',
        'actif',
        'contenu_json',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'contenu_json' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function componentDefinition(): BelongsTo
    {
        return $this->belongsTo(ComponentDefinition::class);
    }
}
