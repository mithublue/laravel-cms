<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-name" content="{{ \App\Support\Cms::setting('site_name', config('app.name', 'Laravel CMS')) }}">
    @php($siteName = \App\Support\Cms::setting('site_name', config('app.name', 'Laravel CMS')))
    @php($tagline = \App\Support\Cms::setting('tagline', ''))
    <title>{{ $title ?? $siteName }}</title>
    @if(!empty($tagline))
        <meta name="description" content="{{ $tagline }}">
    @endif
    <link rel="icon" href="{{ \App\Support\Cms::setting('site_favicon', asset('favicon.ico')) }}">
    @vite(['resources/css/app.css', 'resources/js/theme.js'])
  </head>
  <body class="min-h-screen bg-gray-50 text-gray-800" x-data>
    <header class="bg-white border-b">
        @php($menu = \App\Support\Cms::menu('primary'))
        @if(View::exists('theme::partials.nav'))
            @include('theme::partials.nav', ['items' => $menu])
        @else
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <a href="/" class="text-xl font-semibold flex items-center gap-2" data-turbo-frame="content">
                    @php($logo = \App\Support\Cms::setting('site_logo'))
                    @if(!empty($logo))
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-8 w-auto" />
                    @else
                        {{ $siteName }}
                    @endif
                </a>
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
                © {{ date('Y') }} {{ $siteName }}
            </div>
        @endif
    </footer>

  </body>
 </html>
