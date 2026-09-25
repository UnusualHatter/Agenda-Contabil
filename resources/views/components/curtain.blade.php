@props(['title', 'subtitle' => null, 'phase' => 'leave'])

<div data-curtain role="status" aria-live="polite"
     {{ $attributes->merge(['class' => "curtain curtain--{$phase}"]) }}>
    <div class="curtain__content">
        <x-application-logo class="curtain__logo" />
        <p class="curtain__title">{{ $title }}</p>
        @if ($subtitle)
            <p class="curtain__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</div>
