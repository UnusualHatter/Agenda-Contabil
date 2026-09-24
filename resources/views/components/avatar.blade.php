@props(['user'])

@php
    // Stable colour per person, so the same colleague is always the same tone.
    $tone = match ($user->id % 6) {
        0 => 'bg-primary-soft text-primary',
        1 => 'bg-clay-soft text-clay',
        2 => 'bg-moss-soft text-moss',
        3 => 'bg-plum-soft text-plum',
        4 => 'bg-ochre-soft text-ochre',
        5 => 'bg-rose-soft text-rose',
    };
    $initials = Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
@endphp

<span {{ $attributes->merge(['class' => "inline-flex size-6 shrink-0 items-center justify-center rounded-full text-[0.65rem] font-semibold {$tone}"]) }}
      title="{{ $user->name }}" aria-hidden="true">{{ $initials }}</span>
