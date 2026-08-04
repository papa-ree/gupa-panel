<?php

namespace Bale\GupaPanel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstallGupaPanelCommand extends Command
{
    protected $signature = 'gupa-panel:install';

    protected $description = 'Install GupaPanel: config, migrations, roles & permissions';

    public function handle(): int
    {
        $this->info('Starting GupaPanel installation...');

        $choice = $this->choice(
            'What would you like to install?',
            ['All', 'Role Permission Only', 'Migration Only'],
            0
        );

        match ($choice) {
            'All' => $this->installAll(),
            'Role Permission Only' => $this->seedPermissions(),
            'Migration Only' => $this->publishAndRunMigrations(),
        };

        $this->info('GupaPanel installation completed successfully!');

        return self::SUCCESS;
    }

    protected function installAll(): void
    {
        $this->publishConfig();
        $this->publishAndRunMigrations();
        $this->seedPermissions();
    }

    protected function publishConfig(): void
    {
        $this->info('Publishing config...');

        $this->call('vendor:publish', [
            '--tag' => 'gupa-panel:config',
            '--force' => true,
        ]);
    }

    protected function publishAndRunMigrations(): void
    {
        $this->info('Publishing and running migrations...');

        $sourcePath = __DIR__.'/../../database/migrations/';
        $targetPath = database_path('migrations/');

        if (! is_dir($sourcePath)) {
            $this->warn('No migration stubs found.');

            return;
        }

        $published = 0;

        foreach (glob($sourcePath.'*.php.stub') as $stubFile) {
            $filename = basename($stubFile);
            $migrationName = str_replace('.php.stub', '.php', $filename);

            $existing = glob($targetPath.'*'.$migrationName);

            if (! empty($existing)) {
                $this->warn("  Migration {$migrationName} already exists, skipping.");

                continue;
            }

            $newFilename = $migrationName;
            $newFile = $targetPath.$newFilename;

            File::copy($stubFile, $newFile);
            $this->info("  Published: {$newFilename}");
            $published++;
        }

        if ($published > 0) {
            $this->call('migrate');
        }

        $this->call('migrate');
    }

    protected function seedPermissions(): void
    {
        $this->info('Seeding permissions...');

        $permissions = [
            'gupa-panel.overview',
            'gupa-panel.blacklist.read',
            'gupa-panel.blacklist.create',
            'gupa-panel.blacklist.delete',
            'gupa-panel.whitelist.read',
            'gupa-panel.whitelist.create',
            'gupa-panel.whitelist.delete',
            'gupa-panel.blocked-ip.read',
            'gupa-panel.blocked-ip.unblock',
            'gupa-panel.false-positive.review',
            'gupa-panel.false-positive.whitelist',
            'gupa-panel.sync.manual',
            'gupa-panel.known-crawler.read',
            'gupa-panel.known-crawler.create',
            'gupa-panel.known-crawler.update',
            'gupa-panel.known-crawler.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        $this->info('Permissions seeded and updated.');

        $rootRole = Role::where('name', 'root')->first();
        if ($rootRole) {
            $this->info('Force syncing ALL permissions to root role...');
            $rootRole->syncPermissions(Permission::where('name', '!=', 'guest.sidebar')->get());

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->info('Permissions force synced and cache cleared for root role.');
        }

        $this->call('db:seed', [
            '--class' => 'Bale\GupaPanel\Database\Seeders\KnownCrawlerSeeder',
        ]);
    }
}
