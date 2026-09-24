@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-soft bg-sunken px-4 py-3 text-sm text-moss']) }} role="status">
        {{ $status }}
    </div>
@endif
