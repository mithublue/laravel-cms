@php
    $t = $product->translation ?? ($product->translations->first() ?? null);
    $title = $t?->name ?? 'Product';
    $desc = $t?->description ?? '';
@endphp

<x-app-layout :title="$title">
    <article class="prose max-w-none">
        <h1 class="mb-4">{{ $title }}</h1>
        <div x-data class="content">{!! $desc !!}</div>
    </article>
</x-app-layout>
