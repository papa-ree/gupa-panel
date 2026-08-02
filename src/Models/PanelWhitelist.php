<?php

namespace Bale\GupaPanel\Models;

use Bale\Core\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;

class PanelWhitelist extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'gupa_panel_whitelists';

    public $timestamps = false;

    protected $fillable = ['ip', 'reason', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime:d M Y H:i',
    ];

    protected static $recordEvents = ['created', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['ip', 'reason'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}
