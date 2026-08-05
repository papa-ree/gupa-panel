<?php

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Services\GupaSyncService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('deletes expired blocked ips immediately when retention is zero', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 0);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->addDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.3', 'is_permanent' => true, 'expires_at' => now()->subDay()]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeFalse();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeTrue();
    expect(PanelBlockedIp::where('ip', '10.0.0.3')->exists())->toBeTrue();
});

it('retains expired blocked ips within the retention window', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 30);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDay()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->subDays(31)]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeTrue();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeFalse();
});

it('deletes expired blocked ips exactly at the retention boundary', function () {
    config()->set('gupa-panel.blocked_ip_retention_days', 7);

    PanelBlockedIp::create(['ip' => '10.0.0.1', 'expires_at' => now()->subDays(7)->subMinute()]);
    PanelBlockedIp::create(['ip' => '10.0.0.2', 'expires_at' => now()->subDays(7)->addMinute()]);

    $deleted = (new GupaSyncService)->cleanupExpiredBlockedIps();

    expect($deleted)->toBe(1);
    expect(PanelBlockedIp::where('ip', '10.0.0.1')->exists())->toBeFalse();
    expect(PanelBlockedIp::where('ip', '10.0.0.2')->exists())->toBeTrue();
});

it('builds request log metadata from tenant columns', function () {
    $log = (object) [
        'metadata' => json_encode(['header_accept' => 'text/html']),
        'score' => 85,
        'path' => '/login',
        'method' => 'POST',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
        'status_code' => 403,
    ];

    $metadata = (new GupaSyncService)->buildRequestLogMetadata($log);

    expect($metadata['score'])->toBe(85);
    expect($metadata['path'])->toBe('/login');
    expect($metadata['method'])->toBe('POST');
    expect($metadata['user_agent'])->toBe('Mozilla/5.0 (Windows NT 10.0)');
    expect($metadata['status_code'])->toBe(403);
    expect($metadata['header_accept'])->toBe('text/html');
});

it('keeps existing metadata values when tenant columns are absent', function () {
    $log = (object) [
        'metadata' => json_encode([
            'score' => 60,
            'path' => '/api/users',
            'method' => 'GET',
            'user_agent' => 'curl/8.0',
            'status_code' => 401,
        ]),
    ];

    $metadata = (new GupaSyncService)->buildRequestLogMetadata($log);

    expect($metadata['score'])->toBe(60);
    expect($metadata['path'])->toBe('/api/users');
    expect($metadata['method'])->toBe('GET');
    expect($metadata['user_agent'])->toBe('curl/8.0');
    expect($metadata['status_code'])->toBe(401);
});

it('prefers tenant columns over metadata values', function () {
    $log = (object) [
        'metadata' => json_encode(['score' => 10, 'path' => '/old']),
        'score' => 99,
        'path' => '/new',
    ];

    $metadata = (new GupaSyncService)->buildRequestLogMetadata($log);

    expect($metadata['score'])->toBe(99);
    expect($metadata['path'])->toBe('/new');
});

it('ignores missing columns and null metadata', function () {
    $log = (object) [
        'metadata' => null,
        'method' => 'GET',
    ];

    $metadata = (new GupaSyncService)->buildRequestLogMetadata($log);

    expect($metadata['method'])->toBe('GET');
    expect($metadata)->not->toHaveKey('score');
    expect($metadata)->not->toHaveKey('status_code');
});

