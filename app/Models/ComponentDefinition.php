<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentDefinition extends Model
{
    protected $fillable = [
        'type',
        'nom',
        'variant_of_id',
        'variant_nom',
        'statut',
        'version_courante_id',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ComponentVersion::class)->latest();
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ComponentVersion::class, 'version_courante_id');
    }

    public function variantOf(): BelongsTo
    {
        return $this->belongsTo(ComponentDefinition::class, 'variant_of_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ComponentDefinition::class, 'variant_of_id');
    }
}
