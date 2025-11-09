<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- SEO Meta Tags --}}
        <meta name="description" content="A modern Bible reading application with multiple translations, cross-references, verse highlighting, and reading progress tracking. Read the Bible online for free.">
        <meta name="keywords" content="Bible, Bible reading, Bible translations, Bible study, cross-references, scripture, Christian resources, online Bible">
        <meta name="author" content="Kea Rajabu Baruan">
        <meta name="robots" content="index, follow">
        <meta name="language" content="English">
        
        {{-- Open Graph Meta Tags --}}
        <meta property="og:title" content="{{ config('app.name', 'Bible Application') }}">
        <meta property="og:description" content="A modern Bible reading application with multiple translations, cross-references, and study tools.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:site_name" content="{{ config('app.name', 'Bible Application') }}">
        
        {{-- Twitter Card Meta Tags --}}
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ config('app.name', 'Bible Application') }}">
        <meta name="twitter:description" content="A modern Bible reading application with multiple translations, cross-references, and study tools.">
        
        {{-- Canonical URL --}}
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Bible') }}</title>

        <link rel="icon" href="storage/favicon.ico" sizes="any">
        <link rel="icon" href="storage/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/storage/public/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
