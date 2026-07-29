<?php

namespace Bale\GupaPanel;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use Bale\GupaPanel\Commands\InstallGupaPanelCommand;
use Bale\GupaPanel\Jobs\SyncAllToTenants;
use Symfony\Component\Finder\Finder;

class GupaPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gupa-panel.php', 'gupa-panel');

        $this->registerCommands();
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->registerViews();
        $this->offerPublishing();
        $this->registerLivewireComponents();
        $this->registerSchedules();
    }

    protected function registerCommands(): void
    {
        $this->commands([
            InstallGupaPanelCommand::class,
        ]);
    }

    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gupa-panel');
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/gupa-panel.php' => config_path('gupa-panel.php'),
        ], 'gupa-panel:config');

        $this->publishes($this->getMigrations(), 'gupa-panel:migrations');
    }

    protected function getMigrations(): array
    {
        $migrations = [];
        $sourcePath = __DIR__.'/../database/migrations/';

        if (! is_dir($sourcePath)) {
            return $migrations;
        }

        foreach (glob($sourcePath.'*.{php,stub}', GLOB_BRACE) as $file) {
            $filename = basename($file);
            $targetFile = $this->getMigrationFileName($filename);
            $migrations[$file] = $targetFile;
        }

        return $migrations;
    }

    protected function getMigrationFileName(string $filename): string
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = str_replace('.php.stub', '.php', $filename);

        return database_path('migrations/'.$timestamp.'_'.$migrationName);
    }

    protected function registerLivewireComponents(): void
    {
        $namespace = 'Bale\\GupaPanel\\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder;
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePathname = $file->getRelativePathname();
            $nsPath = str_replace(['/', '\\'], '\\', $relativePathname);
            $class = $namespace.'\\'.Str::beforeLast($nsPath, '.php');

            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, LivewireComponent::class)) {
                continue;
            }

            $withoutExt = Str::replaceLast('.php', '', $relativePathname);
            $segments = preg_split('#[\\/\\\\]#', $withoutExt);
            $kebab = array_map(fn ($s) => Str::kebab($s), $segments);

            $alias = 'gupa-panel.'.implode('.', $kebab);

            Livewire::component($alias, $class);
        }
    }

    protected function registerSchedules(): void
    {
        $interval = (int) Config::get('gupa-panel.sync_interval', 1);

        $this->app->booted(function () use ($interval) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            $schedule->job(new SyncAllToTenants)
                ->cron("*/{$interval} * * * *")
                ->withoutOverlapping()
                ->name('gupa-panel:sync-tenants');
        });
    }
}
