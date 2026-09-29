<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use Stringable;

final readonly class PhoneNumber implements Stringable
{
    public string $value;

    public function __construct(string $number)
    {
        $cleaned = preg_replace('/[^\d+]/', '', trim($number)) ?? '';

        if (str_starts_with($cleaned, '+880')) {
            $cleaned = substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '880')) {
            $cleaned = substr($cleaned, 2);
        }

        if (! preg_match('/^01[3-9]\d{8}$/', $cleaned)) {
            throw new ClientValidationException(
                'Invalid Bangladeshi phone number format. Recipient numbers must be 11 digits starting with 01[3-9].'
            );
        }

        $this->value = $cleaned;
    }

    public static function from(string $number): self
    {
        return new self($number);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function masked(): string
    {
        if (strlen($this->value) === 11) {
            return substr($this->value, 0, 3).'****'.substr($this->value, 7);
        }

        return '***';
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
