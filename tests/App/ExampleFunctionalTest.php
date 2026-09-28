<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App;

use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\AwajdigitalLaravelServiceProvider;
use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital as AwajDigitalFacade;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class ExampleFunctionalTest extends TestCase
{
    public function test_it_registers_the_service_provider(): void
    {
        // Act & Assert
        $this->assertInstanceOf(
            AwajdigitalLaravelServiceProvider::class,
            $this->app->getProvider(AwajdigitalLaravelServiceProvider::class)
        );
    }

    public function test_it_merges_the_default_configuration(): void
    {
        // Act & Assert
        $this->assertSame('https://api.awajdigital.com/api', config('awajdigital.base_url'));
    }

    public function test_it_resolves_the_manager_as_a_singleton(): void
    {
        // Arrange
        $firstInstance = $this->app->make(AwajDigital::class);

        // Act
        $secondInstance = $this->app->make(AwajDigital::class);

        // Assert
        $this->assertInstanceOf(AwajDigital::class, $firstInstance);
        $this->assertSame($firstInstance, $secondInstance);
    }

    public function test_the_facade_resolves_to_the_same_singleton_instance(): void
    {
        // Arrange
        $manager = $this->app->make(AwajDigital::class);

        // Act
        $facadeInstance = AwajDigitalFacade::getFacadeRoot();

        // Assert
        $this->assertSame($manager, $facadeInstance);
    }
}
