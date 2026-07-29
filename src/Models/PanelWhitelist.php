<?php

namespace Bale\GupaPanel\Models;

use Bale\Core\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanelWhitelist extends Model
{
    use LogsActivity;

    protected $table = 'gupa_panel_whitelists';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['ip', 'reason'];

    protected static $recordEvents = ['created', 'deleted'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\Support\LogOptions
    {
        return \Spatie\Activitylog\Support\LogOptions::defaults()
            ->logOnly(['ip', 'reason'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}
