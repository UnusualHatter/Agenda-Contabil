@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-soft border-line bg-surface text-ink focus:border-primary focus:ring-primary disabled:bg-sunken']) }}>
    {{ $slot }}
</select>
