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

it('applies notExpired scope correctly', function () {
    PanelBlockedIp::create(['ip' => '10.0.0.1', 'is_permanent' => true]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->addDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.3', 'expires_at' => now()->subDay()]);

    $notExpired = PanelBlockedIp::notExpired()->get();

    expect($notExpired)->toHaveCount(2);
    expect($notExpired->pluck('ip')->toArray())->toEqualCanonicalizing(['10.0.0.1', '10.0.0.2']);
});

it('logs activity on creation', function () {
    $blocked = PanelBlockedIp::create(['ip' => '10.0.0.100', 'reason' => 'test']);

    $activity = $blocked->activities()->first();

    expect($activity)->not->toBeNull();
    expect($activity->event)->toBe('created');
    expect($activity->properties['logged_by'])->toBe('system');
});

it('logs activity on deletion', function () {
    $blocked = PanelBlockedIp::create(['ip' => '10.0.0.101']);
    $blocked->delete();

    expect($blocked->activities()->where('event', 'deleted')->exists())->toBeTrue();
});
