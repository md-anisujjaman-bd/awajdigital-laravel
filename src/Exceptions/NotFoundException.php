<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class NotFoundException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Resource not found.',
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: 404,
            errorCode: $errorCode,
            context: [],
            previous: $previous,
        );
    }
}
