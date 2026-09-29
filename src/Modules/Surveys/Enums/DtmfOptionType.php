<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Enums;

enum DtmfOptionType: string
{
    case Voice = 'voice';
    case Transfer = 'transfer';
}
