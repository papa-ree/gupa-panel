<?php

namespace Bale\GupaPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PanelBlockedIp extends Model
{
    use HasUuids;

    protected $table = 'gupa_panel_blocked_ips';

    protected $fillable = [
        'ip',
        'reason',
        'is_permanent',
        'expires_at',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'expires_at' => 'datetime:d M Y H:i',
        'created_at' => 'datetime:d M Y H:i',
        'updated_at' => 'datetime:d M Y H:i',
    ];

    protected static $recordEvents = ['created', 'deleted'];

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
                ->orWhere('expires_at', '>', now())
                ->orWhereNull('expires_at');
        });
    }
}