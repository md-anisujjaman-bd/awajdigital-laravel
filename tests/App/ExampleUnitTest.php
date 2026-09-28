<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App;

use MdAnisujjamanBd\AwajdigitalLaravel\AwajDigital;
use PHPUnit\Framework\TestCase;

final class ExampleUnitTest extends TestCase
{
    public function test_it_can_be_instantiated_without_a_container(): void
    {
        // Act
        $manager = new AwajDigital([
            'base_url' => 'https://api.awajdigital.com/api',
        ]);

        // Assert
        $this->assertSame('https://api.awajdigital.com/api', $manager->baseUrl());
    }
}
