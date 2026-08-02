<?php

use Bale\GupaPanel\Models\PanelRequestLog;
use Illuminate\Support\Str;

it('creates a request log entry with uuid', function () {
    $log = PanelRequestLog::create([
        'tenant_id' => 'tenant-123',
        'ip' => '192.168.1.5',
        'metadata' => [
            'user_agent' => 'Mozilla/5.0',
            'header_accept' => 'text/html',
        ],
    ]);

    expect($log->id)->toBeString();
    expect(Str::isUuid($log->id))->toBeTrue();
    expect($log->tenant_id)->toBe('tenant-123');
    expect($log->ip)->toBe('192.168.1.5');
    expect($log->metadata)->toBeArray();
    expect($log->metadata['user_agent'])->toBe('Mozilla/5.0');
});

it('preserves tenant timestamps instead of stamping sync time', function () {
    $tenantCreatedAt = now()->subDays(3)->subHours(2);
    $tenantUpdatedAt = now()->subDay();

    $log = PanelRequestLog::create([
        'tenant_id' => 'tenant-123',
        'tenant_log_id' => 'tenant-log-1',
        'ip' => '192.168.1.6',
        'metadata' => [],
        'created_at' => $tenantCreatedAt,
        'updated_at' => $tenantUpdatedAt,
    ]);

    $fresh = $log->fresh();

    expect($fresh->created_at->timestamp)->toBe($tenantCreatedAt->timestamp);
    expect($fresh->updated_at->timestamp)->toBe($tenantUpdatedAt->timestamp);
});
