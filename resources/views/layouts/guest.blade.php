<!DOCTYPE html>
<html lang="ru" class="h-full bg-gradient-to-br from-primary-50 via-white to-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Войти') — RSG-CRM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-gray-700">

    <div class="min-h-full flex flex-col items-center justify-center px-4 py-12">
        @yield('content')

        <p class="mt-8 text-xs text-gray-400">
            © {{ date('Y') }} RSG. Все права защищены.
        </p>
    </div>

    @livewireScripts
</body>
</html>
