<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel CMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/theme.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800" x-data>
    <header class="bg-white border-b">
        @php($menu = \App\Support\Cms::menu('primary'))
        @if(View::exists('theme::partials.nav'))
            @include('theme::partials.nav', ['items' => $menu])
        @else
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <a href="/" class="text-xl font-semibold">{{ config('app.name', 'My CMS') }}</a>
                <nav class="space-x-4 text-sm">
                    <a href="/" class="hover:underline">Home</a>
                </nav>
            </div>
        @endif
    </header>

    <main id="content" class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t">
        @includeIf('theme::partials.footer')
        @unless (View::exists('theme::partials.footer'))
            <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-500">
                © {{ date('Y') }} {{ config('app.name', 'My CMS') }}
            </div>
        @endunless
    </footer>
</body>
</html>
