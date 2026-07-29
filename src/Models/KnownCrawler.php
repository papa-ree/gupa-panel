<?php

namespace Bale\GupaPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KnownCrawler extends Model
{
    protected $table = 'gupa_known_crawlers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'verified_ip_ranges' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
