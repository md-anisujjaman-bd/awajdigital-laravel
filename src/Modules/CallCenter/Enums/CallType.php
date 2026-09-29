<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\CallCenter\Enums;

enum CallType: string
{
    case Internal = 'internal';
    case Outbound = 'outbound';
}
