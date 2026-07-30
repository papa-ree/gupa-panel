<?php

namespace Bale\GupaPanel\Models;

use Bale\Core\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PanelWhitelist extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'gupa_panel_whitelists';

    public $timestamps = false;

    protected $fillable = ['ip', 'reason'];

    protected $casts = [
        'created_at' => 'datetime:d M Y H:i',
    ];

    protected static $recordEvents = ['created', 'deleted'];

    public function getActivitylogOptions(): \Spatie\Activitylog\Support\LogOptions
    {
        return \Spatie\Activitylog\Support\LogOptions::defaults()
            ->logOnly(['ip', 'reason'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}