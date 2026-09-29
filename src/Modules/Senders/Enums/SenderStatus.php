<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\Enums;

enum SenderStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
