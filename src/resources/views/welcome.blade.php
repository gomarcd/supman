<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Supman</title>
            <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" sizes="32x32">
            @vite(['resources/css/flatpickr.min.css', 'resources/css/app.css', 'resources/js/app.js'])
            @livewireStyles
    </head>

    <body>
        @livewire('question-search')

        @livewireScripts
    </body>

</html>