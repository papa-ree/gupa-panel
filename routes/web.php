<?php

use Bale\GupaPanel\Livewire\Pages\Blacklist;
use Bale\GupaPanel\Livewire\Pages\BlockedIp;
use Bale\GupaPanel\Livewire\Pages\FalsePositiveReview;
use Bale\GupaPanel\Livewire\Pages\KnownCrawler;
use Bale\GupaPanel\Livewire\Pages\Overview;
use Bale\GupaPanel\Livewire\Pages\Sync;
use Bale\GupaPanel\Livewire\Pages\Whitelist;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix(config('gupa-panel.route_prefix', 'gupa-panel'))->as('gupa-panel.')->group(function () {

    Route::get('/overview', Overview::class)->name('overview');

    Route::middleware(['permission:gupa-panel.blacklist.read'])->group(function () {
        Route::get('/blacklist', Blacklist\Index::class)->name('blacklist');
        Route::get('/blacklist/create', Blacklist\Form::class)->name('blacklist.create');
        Route::get('/blacklist/{id}/edit', Blacklist\Form::class)->name('blacklist.edit');
    });

    Route::middleware(['permission:gupa-panel.whitelist.read'])->group(function () {
        Route::get('/whitelist', Whitelist\Index::class)->name('whitelist');
        Route::get('/whitelist/create', Whitelist\Form::class)->name('whitelist.create');
        Route::get('/whitelist/{id}/edit', Whitelist\Form::class)->name('whitelist.edit');
    });

    Route::middleware(['permission:gupa-panel.blocked-ip.read'])->group(function () {
        Route::get('/blocked-ips', BlockedIp\Index::class)->name('blocked-ips');
    });

    Route::middleware(['permission:gupa-panel.false-positive.review'])->group(function () {
        Route::get('/review-false-positive', FalsePositiveReview::class)->name('review-false-positive');
    });

    Route::middleware(['permission:gupa-panel.sync.manual'])->group(function () {
        Route::get('/sync', Sync\Index::class)->name('sync');
    });

    Route::middleware(['permission:gupa-panel.known-crawler.read'])->group(function () {
        Route::get('/known-crawlers', KnownCrawler\Index::class)->name('known-crawler');
        Route::get('/known-crawlers/create', KnownCrawler\Form::class)->name('known-crawler.create');
        Route::get('/known-crawlers/{id}/edit', KnownCrawler\Form::class)->name('known-crawler.edit');
    });
});
