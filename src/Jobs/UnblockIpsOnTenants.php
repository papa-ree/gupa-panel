<?php

namespace Bale\GupaPanel\Jobs;

use Bale\GupaPanel\Services\GupaSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Facades\Activity;

class UnblockIpsOnTenants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $ips
     */
    public function __construct(public array $ips, public bool $whitelist = false) {}

    public function handle(GupaSyncService $syncService): void
    {
        $result = $syncService->unblockIps($this->ips, $this->whitelist);

        Activity::inLog('gupa-panel-unblock')
            ->causedByAnonymous()
            ->withProperties($result)
            ->log($result['ok']
                ? 'Unblocked IPs on all tenants and panel'
                : 'Unblock aborted: '.($result['reason'] ?? 'unknown reason'));
    }
}
