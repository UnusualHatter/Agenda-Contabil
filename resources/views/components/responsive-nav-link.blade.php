@props(['active' => false])

<a {{ $attributes->class([
    'press block w-full rounded-soft px-4 py-3 text-start text-base font-medium',
    'bg-primary-soft text-primary' => $active,
    'text-ink-muted hover:bg-sunken hover:text-ink' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
