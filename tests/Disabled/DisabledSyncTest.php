<?php

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\GupaSyncService;
use Bale\GupaPanel\Tests\DisabledGupaPanelTestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(DisabledGupaPanelTestCase::class);

$makeTenant = function () {
    return BaleList::create([
        'id' => (string) Str::uuid(),
        'organization_id' => (string) Str::uuid(),
        'name' => 'Disabled Tenant',
        'slug' => 'disabled-tenant-'.Str::random(6),
        'database_host' => 'localhost',
        'database_name' => 'tenant_db_'.Str::random(6),
        'database_username' => 'root',
        'database_password' => 'secret',
        'is_active' => true,
    ]);
};

it('does not register the gupa-panel sync job on the scheduler when disabled', function () {
    $events = $this->app->make(Schedule::class)->events();

    $gupaEvents = array_values(array_filter(
        $events,
        fn ($event) => $event->description === 'gupa-panel:sync-tenants'
    ));

    expect($gupaEvents)->toBeEmpty();
});

it('does not dispatch any sync jobs when disabled', function () use ($makeTenant) {
    $makeTenant();

    Queue::fake();

    (new GupaSyncService)->syncAll();

    Queue::assertNothingPushed();
    expect(PanelBlacklist::count())->toBe(0);
    expect(PanelWhitelist::count())->toBe(0);
    expect(PanelRequestLog::count())->toBe(0);
});

it('does not sync master data to a tenant when disabled', function () use ($makeTenant) {
    $tenant = $makeTenant();

    (new GupaSyncService)->syncMasterDataToTenant($tenant->id);

    expect(PanelBlacklist::count())->toBe(0);
    expect(PanelWhitelist::count())->toBe(0);
});

it('does not pull request logs from a tenant when disabled', function () use ($makeTenant) {
    $tenant = $makeTenant();

    (new GupaSyncService)->syncLogsFromTenant($tenant->id);

    expect(PanelRequestLog::count())->toBe(0);
});

it('skips backfilling request log metadata when disabled', function () use ($makeTenant) {
    $tenant = $makeTenant();

    $updated = (new GupaSyncService)->backfillLogMetadata($tenant->id);

    expect($updated)->toBe(0);
});
