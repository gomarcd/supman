<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Supman</title>

         @vite(['resources/css/flatpickr.min.css', 'resources/css/app.css', 'resources/js/app.js'])
         @livewireStyles
    </head>

    <body>
        @livewire('question-search')

        @livewireScripts
    </body>

</html>