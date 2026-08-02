<?php

namespace Bale\GupaPanel\Models;

use Bale\Cms\Models\BaleList;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanelRequestLog extends Model
{
    use HasUuids;

    protected $table = 'gupa_panel_request_logs';

    protected $fillable = [
        'tenant_id',
        'tenant_log_id',
        'ip',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tenant_log_id' => 'string',
        'created_at' => 'datetime:d M Y H:i',
        'updated_at' => 'datetime:d M Y H:i',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function tenant()
    {
        return $this->belongsTo(BaleList::class, 'tenant_id');
    }

    public function metadataSummary(int $length = 140): string
    {
        if (empty($this->metadata)) {
            return '—';
        }

        return Str::limit(
            json_encode($this->metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $length
        );
    }
}
