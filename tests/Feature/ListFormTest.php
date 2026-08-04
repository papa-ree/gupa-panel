<?php

use Bale\GupaPanel\Livewire\Pages\Blacklist;
use Bale\GupaPanel\Livewire\Pages\Whitelist;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Livewire;

it('rejects creating a whitelist entry with an existing ip', function () {
    PanelWhitelist::create(['ip' => '10.0.0.1', 'reason' => 'existing']);

    Livewire::test(Whitelist\Form::class)
        ->set('ip', '10.0.0.1')
        ->set('reason', 'duplicate')
        ->call('save')
        ->assertHasErrors('ip');

    expect(PanelWhitelist::where('ip', '10.0.0.1')->count())->toBe(1);
});

it('allows editing a whitelist entry without conflicting with itself', function () {
    $whitelist = PanelWhitelist::create(['ip' => '10.0.0.2', 'reason' => 'keep']);

    Livewire::test(Whitelist\Form::class, ['id' => $whitelist->id])
        ->set('reason', 'updated reason')
        ->call('save')
        ->assertHasNoErrors();

    expect(PanelWhitelist::find($whitelist->id)->reason)->toBe('updated reason');
});

it('rejects creating a blacklist entry with an existing ip', function () {
    PanelBlacklist::create(['ip' => '10.0.0.3', 'reason' => 'existing']);

    Livewire::test(Blacklist\Form::class)
        ->set('ip', '10.0.0.3')
        ->set('reason', 'duplicate')
        ->call('save')
        ->assertHasErrors('ip');

    expect(PanelBlacklist::where('ip', '10.0.0.3')->count())->toBe(1);
});

it('allows editing a blacklist entry without conflicting with itself', function () {
    $blacklist = PanelBlacklist::create(['ip' => '10.0.0.4', 'reason' => 'keep']);

    Livewire::test(Blacklist\Form::class, ['id' => $blacklist->id])
        ->set('reason', 'updated reason')
        ->call('save')
        ->assertHasNoErrors();

    expect(PanelBlacklist::find($blacklist->id)->reason)->toBe('updated reason');
});
