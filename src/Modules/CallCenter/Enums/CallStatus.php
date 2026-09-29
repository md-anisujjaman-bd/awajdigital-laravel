<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Enums;

enum CallStatus: string
{
    case Initiated = 'initiated';
    case Answered = 'answered';
    case Failed = 'failed';
    case Busy = 'busy';
    case NoAnswer = 'no_answer';
    case Cancelled = 'cancelled';
}
