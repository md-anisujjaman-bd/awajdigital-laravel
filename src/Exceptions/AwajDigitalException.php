<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use RuntimeException;
use Throwable;

class AwajDigitalException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message = '',
        public readonly ?int $statusCode = null,
        public readonly ?string $errorCode = null,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }
}
