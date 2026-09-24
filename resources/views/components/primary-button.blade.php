<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary press hover:bg-primary-hover disabled:opacity-50']) }}>
    {{ $slot }}
</button>
