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
</head>
<body class="h-full font-sans antialiased text-gray-700"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('rsg-admin-sidebar-collapsed') === '1',
          toggleSidebarCollapsed() {
              this.sidebarCollapsed = ! this.sidebarCollapsed;
              localStorage.setItem('rsg-admin-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
          }
      }">

    <div class="min-h-full">

        <div x-show="sidebarOpen"
             x-transition.opacity
             class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
             @click="sidebarOpen = false"
             style="display: none"></div>

        <aside class="fixed inset-y-0 left-0 z-40 bg-white border-r border-gray-200 transform transition-all duration-200"
               :class="[
                   sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                   sidebarCollapsed ? 'w-64 lg:w-20' : 'w-64 lg:w-64'
               ]">
            @include('admin.partials.sidebar')
        </aside>

        <div class="transition-all duration-200" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-6">
                @include('admin.partials.header')
            </header>

            <main class="{{ $mainClass ?? 'p-4 lg:p-6' }}">
                @yield('content')
            </main>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
