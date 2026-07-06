<!DOCTYPE html>
<html lang="ru" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Админ-панель') — RSG-CRM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-gray-700"
      x-data="{ sidebarOpen: false }">

    <div class="min-h-full">

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen"
             x-transition.opacity
             class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
             @click="sidebarOpen = false"
             style="display: none"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform lg:translate-x-0 transition-transform duration-200"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            @include('admin.partials.sidebar')
        </aside>

        {{-- Main content area --}}
        <div class="lg:pl-64">

            {{-- Top header --}}
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200 h-16 flex items-center px-4 lg:px-6">
                @include('admin.partials.header')
            </header>

            {{-- Page content --}}
            <main class="p-4 lg:p-6">
                @yield('content')
            </main>

        </div>

    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
