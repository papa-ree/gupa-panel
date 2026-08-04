<?php

use Bale\GupaPanel\Livewire\Pages\FalsePositiveReview;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Livewire;

it('whitelists a selected false positive and unblocks the ip', function () {
    $blocked = PanelBlockedIp::create([
        'ip' => '66.249.66.1',
        'reason' => 'suspected bot',
        'is_permanent' => true,
    ]);

    Livewire::test(FalsePositiveReview::class)
        ->set('selected', [$blocked->id])
        ->call('whitelistSelected');

    expect(PanelBlockedIp::find($blocked->id))->toBeNull();
    expect(PanelWhitelist::where('ip', '66.249.66.1')->exists())->toBeTrue();
    expect(session('_flash.new'))->toContain('message');
});

it('does not error when the ip is already whitelisted', function () {
    $ip = '66.249.66.2';

    PanelWhitelist::create(['ip' => $ip, 'reason' => 'existing whitelist']);

    $blocked = PanelBlockedIp::create([
        'ip' => $ip,
        'reason' => 'suspected bot',
        'is_permanent' => true,
    ]);

    Livewire::test(FalsePositiveReview::class)
        ->set('selected', [$blocked->id])
        ->call('whitelistSelected');

    expect(PanelBlockedIp::find($blocked->id))->toBeNull();
    expect(PanelWhitelist::where('ip', $ip)->count())->toBe(1);
    expect(PanelWhitelist::where('ip', $ip)->value('reason'))->toBe('existing whitelist');
    expect(session('_flash.new'))->toContain('message');
});

it('unblocks selected ips without whitelisting', function () {
    $blocked = PanelBlockedIp::create([
        'ip' => '66.249.66.3',
        'reason' => 'suspected bot',
        'is_permanent' => true,
    ]);

    Livewire::test(FalsePositiveReview::class)
        ->set('selected', [$blocked->id])
        ->call('unblockOnly');

    expect(PanelBlockedIp::find($blocked->id))->toBeNull();
    expect(PanelWhitelist::where('ip', '66.249.66.3')->exists())->toBeFalse();
});
