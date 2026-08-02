<?php

use Bale\GupaPanel\Models\PanelWhitelist;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

it('creates a whitelist entry with uuid', function () {
    $whitelist = PanelWhitelist::create([
        'ip' => '192.168.1.100',
        'reason' => 'Trusted internal',
    ]);

    expect($whitelist->id)->toBeString();
    expect(Str::isUuid($whitelist->id))->toBeTrue();
    expect($whitelist->ip)->toBe('192.168.1.100');
    expect($whitelist->reason)->toBe('Trusted internal');
});

it('preserves tenant created_at instead of defaulting to now', function () {
    $tenantCreatedAt = now()->subDays(7)->subHours(4);

    $whitelist = PanelWhitelist::create([
        'ip' => '192.168.1.199',
        'reason' => 'synced from tenant',
        'created_at' => $tenantCreatedAt,
    ]);

    expect($whitelist->fresh()->created_at->timestamp)->toBe($tenantCreatedAt->timestamp);
});

it('enforces unique ip on whitelist', function () {
    PanelWhitelist::create(['ip' => '10.0.0.1']);

    expect(fn () => PanelWhitelist::create(['ip' => '10.0.0.1']))
        ->toThrow(QueryException::class);
});

it('logs activity on creation', function () {
    $whitelist = PanelWhitelist::create(['ip' => '10.0.0.100']);

    $activity = $whitelist->activities()->first();

    expect($activity)->not->toBeNull();
    expect($activity->event)->toBe('created');
    expect($activity->properties['logged_by'])->toBe('system');
});

it('logs activity on deletion', function () {
    $whitelist = PanelWhitelist::create(['ip' => '10.0.0.101']);
    $whitelist->delete();

    expect($whitelist->activities()->where('event', 'deleted')->exists())->toBeTrue();
});

it('does not log updated events on whitelist', function () {
    $whitelist = PanelWhitelist::create(['ip' => '10.0.0.102', 'reason' => 'original']);
    $whitelist->forceFill(['reason' => 'changed'])->save();

    expect($whitelist->activities()->count())->toBe(1);
});
