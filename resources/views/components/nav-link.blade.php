@props(['active' => false])

<a wire:navigate {{ $attributes->class([
    'press relative z-10 inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition-colors duration-300',
    'text-primary' => $active,
    'text-ink-muted hover:text-ink' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
