<?php

namespace Bale\GupaPanel\Commands;

use Bale\GupaPanel\Services\GupaSyncService;
use Illuminate\Console\Command;

class BackfillLogMetadataCommand extends Command
{
    protected $signature = 'gupa-panel:backfill-log-metadata
        {--tenant= : Only backfill request logs for the given tenant id}';

    protected $description = 'Backfill request log metadata (score, path, method, user_agent, status_code) from tenant gupa_logs';

    public function handle(GupaSyncService $sync): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $this->info("Backfilling request log metadata for tenant {$tenantId}...");
        } else {
            $this->info('Backfilling request log metadata for all tenants...');
        }

        $updated = $sync->backfillLogMetadata($tenantId);

        $this->info("Done. {$updated} request log(s) updated.");

        return self::SUCCESS;
    }
}
