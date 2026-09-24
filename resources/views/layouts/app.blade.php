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
        @livewireScriptConfig
    </body>
</html>
