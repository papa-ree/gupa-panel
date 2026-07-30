<?php

namespace Bale\GupaPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PanelRequestLog extends Model
{
    use HasUuids;

    protected $table = 'gupa_panel_request_logs';

    protected $fillable = [
        'tenant_id',
        'tenant_log_id',
        'ip',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tenant_log_id' => 'string',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}