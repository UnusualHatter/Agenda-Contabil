@if (session('notice'))
    <div {{ $attributes->merge(['class' => 'rise-in rounded-soft bg-moss-soft px-4 py-3 text-sm text-moss']) }} role="status">
        {{ session('notice') }}
    </div>
@endif
