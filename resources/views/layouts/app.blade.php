<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="pt-16">
    <nav class="fixed top-0 w-full bg-white shadow-sm border-b border-gray-100 p-4 mb-6 z-30">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <a href="/dashboard" class="font-bold text-indigo-600 text-xl">BPU Unila</a>
            <div class="flex gap-4">
                <a href="/pilih-lapak" class="text-gray-600 hover:text-indigo-600 font-medium">Pilih Lapak</a>
                <span class="text-gray-300">|</span>
                <span class="text-gray-800 font-bold">{{ Auth::user()?->name }}</span>
            </div>
        </div>
    </nav>
    {{ $slot }}

    @livewireScripts
</body>

</html>