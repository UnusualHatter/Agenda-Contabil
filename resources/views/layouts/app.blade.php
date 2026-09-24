<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body>
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="mx-auto max-w-6xl px-4 pt-10 pb-2 sm:px-6 lg:px-8">
                    {{ $header }}
                </header>
            @endisset

            <main class="pb-16">
                {{ $slot }}
            </main>
        </div>
        @if (session('welcome'))
            <x-curtain :title="__('session.welcome_title', ['name' => Str::before(Auth::user()->name, ' ')])" :subtitle="__('session.welcome_subtitle')" />
        @endif

        <template data-farewell>
            <x-curtain phase="enter" :title="__('session.farewell_title', ['name' => Str::before(Auth::user()->name, ' ')])" :subtitle="__('session.farewell_subtitle')" />
        </template>

        @livewireScriptConfig(['nonce' => Vite::cspNonce()])
    </body>
</html>
