@php
    $t = $post->translation ?? ($post->translations->first() ?? null);
    $title = $t?->title ?? 'Post';
    $content = $t?->content ?? '';
@endphp

<x-app-layout :title="$title">
    <article class="prose max-w-none">
        <h1 class="mb-4">{{ $title }}</h1>
        <div x-data class="content">{!! $content !!}</div>
    </article>
</x-app-layout>
