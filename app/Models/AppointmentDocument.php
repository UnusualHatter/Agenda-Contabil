<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['appointment_id', 'name', 'sort_order', 'received_at'])]
class AppointmentDocument extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'received_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function isReceived(): bool
    {
        return $this->received_at !== null;
    }
}
