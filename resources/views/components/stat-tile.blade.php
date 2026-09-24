@props(['label', 'value', 'tone' => 'primary'])

@php
    $classes = match ($tone) {
        'primary' => 'bg-primary-soft text-primary',
        'moss' => 'bg-moss-soft text-moss',
        'clay' => 'bg-clay-soft text-clay',
        'ochre' => 'bg-ochre-soft text-ochre',
        'plum' => 'bg-plum-soft text-plum',
    };
@endphp

<div {{ $attributes->merge(['class' => "rise-in rounded-[1.25rem] px-5 py-4 {$classes}"]) }}>
    <p class="font-display text-3xl sm:text-4xl [font-variation-settings:'SOFT'_100]">{{ $value }}</p>
    <p class="mt-1 text-sm font-medium">{{ $label }}</p>
</div>
