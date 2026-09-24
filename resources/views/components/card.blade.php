@props(['padding' => 'normal'])

@php
    $spacing = match ($padding) {
        'normal' => 'p-6 sm:p-8',
        'tight' => 'p-2 sm:p-3',
    };
@endphp

<section {{ $attributes->merge(['class' => "rounded-[1.75rem] border border-line bg-surface {$spacing}"]) }}>
    {{ $slot }}
</section>
