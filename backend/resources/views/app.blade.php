<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'DisasterResource+') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0B0D0C">
    {{-- Apply the saved theme before first paint (same key and fallback as Hooks/use-theme.ts), so light-mode users never see a dark flash and the sign-in pages follow it too. --}}
    <script>
        try {
            var t = localStorage.getItem('disasterresource_theme');
            document.documentElement.setAttribute('data-theme', t === 'light' || t === 'dark' ? t : (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        } catch (e) {}
    </script>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
