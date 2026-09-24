@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-soft border-line bg-surface text-ink placeholder:text-ink-muted focus:border-primary focus:ring-primary disabled:bg-sunken']) }}>
