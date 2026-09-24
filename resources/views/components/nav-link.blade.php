@props(['active' => false])

<a {{ $attributes->class([
    'press inline-flex items-center rounded-full px-4 py-2 text-sm font-medium',
    'nav-active bg-primary-soft text-primary' => $active,
    'text-ink-muted hover:text-ink' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
