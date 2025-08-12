@extends('theme::layouts.app')

@section('content')
    @php
        $t = $news->translation ?? ($news->translations->first() ?? null);
        $title = $t?->title ?? 'News';
        $content = $t?->content ?? '';
    @endphp

    <article class="prose max-w-none">
        <h1 class="mb-4">{{ $title }}</h1>
        <div x-data class="content">{!! $content !!}</div>
    </article>
@endsection
