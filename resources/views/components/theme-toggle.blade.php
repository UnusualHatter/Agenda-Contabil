@php
    $options = [
        'light' => ['label' => __('theme.light'), 'icon' => 'M12 4V2m0 20v-2m8-8h2M2 12h2m13.66-5.66 1.41-1.41M4.93 19.07l1.41-1.41m0-11.32L4.93 4.93m14.14 14.14-1.41-1.41M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z'],
        'dark' => ['label' => __('theme.dark'), 'icon' => 'M20.35 15.35A8.5 8.5 0 0 1 8.65 3.65a8.5 8.5 0 1 0 11.7 11.7Z'],
        'system' => ['label' => __('theme.system'), 'icon' => 'M4 5h16v11H4zM9 20h6m-3-4v4'],
    ];
@endphp

<div x-data="themeToggle" role="radiogroup" aria-label="{{ __('theme.label') }}"
     x-on:theme-chosen.window="choice = $event.detail"
     {{ $attributes->merge(['class' => 'relative inline-flex rounded-full border border-line bg-surface p-1']) }}>
    <span class="theme-toggle__thumb absolute start-1 top-1 size-9 rounded-full bg-primary-soft"
          x-bind:style="{ transform: thumbOffset }" aria-hidden="true"></span>

    @foreach ($options as $value => $option)
        <button type="button" role="radio" title="{{ $option['label'] }}"
                x-on:click="choose('{{ $value }}', $el); $dispatch('theme-chosen', '{{ $value }}')"
                x-bind:aria-checked="(choice === '{{ $value }}').toString()"
                x-bind:class="choice === '{{ $value }}' ? 'text-primary' : 'text-ink-muted hover:text-ink'"
                class="press relative inline-flex size-9 items-center justify-center rounded-full">
            <svg class="theme-toggle__icon theme-toggle__icon--{{ $value }} size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="{{ $option['icon'] }}" />
            </svg>
            <span class="sr-only">{{ $option['label'] }}</span>
        </button>
    @endforeach
</div>
