<?php

namespace App\Console\Commands;

use App\Services\Advertising\AdvertisingService;
use Illuminate\Console\Command;

class SyncAdCampaigns extends Command
{
    protected $signature = 'ads:sync-schedules';

    protected $description = 'Start and end direct ad campaigns from their schedule';

    public function handle(AdvertisingService $advertising): int
    {
        $advertising->syncSchedules();
        $this->info('Ad campaign schedules synced.');

        return self::SUCCESS;
    }
}
