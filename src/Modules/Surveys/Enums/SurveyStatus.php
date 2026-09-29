<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Surveys\Enums;

enum SurveyStatus: string
{
    case Ready = 'ready';
    case Surveying = 'surveying';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
