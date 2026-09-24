@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['rows' => 3, 'class' => 'rounded-soft border-line bg-surface text-ink placeholder:text-ink-muted focus:border-primary focus:ring-primary disabled:bg-sunken']) }}>{{ $slot }}</textarea>
