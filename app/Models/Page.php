<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'site_id',
        'titre',
        'slug',
        'statut',
        'publie_le',
    ];

    protected function casts(): array
    {
        return [
            'publie_le' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pageBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('ordre');
    }
}
