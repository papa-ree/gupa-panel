<?php

use Bale\GupaPanel\Livewire\Pages\BlockedIpIndex;
use Bale\GupaPanel\Livewire\Pages\BlacklistIndex;
use Bale\GupaPanel\Livewire\Pages\FalsePositiveReview;
use Bale\GupaPanel\Livewire\Pages\Overview;
use Bale\GupaPanel\Livewire\Pages\WhitelistIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix(config('gupa-panel.route_prefix', 'gupa-panel'))->as('gupa-panel.')->group(function () {

    Route::get('/overview', Overview::class)->name('overview');

    Route::middleware(['permission:gupa-panel.blacklist.read'])->group(function () {
        Route::get('/blacklist', BlacklistIndex::class)->name('blacklist');
    });

    Route::middleware(['permission:gupa-panel.whitelist.read'])->group(function () {
        Route::get('/whitelist', WhitelistIndex::class)->name('whitelist');
    });

    Route::middleware(['permission:gupa-panel.blocked-ip.read'])->group(function () {
        Route::get('/blocked-ips', BlockedIpIndex::class)->name('blocked-ips');
    });

    Route::middleware(['permission:gupa-panel.false-positive.review'])->group(function () {
        Route::get('/review-false-positive', FalsePositiveReview::class)->name('review-false-positive');
    });
});
