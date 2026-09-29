<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Enums;

enum AgentAccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';
}
