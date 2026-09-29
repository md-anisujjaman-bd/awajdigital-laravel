<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class InsufficientBalanceException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Insufficient balance to perform this operation.',
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: 402,
            errorCode: $errorCode,
            context: [],
            previous: $previous,
        );
    }
}
