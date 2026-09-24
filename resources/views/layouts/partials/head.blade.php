<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="color-scheme" content="light dark">

<title>{{ config('app.name') }}</title>

@include('layouts.partials.theme')
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
