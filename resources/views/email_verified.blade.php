<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-950 text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <div class="flex flex-col gap-5 w-full items-center justify-center">

            @if ($verified)
                <p class="text-white">{{ $message }}</p>
                <p class="text-gray-400">you can continue exploring in <span class="font-bold">Vfolio</span>.</p>
            @else
                <p class="text-red-700">{{ $message }}</p>
            @endif
        </div>
    </body>
</html>
