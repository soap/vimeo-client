<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class VimeoItem extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_accessed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeVideo(Builder $query)
    {
        $query->where('item_type', 'video');
    }

    public function scopeFolder(Builder $query)
    {
        $query->where('item_type', 'folder');
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('item_type', $type);
    }
}
