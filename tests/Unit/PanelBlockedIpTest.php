<?php

use Bale\GupaPanel\Models\PanelBlockedIp;
use Illuminate\Support\Str;

it('creates a blocked ip entry with uuid', function () {
    $blocked = PanelBlockedIp::create([
        'ip' => '10.0.0.50',
        'reason' => 'malicious activity',
        'is_permanent' => true,
    ]);

    expect($blocked->id)->toBeString();
    expect(Str::isUuid($blocked->id))->toBeTrue();
    expect($blocked->ip)->toBe('10.0.0.50');
    expect($blocked->is_permanent)->toBeTrue();
});

it('defaults to non-permanent', function () {
    $blocked = PanelBlockedIp::create(['ip' => '10.0.0.51']);

    expect($blocked->fresh()->is_permanent)->toBeFalse();
});

it('preserves tenant timestamps instead of stamping sync time', function () {
    $tenantCreatedAt = now()->subDays(5)->subHours(3);
    $tenantUpdatedAt = now()->subDays(2);

    $blocked = PanelBlockedIp::create([
        'ip' => '10.0.0.52',
        'reason' => 'synced from tenant',
        'is_permanent' => false,
        'expires_at' => now()->addWeek(),
        'created_at' => $tenantCreatedAt,
        'updated_at' => $tenantUpdatedAt,
    ]);

    $fresh = $blocked->fresh();

    expect($fresh->created_at->timestamp)->toBe($tenantCreatedAt->timestamp);
    expect($fresh->updated_at->timestamp)->toBe($tenantUpdatedAt->timestamp);
});

it('applies notExpired scope correctly', function () {
    PanelBlockedIp::create(['ip' => '10.0.0.1', 'is_permanent' => true]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->addDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.3', 'expires_at' => now()->subDay()]);

    $notExpired = PanelBlockedIp::notExpired()->get();

    expect($notExpired)->toHaveCount(2);
    expect($notExpired->pluck('ip')->toArray())->toEqualCanonicalizing(['10.0.0.1', '10.0.0.2']);
});
