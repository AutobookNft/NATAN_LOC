<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NATAN - Cognitive Trust Layer per PA & Enterprises. Non immagina. Dimostra.">
    
    <title>{{ $title ?? 'NATAN - Cognitive Trust Layer' }}</title>
    
    <!-- NATAN Fonts: IBM Plex Sans, Source Sans Pro, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Source+Sans+Pro:wght@400;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-natan-gray-50 font-body text-natan-gray-700 antialiased">
    <div class="min-h-screen flex flex-col">
        {{-- Header minimale mobile-first --}}
        <x-natan.header />
        
        {{-- Main content area --}}
        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>

