<?php

namespace Bale\GupaPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanelRequestLog extends Model
{
    protected $table = 'gupa_panel_request_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'ip',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
