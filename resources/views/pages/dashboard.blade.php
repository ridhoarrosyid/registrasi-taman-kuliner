<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dasbor Tenant - Wajah Digital untuk Bisnis Profesional</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="bg-gray-50 min-h-screen font-sans antialiased">

  <nav class="bg-white shadow-sm border-b border-gray-100 p-4 mb-6">
    <div class="max-w-5xl mx-auto flex justify-between items-center">
      <span class="font-bold text-indigo-600 text-xl">BPU Unila</span>
      <div class="flex gap-4">
        <a href="/pilih-lapak" class="text-gray-600 hover:text-indigo-600 font-medium">Pilih Lapak</a>
        <span class="text-gray-300">|</span>
        <span class="text-gray-800 font-bold">{{ Auth::user()->name }}</span>
      </div>
    </div>
  </nav>

  <livewire:tenant.dashboard />

  @livewireScripts
</body>

</html>