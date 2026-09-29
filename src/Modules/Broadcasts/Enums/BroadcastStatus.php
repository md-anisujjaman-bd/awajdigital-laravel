<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\Enums;

/**
 * Status of a broadcast.
 *
 * TODO(verify): Confirmed statuses from API docs are 'broadcasting' and 'completed'.
 * Other statuses like 'pending', 'processing', 'failed', 'ready' are included based on
 * related endpoints and lifecycle stages; verify against real API webhooks/polling.
 */
enum BroadcastStatus: string
{
    case Broadcasting = 'broadcasting';
    case Completed = 'completed';
    case Pending = 'pending';
    case Processing = 'processing';
    case Failed = 'failed';
    case Ready = 'ready';
}
