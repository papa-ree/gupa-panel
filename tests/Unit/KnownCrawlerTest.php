<?php

use Bale\GupaPanel\Models\KnownCrawler;
use Illuminate\Support\Str;

it('creates a known crawler with uuid', function () {
    $crawler = KnownCrawler::create([
        'name' => 'Googlebot',
        'provider' => 'Google',
        'user_agent_pattern' => 'Googlebot',
        'verified_ip_ranges' => ['66.249.64.0/19', '66.249.80.0/20'],
        'is_active' => true,
    ]);

    expect($crawler->id)->toBeString();
    expect(Str::isUuid($crawler->id))->toBeTrue();
    expect($crawler->name)->toBe('Googlebot');
    expect($crawler->provider)->toBe('Google');
    expect($crawler->user_agent_pattern)->toBe('Googlebot');
    expect($crawler->is_active)->toBeTrue();
});

it('casts verified_ip_ranges to array', function () {
    $ranges = ['66.249.64.0/19', '66.249.80.0/20'];

    $crawler = KnownCrawler::create([
        'name' => 'Googlebot2',
        'provider' => 'Google',
        'user_agent_pattern' => 'Googlebot/2.1',
        'verified_ip_ranges' => $ranges,
    ]);

    expect($crawler->verified_ip_ranges)->toBeArray();
    expect($crawler->verified_ip_ranges)->toHaveCount(2);
    expect($crawler->verified_ip_ranges[0])->toBe('66.249.64.0/19');
});

it('allows null verified_ip_ranges', function () {
    $crawler = KnownCrawler::create([
        'name' => 'Bingbot',
        'provider' => 'Microsoft',
        'user_agent_pattern' => 'bingbot',
    ]);

    expect($crawler->verified_ip_ranges)->toBeNull();
});

it('scopes active crawlers', function () {
    KnownCrawler::create(['name' => 'Active', 'provider' => 'A', 'user_agent_pattern' => 'active', 'is_active' => true]);
    KnownCrawler::create(['name' => 'Inactive', 'provider' => 'B', 'user_agent_pattern' => 'inactive', 'is_active' => false]);

    $active = KnownCrawler::active()->get();

    expect($active)->toHaveCount(1);
    expect($active->first()->name)->toBe('Active');
});
