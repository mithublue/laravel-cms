@php
    $t = $page->translation ?? ($page->translations->first() ?? null);
    $title = $t?->title ?? 'Page';
    $content = $t?->content ?? '';
@endphp

<x-app-layout :title="$title">
    <article class="prose max-w-none">
        <h1 class="mb-4">{{ $title }}</h1>
        <div x-data class="content">{!! $content !!}</div>
    </article>
</x-app-layout>
