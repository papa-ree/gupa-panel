<?php

use Bale\GupaPanel\Models\PanelBlacklist;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

it('creates a blacklist entry with uuid', function () {
    $blacklist = PanelBlacklist::create([
        'ip' => '192.168.1.1',
        'reason' => 'Test block',
    ]);

    expect($blacklist->id)->toBeString();
    expect(Str::isUuid($blacklist->id))->toBeTrue();
    expect($blacklist->ip)->toBe('192.168.1.1');
    expect($blacklist->reason)->toBe('Test block');
});

it('preserves tenant created_at instead of defaulting to now', function () {
    $tenantCreatedAt = now()->subDays(7)->subHours(4);

    $blacklist = PanelBlacklist::create([
        'ip' => '192.168.1.99',
        'reason' => 'synced from tenant',
        'created_at' => $tenantCreatedAt,
    ]);

    expect($blacklist->fresh()->created_at->timestamp)->toBe($tenantCreatedAt->timestamp);
});

it('enforces unique ip', function () {
    PanelBlacklist::create(['ip' => '10.0.0.1']);

    expect(fn () => PanelBlacklist::create(['ip' => '10.0.0.1']))
        ->toThrow(QueryException::class);
});

it('logs activity on creation', function () {
    $blacklist = PanelBlacklist::create(['ip' => '10.0.0.1', 'reason' => 'spam']);

    $activity = $blacklist->activities()->first();

    expect($activity)->not->toBeNull();
    expect($activity->event)->toBe('created');
    expect($activity->properties['logged_by'])->toBe('system');
    expect($activity->properties['ip_address'])->toBeNull();
});

it('logs activity on deletion', function () {
    $blacklist = PanelBlacklist::create(['ip' => '10.0.0.2']);
    $blacklist->delete();

    expect($blacklist->activities()->where('event', 'deleted')->exists())->toBeTrue();
});

it('does not log updated events', function () {
    $blacklist = PanelBlacklist::create(['ip' => '10.0.0.3', 'reason' => 'initial']);
    $blacklist->forceFill(['reason' => 'updated'])->save();

    expect($blacklist->activities()->count())->toBe(1);
});
