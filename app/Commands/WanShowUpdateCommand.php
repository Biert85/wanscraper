<?php

namespace App\Commands;

use App\Service\WanScraperService;
use LaravelZero\Framework\Commands\Command;

class WanShowUpdateCommand extends Command
{
    protected $signature = 'wanshow:update';

    protected $description = 'Update WanShow RSS feed';

    public function handle(WanScraperService $wanScraperService): void
    {
        $this->info('Updating WAN Show RSS');
        $wanScraperService->getEpisodes();
    }
}
