@extends('theme::layouts.app')

@section('content')
    @php
        $t = $product->translation ?? ($product->translations->first() ?? null);
        $title = $t?->name ?? 'Product';
        $desc = $t?->description ?? '';
    @endphp

    <article class="prose max-w-none">
        <h1 class="mb-4">{{ $title }}</h1>
        <div x-data class="content">{!! $desc !!}</div>
    </article>
@endsection
