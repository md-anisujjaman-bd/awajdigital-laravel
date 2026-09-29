<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class ServerErrorException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Server error occurred on AwajDigital.',
        int $statusCode = 500,
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: $statusCode,
            errorCode: $errorCode,
            context: [],
            previous: $previous,
        );
    }
}
