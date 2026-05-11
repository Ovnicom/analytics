<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aplicar tema antes de render para evitar flash --}}
    <script>
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
            if (!localStorage.theme) localStorage.theme = 'light';
        }
    </script>
</head>
<body class="font-sans antialiased min-h-screen bg-gray-50 dark:bg-gray-900" style="color:var(--text-primary)">

    {{-- Navigation --}}
    @include('layouts.navigation')

    {{-- Sub-header (breadcrumb / section nav) --}}
    @isset($header)
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            {{ $header }}
        </div>
    </div>
    @endisset

    {{-- Page Content --}}
    <main>
        {{ $slot }}
    </main>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.theme = isDark ? 'dark' : 'light';
            updateThemeIcons(isDark);
        }

        function updateThemeIcons(isDark) {
            document.querySelectorAll('.icon-moon').forEach(el => el.classList.toggle('hidden', isDark));
            document.querySelectorAll('.icon-sun').forEach(el => el.classList.toggle('hidden', !isDark));
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcons(document.documentElement.classList.contains('dark'));
        });
    </script>

    @stack('scripts')
</body>
</html>
