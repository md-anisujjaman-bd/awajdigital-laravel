<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class ConflictException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Duplicate request_id within the idempotency window.',
        public readonly ?string $requestId = null,
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: 409,
            errorCode: $errorCode,
            context: ['request_id' => $requestId],
            previous: $previous,
        );
    }
}
