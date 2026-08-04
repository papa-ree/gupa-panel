<?php

namespace Bale\GupaPanel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnownCrawler extends Model
{
    use HasUuids;

    protected $table = 'gupa_known_crawlers';

    protected $guarded = ['id'];

    protected $casts = [
        'verified_ip_ranges' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
