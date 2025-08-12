<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'My CMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/theme.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <header class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-xl font-semibold">My CMS</a>
            <nav class="space-x-4 text-sm">
                <a href="/" class="hover:underline">Home</a>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t">
        <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-500">
            © {{ date('Y') }} My CMS
        </div>
    </footer>
</body>
</html>
