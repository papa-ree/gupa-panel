<?php

use Bale\GupaPanel\Models\KnownCrawler;
use Bale\GupaPanel\Rules\KnownCrawlerRule;

it('passes when value is not a known crawler', function () {
    $rule = new KnownCrawlerRule;
    $failed = false;

    $rule->validate('user_agent_pattern', 'SomeRandomBot', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeFalse();
});

it('fails when value matches existing crawler name', function () {
    KnownCrawler::create([
        'name' => 'Googlebot',
        'provider' => 'Google',
        'user_agent_pattern' => 'Googlebot',
    ]);

    $rule = new KnownCrawlerRule;
    $failed = false;

    $rule->validate('user_agent_pattern', 'Googlebot', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('already registered');
});

it('fails when value matches existing crawler pattern', function () {
    KnownCrawler::create([
        'name' => 'Googlebot',
        'provider' => 'Google',
        'user_agent_pattern' => 'Googlebot',
    ]);

    $rule = new KnownCrawlerRule;
    $failed = false;

    $rule->validate('user_agent_pattern', 'Googlebot', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('already registered');
});

it('fails for non-string values', function () {
    $rule = new KnownCrawlerRule;
    $failed = false;

    $rule->validate('user_agent_pattern', 123, function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('must be a string');
});
