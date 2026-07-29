<?php

namespace Bale\GupaPanel\Models;

use Bale\Core\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanelBlockedIp extends Model
{
    use LogsActivity;

    protected $table = 'gupa_panel_blocked_ips';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'ip',
        'reason',
        'is_permanent',
        'expires_at',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static $recordEvents = ['created', 'deleted'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
                ->orWhere('expires_at', '>', now())
                ->orWhereNull('expires_at');
        });
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\Support\LogOptions
    {
        return \Spatie\Activitylog\Support\LogOptions::defaults()
            ->logOnly(['ip', 'reason', 'is_permanent'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}
