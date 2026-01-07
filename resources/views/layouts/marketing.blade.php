<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trim(config('app.name', 'App')) }} — {{ $title ?? 'Início' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Organize relatórios e dashboards por organização com facilidade.' }}">
    <link rel="icon" href="/favicon.ico">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: 'media' }
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        <style> body{font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"} </style>
    @endif
</head>
<body class="bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased">
    {{ $slot }}
</body>
</html>
