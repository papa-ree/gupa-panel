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
        'ip',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}