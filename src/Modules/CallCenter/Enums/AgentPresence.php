<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Enums;

enum AgentPresence: string
{
    case Offline = 'offline';
    case Online = 'online';
    case OnCall = 'on_call';
    case OnBreak = 'on_break';
    case WrapUp = 'wrap_up';
}
