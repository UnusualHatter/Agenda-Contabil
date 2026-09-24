<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Enums;

enum ActivityType: string
{
    case Created = 'created';
    case Rescheduled = 'rescheduled';
    case StatusChanged = 'status_changed';
    case ResponsibleChanged = 'responsible_changed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("appointments.activity.{$this->value}");
    }
}
