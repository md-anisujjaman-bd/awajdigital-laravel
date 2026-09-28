<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests;

use Illuminate\Foundation\Application;
use MdAnisujjamanBd\AwajdigitalLaravel\AwajdigitalLaravelServiceProvider;
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    #[\Override]
    protected function getPackageProviders($app): array
    {
        return [
            AwajdigitalLaravelServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     * @return array<string, class-string>
     */
    #[\Override]
    protected function getPackageAliases($app): array
    {
        return [
            'AwajDigital' => AwajDigital::class,
        ];
    }
}
