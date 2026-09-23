<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <wireui:scripts />
        @livewireStyles
    </head>
    <body class="dark:bg-gray-700 dark:text-gray-200">
        {{ $slot }}

        <x-notifications />
        <x-dialog />

        <wireui:scripts />
        @livewireScripts
    </body>
</html>
