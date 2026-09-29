<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class ValidationFailedException extends AwajDigitalException
{
    /**
     * @param  array<int|string, mixed>  $errors
     */
    public function __construct(
        string $message = 'Validation failed.',
        int $statusCode = 422,
        public readonly array $errors = [],
        public readonly ?string $duplicatedNumber = null,
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            statusCode: $statusCode,
            errorCode: $errorCode,
            context: [
                'errors' => $errors,
                'duplicated_number' => $duplicatedNumber,
            ],
            previous: $previous,
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getDuplicatedNumber(): ?string
    {
        return $this->duplicatedNumber;
    }
}
