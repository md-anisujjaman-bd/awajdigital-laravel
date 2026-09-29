<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class ApiException extends AwajDigitalException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $errorCode = null,
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: $statusCode,
            errorCode: $errorCode,
            context: $context,
            previous: $previous,
        );
    }
}
