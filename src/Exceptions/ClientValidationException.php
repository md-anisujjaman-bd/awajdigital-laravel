<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class ClientValidationException extends AwajDigitalException
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: null,
            errorCode: 'client_validation_failed',
            context: ['errors' => $errors],
            previous: $previous,
        );
    }
}
