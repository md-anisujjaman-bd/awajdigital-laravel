<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class AuthenticationException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Unauthenticated from AwajDigital API.',
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: 401,
            errorCode: $errorCode,
            context: [],
            previous: $previous,
        );
    }
}
