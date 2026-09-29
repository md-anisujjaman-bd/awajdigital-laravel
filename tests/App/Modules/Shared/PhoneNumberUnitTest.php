<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Shared;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhoneNumberUnitTest extends TestCase
{
    #[DataProvider('validNumbersProvider')]
    public function test_it_normalizes_valid_bangladeshi_numbers(string $input, string $expected): void
    {
        // Arrange & Act
        $phone = new PhoneNumber($input);

        // Assert
        $this->assertSame($expected, $phone->toString());
        $this->assertSame($expected, (string) $phone);
    }

    /**
     * @return \Generator<string, array{input: string, expected: string}>
     */
    public static function validNumbersProvider(): \Generator
    {
        yield 'local format' => [
            'input' => '01712345678',
            'expected' => '01712345678',
        ];

        yield 'with country code prefix' => [
            'input' => '8801812345678',
            'expected' => '01812345678',
        ];

        yield 'with plus country code' => [
            'input' => '+8801912345678',
            'expected' => '01912345678',
        ];

        yield 'with spaces and dashes' => [
            'input' => '+880 1312-345678',
            'expected' => '01312345678',
        ];
    }

    #[DataProvider('invalidNumbersProvider')]
    public function test_it_throws_client_validation_exception_for_invalid_numbers(string $input): void
    {
        // Arrange & Act & Assert
        $this->expectException(ClientValidationException::class);
        $this->expectExceptionMessage('Invalid Bangladeshi phone number format');

        new PhoneNumber($input);
    }

    /**
     * @return \Generator<string, array{input: string}>
     */
    public static function invalidNumbersProvider(): \Generator
    {
        yield 'invalid prefix 010' => [
            'input' => '01012345678',
        ];

        yield 'invalid prefix 012' => [
            'input' => '01212345678',
        ];

        yield 'too short' => [
            'input' => '0171234567',
        ];

        yield 'too long' => [
            'input' => '017123456789',
        ];

        yield 'non numeric characters' => [
            'input' => 'invalid-number',
        ];
    }

    public function test_it_masks_phone_number_correctly(): void
    {
        // Arrange
        $phone = new PhoneNumber('01712345678');

        // Act
        $masked = $phone->masked();

        // Assert
        $this->assertSame('017****5678', $masked);
    }
}
