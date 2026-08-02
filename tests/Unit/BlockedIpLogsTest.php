<?php

use Bale\GupaPanel\Models\PanelRequestLog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

it('registers the blocked ip request logs route', function () {
    expect(Route::has('gupa-panel.blocked-ips.logs'))->toBeTrue();
});

it('resolves tenant relation on request logs', function () {
    $log = PanelRequestLog::create([
        'tenant_id' => (string) Str::uuid(),
        'tenant_log_id' => (string) Str::uuid(),
        'ip' => '10.0.0.1',
    ]);

    expect($log->tenant)->toBeNull();
});

it('builds a readable metadata summary', function () {
    $log = PanelRequestLog::create([
        'tenant_id' => 'tenant-123',
        'tenant_log_id' => (string) Str::uuid(),
        'ip' => '10.0.0.1',
        'metadata' => [
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'method' => 'POST',
            'path' => '/api/v1/login',
        ],
    ]);

    expect($log->metadataSummary())->toBeString();
    expect($log->metadataSummary())->toContain('Mozilla/5.0');
    expect($log->metadataSummary(10))->toBeString();
    expect($log->metadataSummary(10))->not->toBe($log->metadataSummary());
    expect($log->metadataSummary(10))->toEndWith('...');
});

it('returns an em dash when metadata is empty', function () {
    $log = PanelRequestLog::create([
        'tenant_id' => 'tenant-123',
        'tenant_log_id' => (string) Str::uuid(),
        'ip' => '10.0.0.2',
    ]);

    expect($log->metadataSummary())->toBe('—');
});
