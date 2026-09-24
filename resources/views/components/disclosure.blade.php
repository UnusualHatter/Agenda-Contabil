@props(['id', 'label'])

{{--
    Summary row that expands in place. The summary is a real button (keyboard
    and screen readers get aria-expanded), and the closed panel is inert so
    its links are skipped by Tab until it opens.
--}}
<div x-data="{ open: false }" x-on:keydown.escape="open = false"
     {{ $attributes->merge(['class' => 'rounded-soft transition-colors duration-200']) }}
     x-bind:class="open && 'bg-sunken/70'">
    <button type="button"
            class="press group flex w-full items-start gap-3 rounded-soft px-3 py-3 text-start hover:bg-sunken sm:gap-4"
            x-on:click="open = ! open"
            x-bind:aria-expanded="open.toString()"
            aria-controls="{{ $id }}">
        <span class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4">{{ $summary }}</span>
        <svg class="mt-1 size-5 shrink-0 text-ink-muted transition-transform duration-300 ease-out"
             x-bind:class="open && 'rotate-180 text-primary'"
             viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div id="{{ $id }}" role="region" aria-label="{{ $label }}"
         class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-out"
         x-bind:class="open && 'grid-rows-[1fr]'"
         x-bind:inert="! open">
        <div class="overflow-hidden">
            <div class="px-3 pt-1 pb-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
