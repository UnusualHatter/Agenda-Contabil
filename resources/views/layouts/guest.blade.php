<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body>
        <div class="mx-auto grid min-h-screen max-w-6xl gap-10 px-4 py-8 sm:px-6 lg:grid-cols-[1.1fr_1fr] lg:items-center lg:gap-16 lg:px-8">
            <div class="flex items-center justify-between gap-4 lg:col-span-2 lg:self-start">
                <a href="/"><x-brand /></a>
                <x-theme-toggle />
            </div>

            <section class="order-last max-w-xl lg:order-none">
                <p class="text-sm uppercase tracking-[0.18em] text-clay">{{ __('brand.project') }}</p>
                <h1 class="mt-4 text-4xl leading-tight sm:text-5xl">{{ __('brand.headline') }}</h1>
                <p class="mt-6 text-lg leading-relaxed text-ink-muted">{{ __('brand.objective') }}</p>
                <p class="mt-6 border-t border-line pt-6 text-sm leading-relaxed text-ink-muted">{{ __('brand.services') }}</p>
            </section>

            <section class="w-full rounded-[1.75rem] border border-line bg-surface p-6 shadow-xl shadow-shade/5 sm:p-10 lg:self-start">
                {{ $slot }}
            </section>
        </div>
        @if (session('farewell'))
            <x-curtain class="curtain--quick" :title="__('session.signed_out_title')" :subtitle="__('session.signed_out_subtitle')" />
        @endif

        @livewireScriptConfig(['nonce' => Vite::cspNonce()])
    </body>
</html>
