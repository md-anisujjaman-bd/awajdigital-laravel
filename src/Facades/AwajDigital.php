<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital as AwajDigitalManager;

/**
 * @see AwajDigitalManager
 */
final class AwajDigital extends Facade
{
    /**
     * Get the registered name of the component.
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return AwajDigitalManager::class;
    }
}
