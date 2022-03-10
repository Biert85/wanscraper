<?php

namespace App\Providers;

use App\FeedIo\Adapter\HttpAdapter;
use FeedIo\FeedIo;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FeedIo::class, function($app): FeedIo {
            $logger = $app->make(LoggerInterface::class);
            $client = new HttpAdapter();

            return new FeedIo($client, $logger);
        });
    }
}
