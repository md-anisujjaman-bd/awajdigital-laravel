<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel;

use Illuminate\Support\ServiceProvider;

class AwajdigitalLaravelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/awajdigital.php', 'awajdigital');

        $this->app->singleton(AwajDigital::class, function (): AwajDigital {
            /** @var array<string, mixed> $config */
            $config = config('awajdigital', []);

            return new AwajDigital($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/awajdigital.php' => config_path('awajdigital.php'),
        ], 'awajdigital-config');
    }
}
