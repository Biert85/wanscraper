<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use YoutubeDl\YoutubeDl;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(YoutubeDl::class, function ($app) {
            return (new YoutubeDl())
                ->setBinPath(config('download')['ytdl_bin'])
                ->setPythonPath(config('download')['python_bin']);
        });
    }
}
