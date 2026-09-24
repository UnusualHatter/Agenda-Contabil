<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full bg-danger px-5 py-2.5 text-sm font-semibold text-surface press hover:opacity-90 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
