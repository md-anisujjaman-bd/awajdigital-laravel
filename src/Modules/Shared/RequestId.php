<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared;

use Illuminate\Support\Str;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use Stringable;

final readonly class RequestId implements Stringable
{
    public function __construct(
        public string $value,
    ) {
        $length = strlen($this->value);
        if ($length < 16 || $length > 64) {
            throw new ClientValidationException(
                sprintf('request_id must be between 16 and 64 characters (length: %d given).', $length)
            );
        }
    }

    public static function generate(): self
    {
        return new self((string) Str::ulid());
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
