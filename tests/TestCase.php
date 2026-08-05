<?php

namespace Bale\GupaPanel\Tests;

use Bale\Cms\CmsServiceProvider;
use Bale\Core\CoreServiceProvider;
use Bale\GupaPanel\GupaPanelServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            GupaPanelServiceProvider::class,
            CoreServiceProvider::class,
            CmsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('gupa-panel.enabled', true);
        $app['config']->set('gupa-panel.sync_interval', 1);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('activitylog.enabled', true);
        $app['config']->set('activitylog.default_auth_driver', 'web');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createGupaPanelTables();
        $this->createActivityLogTable();
        $this->createBaleListTable();
    }

    protected function createGupaPanelTables(): void
    {
        Schema::create('gupa_known_crawlers', function ($table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('provider');
            $table->string('user_agent_pattern');
            $table->json('verified_ip_ranges')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gupa_panel_blacklists', function ($table) {
            $table->uuid('id')->primary();
            $table->string('ip', 45)->unique();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('gupa_panel_whitelists', function ($table) {
            $table->uuid('id')->primary();
            $table->string('ip', 45)->unique();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('gupa_panel_blocked_ips', function ($table) {
            $table->uuid('id')->primary();
            $table->string('ip', 45);
            $table->string('reason')->default('manual block');
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('ip');
            $table->index('is_permanent');
            $table->index('expires_at');
        });

        Schema::create('gupa_panel_request_logs', function ($table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->uuid('tenant_log_id')->nullable();
            $table->string('ip', 45);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
            $table->index('ip');
        });
    }

    protected function createActivityLogTable(): void
    {
        Schema::create('activity_log', function ($table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->json('attribute_changes')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    protected function createBaleListTable(): void
    {
        Schema::create('bale_lists', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('database_host');
            $table->string('database_name')->unique();
            $table->text('database_username');
            $table->string('database_password');
            $table->string('storage_prefix')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
        });
    }
}

class DisabledGupaPanelTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('gupa-panel.enabled', false);
    }
}
