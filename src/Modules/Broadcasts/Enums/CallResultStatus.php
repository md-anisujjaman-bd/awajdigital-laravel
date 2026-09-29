<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Enums;

/**
 * Status of an individual recipient's call result.
 *
 * Note: The AwajDigital API returns 'notAnswered' (camelCase) in statusDistribution
 * and 'not_answered' (snake_case) in survey numbers; both are supported explicitly.
 */
enum CallResultStatus: string
{
    case Answered = 'answered';
    case NotAnsweredCamel = 'notAnswered';
    case NotAnsweredSnake = 'not_answered';
    case Rejected = 'rejected';
    case Busy = 'busy';
    case Failed = 'failed';
    case Unknown = 'unknown';
    case Pending = 'pending';

    public function isAnswered(): bool
    {
        return $this === self::Answered;
    }
}
