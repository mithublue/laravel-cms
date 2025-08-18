<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-turbo="false">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ \App\Support\Cms::setting('site_name', config('app.name', 'Laravel')) }}</title>

        <!-- Favicon from Settings (fallback to public/favicon.ico) -->
        <link rel="icon" href="{{ \App\Support\Cms::setting('site_favicon', asset('favicon.ico')) }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes

        <!-- Expose basic settings to the frontend -->
        @php($cmsSettings = [
            'site_name' => \App\Support\Cms::setting('site_name', config('app.name', 'Laravel')),
            'tagline' => \App\Support\Cms::setting('tagline', ''),
            'site_logo' => \App\Support\Cms::setting('site_logo', ''),
            'site_favicon' => \App\Support\Cms::setting('site_favicon', asset('favicon.ico')),
            'locale' => \App\Support\Cms::setting('locale', config('app.locale')),
            'datetime_format' => \App\Support\Cms::setting('datetime_format', 'Y-m-d H:i'),
        ])
        <script>
            window.cmsSettings = @json($cmsSettings);
        </script>

        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