function createTenantLogFixture(string $tenantId): string
{
    $conn = 'bale_'.str_replace('-', '_', $tenantId);

    Config::set("database.connections.{$conn}", [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    Schema::connection($conn)->create('gupa_logs', function ($table) {
        $table->string('id')->primary();
        $table->string('ip', 45);
        $table->json('metadata')->nullable();
        $table->integer('score')->nullable();
        $table->string('path')->nullable();
        $table->string('method')->nullable();
        $table->string('user_agent')->nullable();
        $table->integer('status_code')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
    });

    return $conn;
}

it('backfills metadata for request logs with empty metadata', function () {
    $tenantId = (string) Str::uuid();
    $conn = createTenantLogFixture($tenantId);
    $tenantLogId = (string) Str::uuid();

    DB::connection($conn)->table('gupa_logs')->insert([
        'id' => $tenantLogId,
        'ip' => '10.0.0.1',
        'metadata' => json_encode(['header_accept' => 'text/html']),
        'score' => 85,
        'path' => '/login',
        'method' => 'POST',
        'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)',
        'status_code' => 403,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $log = PanelRequestLog::create([
        'tenant_id' => $tenantId,
        'tenant_log_id' => $tenantLogId,
        'ip' => '10.0.0.1',
        'metadata' => [],
    ]);

    $service = new class extends GupaSyncService
    {
        public function backfillForTest(string $tenantId, string $connectionName): int
        {
            return $this->backfillTenantLogMetadata($tenantId, $connectionName);
        }
    };

    $updated = $service->backfillForTest($tenantId, $conn);

    expect($updated)->toBe(1);

    $fresh = $log->fresh();

    expect($fresh->metadata['score'])->toBe(85);
    expect($fresh->metadata['path'])->toBe('/login');
    expect($fresh->metadata['method'])->toBe('POST');
    expect($fresh->metadata['user_agent'])->toBe('Mozilla/5.0 (compatible; Googlebot/2.1)');
    expect($fresh->metadata['status_code'])->toBe(403);
    expect($fresh->metadata['header_accept'])->toBe('text/html');
});

it('does not backfill request logs that already have metadata', function () {
    $tenantId = (string) Str::uuid();
    $conn = createTenantLogFixture($tenantId);
    $tenantLogId = (string) Str::uuid();

    DB::connection($conn)->table('gupa_logs')->insert([
        'id' => $tenantLogId,
        'ip' => '10.0.0.2',
        'metadata' => json_encode([]),
        'score' => 90,
        'path' => '/admin',
        'method' => 'GET',
        'user_agent' => 'Googlebot',
        'status_code' => 200,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $log = PanelRequestLog::create([
        'tenant_id' => $tenantId,
        'tenant_log_id' => $tenantLogId,
        'ip' => '10.0.0.2',
        'metadata' => ['user_agent' => 'existing-agent'],
    ]);

    $service = new class extends GupaSyncService
    {
        public function backfillForTest(string $tenantId, string $connectionName): int
        {
            return $this->backfillTenantLogMetadata($tenantId, $connectionName);
        }
    };

    $updated = $service->backfillForTest($tenantId, $conn);

    expect($updated)->toBe(0);
    expect($log->fresh()->metadata['user_agent'])->toBe('existing-agent');
});

it('skips request logs whose tenant row no longer exists', function () {
    $tenantId = (string) Str::uuid();
    $conn = createTenantLogFixture($tenantId);

    $log = PanelRequestLog::create([
        'tenant_id' => $tenantId,
        'tenant_log_id' => (string) Str::uuid(),
        'ip' => '10.0.0.3',
        'metadata' => [],
    ]);

    $service = new class extends GupaSyncService
    {
        public function backfillForTest(string $tenantId, string $connectionName): int
        {
            return $this->backfillTenantLogMetadata($tenantId, $connectionName);
        }
    };

    $updated = $service->backfillForTest($tenantId, $conn);

    expect($updated)->toBe(0);
    expect($log->fresh()->metadata)->toBe([]);
});

it('deduplicates request logs when the same batch is synced more than once', function () {
    config()->set('gupa-panel.tenant_driver', 'sqlite');

    $tenant = BaleList::create([
        'organization_id' => (string) Str::uuid(),
        'name' => 'Tenant Sync Test',
        'slug' => 'tenant-sync-'.Str::random(6),
        'database_host' => 'localhost',
        'database_name' => ':memory:',
        'database_username' => 'root',
        'database_password' => 'secret',
        'is_active' => true,
    ]);

    $conn = 'bale_'.str_replace('-', '_', $tenant->id);

    Config::set("database.connections.{$conn}", [
        'driver' => 'sqlite',
        'host' => 'localhost',
        'port' => 3306,
        'database' => ':memory:',
        'username' => 'root',
        'password' => 'secret',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ]);

    Schema::connection($conn)->create('gupa_logs', function ($table) {
        $table->string('id')->primary();
        $table->string('ip', 45);
        $table->json('metadata')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
    });

    $logId = (string) Str::uuid();

    DB::connection($conn)->table('gupa_logs')->insert([
        'id' => $logId,
        'ip' => '10.0.0.77',
        'metadata' => json_encode(['user_agent' => 'Googlebot']),
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);

    $service = new GupaSyncService;

    $service->syncLogsFromTenant($tenant->id);
    $service->syncLogsFromTenant($tenant->id);

    expect(PanelRequestLog::where('tenant_id', $tenant->id)->count())->toBe(1);
    expect(PanelRequestLog::where('tenant_id', $tenant->id)->value('tenant_log_id'))->toBe($logId);
});

it('identifies duplicate tenant log constraint violations', function () {
    $service = new class extends GupaSyncService
    {
        public function isDuplicateForTest(QueryException $e): bool
        {
            return $this->isDuplicateLogEntry($e);
        }
    };

    $duplicate = new QueryException(
        'sqlite',
        'insert into "gupa_panel_request_logs" ...',
        [],
        new Exception("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'x' for key 'gupa_panel_request_logs.tenant_log_unique'"),
    );

    $other = new QueryException(
        'sqlite',
        'select * from "gupa_logs"',
        [],
        new Exception('SQLSTATE[HY000]: General error: 5 database is locked'),
    );

    expect($service->isDuplicateForTest($duplicate))->toBeTrue();
    expect($service->isDuplicateForTest($other))->toBeFalse();
});

it('registers the gupa-panel sync job on the scheduler when enabled', function () {
    $events = $this->app->make(Schedule::class)->events();

    $gupaEvents = array_values(array_filter(
        $events,
        fn ($event) => $event->description === 'gupa-panel:sync-tenants'
    ));

    expect($gupaEvents)->toHaveCount(1);
});
