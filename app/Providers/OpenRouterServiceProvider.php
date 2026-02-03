<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OpenRouterService;
use App\Services\OpenAIAdapter;

class OpenRouterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('openai.adapter', function () {
            return new OpenAIAdapter(new OpenRouterService());
        });
    }
}
