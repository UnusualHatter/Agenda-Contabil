@props(['variant' => 'primary', 'size' => 'md'])

@php
    $colors = match ($variant) {
        'primary' => 'bg-primary text-on-primary hover:bg-primary-hover',
        'secondary' => 'border border-line bg-surface text-ink hover:bg-sunken',
    };
    $dimensions = match ($size) {
        'md' => 'min-h-11 px-5 py-2.5',
        'sm' => 'min-h-10 px-4 py-2',
    };
@endphp

<a {{ $attributes->merge(['class' => "press inline-flex items-center justify-center gap-2 rounded-full text-sm font-semibold {$dimensions} {$colors}"]) }}>
    {{ $slot }}
</a>
