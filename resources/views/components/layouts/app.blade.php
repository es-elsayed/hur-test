@props([
    'title' => 'منصة باشنور',
    'showHeader' => true,
    'showSidebar' => true,
    'showFooter' => true,
])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        body {
            direction: rtl;
        }
    </style>
    {{ $styles ?? '' }}
</head>
<body class="bg-gray-50">
    @if($showHeader)
        <x-layout.header />
    @endif

    <div class="container mx-auto">
        <div class="flex flex-col gap-8 lg:flex-row">
            @if($showSidebar)
                <x-layout.sidebar />
            @endif

            {{-- Main Content --}}
            <main class="flex-1 mt-12">
                {{ $slot }}
            </main>
        </div>
    </div>

    @if($showFooter)
        <x-layout.footer />
    @endif

    {{ $scripts ?? '' }}
</body>
</html>

