<?php

use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Services\GupaSyncService;

it('deletes expired blocked ips immediately when retention is zero', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 0);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->addDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.3', 'is_permanent' => true, 'expires_at' => now()->subDay()]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeFalse();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeTrue();
    expect(PanelBlockedIp::where('ip', '10.0.0.3')->exists())->toBeTrue();
});

it('retains expired blocked ips within the retention window', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 30);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->subDays(31)]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeTrue();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeFalse();
});

it('deletes expired blocked ips exactly at the retention boundary', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 7);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDays(7)->subMinute()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->subDays(7)->addMinute()]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeFalse();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeTrue();
});
