<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class RateLimitedException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Rate limit exceeded.',
        public readonly ?int $retryAfterSeconds = null,
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: 429,
            errorCode: $errorCode,
            context: ['retry_after' => $retryAfterSeconds],
            previous: $previous,
        );
    }
}
