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
            <div class="flex gap-4 items-center">
                <a href="/pilih-lapak" class="text-gray-600 hover:text-indigo-600 font-medium">Pilih Lapak</a>
                <span class="text-gray-300">|</span>
                @auth
                <span class="text-gray-800 font-bold hidden md:inline-block">{{ Auth::user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="text-sm bg-red-50 text-red-600 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg font-bold transition-colors shadow-sm">
                        Keluar
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="text-sm bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-2 rounded-lg font-bold transition-colors shadow-sm">
                    Masuk
                </a>
                @endauth
            </div>
        </div>
    </nav>
    {{ $slot }}

    @livewireScripts
</body>

</html>