<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-name" content="{{ config('app.name', 'Laravel CMS') }}">
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
                <a href="/" class="text-xl font-semibold" data-turbo-frame="content">{{ config('app.name', 'My CMS') }}</a>
                <nav class="space-x-4 text-sm">
                    <a href="/" class="hover:underline" data-turbo-frame="content">Home</a>
                </nav>
            </div>
        @endif
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <turbo-frame id="content" data-turbo-action="advance">
            @isset($content_view)
                @include($content_view)
            @else
                @includeIf('theme::index')
            @endisset
        </turbo-frame>
    </main>

    <footer class="bg-white border-t">
        @if(View::exists('theme::partials.footer'))
            @include('theme::partials.footer')
        @else
            <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-500">
                © {{ date('Y') }} {{ config('app.name', 'My CMS') }}
            </div>
        @endif
    </footer>

  </body>
 </html>
