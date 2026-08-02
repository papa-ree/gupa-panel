<?php

use Bale\GupaPanel\Models\KnownCrawler;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Services\FalsePositiveDetector;
use Illuminate\Http\Request;

beforeEach(function () {
    KnownCrawler::create([
        'name' => 'Googlebot',
        'provider' => 'Google',
        'user_agent_pattern' => 'Googlebot',
        'verified_ip_ranges' => ['66.249.64.0/19', '66.249.80.0/20'],
        'is_active' => true,
    ]);

    KnownCrawler::create([
        'name' => 'Bingbot',
        'provider' => 'Microsoft',
        'user_agent_pattern' => 'bingbot',
        'verified_ip_ranges' => ['40.77.167.0/24', '65.55.210.0/24'],
        'is_active' => true,
    ]);

    KnownCrawler::create([
        'name' => 'UnverifiedBot',
        'provider' => 'Unknown',
        'user_agent_pattern' => 'UnverifiedBot',
        'verified_ip_ranges' => null,
        'is_active' => true,
    ]);
});

it('detects known crawler with verified ip as false positive', function () {
    $request = new Request;
    $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    $request->server->set('REMOTE_ADDR', '66.249.66.1');

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeRequest($request);

    expect($result['is_false_positive'])->toBeTrue();
    expect($result['matched_crawler'])->toBe('Googlebot');
    expect($result['confidence_score'])->toBe(100);
});

it('detects known crawler without verified ip as false positive with lower score', function () {
    $request = new Request;
    $request->headers->set('User-Agent', 'UnverifiedBot/1.0');
    $request->server->set('REMOTE_ADDR', '10.0.0.1');

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeRequest($request);

    expect($result['is_false_positive'])->toBeTrue();
    expect($result['matched_crawler'])->toBe('UnverifiedBot');
    expect($result['confidence_score'])->toBe(60);
});

it('detects bot-like signals for unknown user agents', function () {
    $request = new Request;
    $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; SomeBot/1.0)');
    $request->server->set('REMOTE_ADDR', '10.0.0.2');

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeRequest($request);

    expect($result['is_false_positive'])->toBeFalse();
    expect($result['matched_crawler'])->toBeNull();
    expect($result['confidence_score'])->toBe(35);
    expect($result['reasons'])->toContain("User-Agent contains 'bot'");
});

it('detects missing headers as suspicious but not false positive', function () {
    $request = new Request;
    $request->server->set('REMOTE_ADDR', '10.0.0.3');

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeRequest($request);

    expect($result['is_false_positive'])->toBeFalse();
    expect($result['confidence_score'])->toBe(30);
    expect($result['reasons'])->toContain('Empty User-Agent');
    expect($result['reasons'])->toContain('Missing Accept header');
});

it('does not flag normal browser request', function () {
    $request = new Request;
    $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');
    $request->headers->set('Accept', 'text/html,application/xhtml+xml');
    $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
    $request->server->set('REMOTE_ADDR', '10.0.0.4');

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeRequest($request);

    expect($result['is_false_positive'])->toBeFalse();
    expect($result['confidence_score'])->toBe(0);
    expect($result['matched_crawler'])->toBeNull();
});

it('analyzes a blocked ip from synced request log metadata', function () {
    $blockedIp = PanelBlockedIp::create([
        'ip' => '66.249.66.1',
        'reason' => 'suspected bot',
        'is_permanent' => false,
        'expires_at' => now()->addDay(),
    ]);

    PanelRequestLog::create([
        'tenant_id' => '019d2d68-4b7e-73de-8c4a-26833836221e',
        'tenant_log_id' => 'log-001',
        'ip' => '66.249.66.1',
        'metadata' => [
            'score' => 85,
            'path' => '/login',
            'method' => 'GET',
            'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'status_code' => 403,
            'header_accept' => 'text/html',
        ],
    ]);

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeBlockedIp($blockedIp);

    expect($result['is_false_positive'])->toBeTrue();
    expect($result['matched_crawler'])->toBe('Googlebot');
    expect($result['confidence_score'])->toBe(100);
});

it('uses metadata method when scoring blocked ip logs', function () {
    $blockedIp = PanelBlockedIp::create([
        'ip' => '10.0.0.9',
        'reason' => 'suspected bot',
        'is_permanent' => false,
        'expires_at' => now()->addDay(),
    ]);

    PanelRequestLog::create([
        'tenant_id' => '019d2d68-4b7e-73de-8c4a-26833836221e',
        'tenant_log_id' => 'log-002',
        'ip' => '10.0.0.9',
        'metadata' => [
            'method' => 'POST',
            'user_agent' => 'Mozilla/5.0',
            'status_code' => 403,
        ],
    ]);

    $detector = app(FalsePositiveDetector::class);
    $result = $detector->analyzeBlockedIp($blockedIp);

    expect($result['confidence_score'])->toBe(10);
    expect($result['reasons'])->toContain('Missing Accept header');
    expect($result['reasons'])->not->toContain('Missing Accept-Language header');
    expect($result['is_false_positive'])->toBeFalse();
});
