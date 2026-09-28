<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\Integration;

use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class ExampleIntegrationTest extends TestCase
{
    #[\Override]
    protected function defineRoutes($router): void
    {
        $router->get('/awajdigital-integration-test', fn () => response()->json([
            'resolved' => $this->app->make(AwajDigital::class)::class,
        ]));
    }

    public function test_it_resolves_the_package_through_a_full_http_request(): void
    {
        // Act
        $testResponse = $this->get('/awajdigital-integration-test');

        // Assert
        $testResponse->assertOk();

        $testResponse->assertJson([
            'resolved' => AwajDigital::class,
        ]);
    }
}
