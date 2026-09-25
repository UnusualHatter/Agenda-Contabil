<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return __("appointments.status.{$this->value}");
    }

    public function actionLabel(): string
    {
        return __("appointments.transition.{$this->value}");
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Scheduled => [self::Confirmed, self::InProgress, self::Cancelled, self::NoShow],
            self::Confirmed => [self::InProgress, self::Cancelled, self::NoShow],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled, self::NoShow => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * @return list<self>
     */
    public static function blocking(): array
    {
        return [self::Scheduled, self::Confirmed, self::InProgress];
    }

    public function canBeRescheduled(): bool
    {
        return $this === self::Scheduled || $this === self::Confirmed;
    }
}
