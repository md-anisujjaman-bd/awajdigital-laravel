<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Exceptions;

use Throwable;

class PermissionDeniedException extends AwajDigitalException
{
    public function __construct(
        string $message = 'Permission denied.',
        public readonly ?string $hint = null,
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        $fullMessage = $hint !== null && $hint !== '' ? sprintf('%s Hint: %s', $message, $hint) : $message;

        parent::__construct(
            message: $fullMessage,
            statusCode: 403,
            errorCode: $errorCode,
            context: ['hint' => $hint],
            previous: $previous,
        );
    }
}
