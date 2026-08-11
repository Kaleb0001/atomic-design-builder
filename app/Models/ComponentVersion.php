<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentVersion extends Model
{
    protected $fillable = [
        'component_definition_id',
        'code_html',
        'code_css',
        'code_js',
        'auteur_id',
        'message',
    ];

    public function componentDefinition(): BelongsTo
    {
        return $this->belongsTo(ComponentDefinition::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
