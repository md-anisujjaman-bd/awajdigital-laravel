<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Shared;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\RequestId;
use PHPUnit\Framework\TestCase;

final class RequestIdUnitTest extends TestCase
{
    public function test_it_generates_valid_ulid_request_id(): void
    {
        // Arrange & Act
        $requestId = RequestId::generate();

        // Assert
        $this->assertSame(26, strlen($requestId->value));
        $this->assertSame($requestId->value, (string) $requestId);
        $this->assertSame($requestId->value, $requestId->toString());
    }

    public function test_it_accepts_valid_string_request_id(): void
    {
        // Arrange
        $validString = '01J8TEST000000000000000000';

        // Act
        $requestId = RequestId::from($validString);

        // Assert
        $this->assertSame($validString, $requestId->value);
    }

    public function test_it_throws_client_validation_exception_for_short_request_id(): void
    {
        // Arrange
        $short = 'too-short-1234';

        // Act & Assert
        $this->expectException(ClientValidationException::class);
        $this->expectExceptionMessage('request_id must be between 16 and 64 characters');

        RequestId::from($short);
    }

    public function test_it_throws_client_validation_exception_for_long_request_id(): void
    {
        // Arrange
        $long = str_repeat('a', 65);

        // Act & Assert
        $this->expectException(ClientValidationException::class);
        $this->expectExceptionMessage('request_id must be between 16 and 64 characters');

        RequestId::from($long);
    }
}
