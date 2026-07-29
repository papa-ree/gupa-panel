<?php

use Bale\GupaPanel\Rules\VerifiedIpRangeRule;

it('passes for valid ip', function () {
    $rule = new VerifiedIpRangeRule;
    $failed = false;

    $rule->validate('ip_range', '192.168.1.1', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeFalse();
});

it('passes for valid CIDR notation', function () {
    $rule = new VerifiedIpRangeRule;
    $failed = false;

    $rule->validate('ip_range', '66.249.64.0/19', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeFalse();
});

it('fails for invalid ip', function () {
    $rule = new VerifiedIpRangeRule;
    $failed = false;

    $rule->validate('ip_range', '999.999.999.999', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('not a valid IP');
});

it('fails for invalid CIDR prefix', function () {
    $rule = new VerifiedIpRangeRule;
    $failed = false;

    $rule->validate('ip_range', '10.0.0.0/99', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('invalid prefix length');
});

it('fails for non-string values', function () {
    $rule = new VerifiedIpRangeRule;
    $failed = false;

    $rule->validate('ip_range', 12345, function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toBeString();
    expect($failed)->toContain('must be a string');
});
