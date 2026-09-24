<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Enums;

enum LocationType: string
{
    case OnCampus = 'on_campus';
    case External = 'external';
    case Remote = 'remote';

    public function label(): string
    {
        return __("appointments.location_type.{$this->value}");
    }
}
