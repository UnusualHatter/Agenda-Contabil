<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Appointments\Enums\ActivityType;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Support\DisplayTimezone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Fillable(['appointment_id', 'user_id', 'event_type', 'old_values', 'new_values'])]
class AppointmentActivity extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'event_type' => ActivityType::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changeSummary(Collection $userNames): ?string
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return match ($this->event_type) {
            ActivityType::Rescheduled => self::localTime($old['starts_at']).' → '.self::localTime($new['starts_at']),
            ActivityType::StatusChanged, ActivityType::Cancelled => AppointmentStatus::from($old['status'])->label()
                .' → '.AppointmentStatus::from($new['status'])->label(),
            ActivityType::ResponsibleChanged => ($userNames[$old['responsible_user_id']] ?? '—')
                .' → '.($userNames[$new['responsible_user_id']] ?? '—'),
            ActivityType::Created => null,
        };
    }

    private static function localTime(string $iso): string
    {
        return DisplayTimezone::toLocal(Carbon::parse($iso))->format('d/m H:i');
    }
}
