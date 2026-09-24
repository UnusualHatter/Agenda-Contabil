@props(['status'])

@php
    // Colour always comes with the text label, never alone.
    $tone = match ($status) {
        App\Domain\Appointments\Enums\AppointmentStatus::Scheduled => 'bg-primary-soft text-primary',
        App\Domain\Appointments\Enums\AppointmentStatus::Confirmed => 'bg-moss-soft text-moss',
        App\Domain\Appointments\Enums\AppointmentStatus::InProgress => 'bg-ochre-soft text-ochre',
        App\Domain\Appointments\Enums\AppointmentStatus::Completed => 'bg-bark-soft text-bark',
        App\Domain\Appointments\Enums\AppointmentStatus::Cancelled => 'bg-danger-soft text-danger',
        App\Domain\Appointments\Enums\AppointmentStatus::NoShow => 'bg-clay-soft text-clay',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {$tone}"]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
