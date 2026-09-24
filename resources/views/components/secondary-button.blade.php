<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-ink press hover:bg-sunken disabled:opacity-50']) }}>
    {{ $slot }}
</button>
