<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Clients\Enums\ClientType;
use App\Support\BlindIndex;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'type', 'name', 'trade_name', 'document', 'phone', 'email',
    'notes', 'accepts_reminders', 'active',
])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Client $client): void {
            if ($client->isDirty('document')) {
                $client->document_index = BlindIndex::forDocument($client->document);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'type' => ClientType::class,
            'document' => 'encrypted',
            'notes' => 'encrypted',
            'accepts_reminders' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasOne<Appointment, $this>
     */
    public function nextAppointment(): HasOne
    {
        return $this->hasOne(Appointment::class)->ofMany(
            ['starts_at' => 'min'],
            fn (Builder $query) => $query->where('starts_at', '>=', now())->blocking(),
        );
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function recentAppointments(): HasMany
    {
        return $this->appointments()->where('starts_at', '<', now())->latest('starts_at')->limit(3);
    }

    public function isOrganization(): bool
    {
        return $this->type === ClientType::Organization;
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
