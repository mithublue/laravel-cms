@extends('theme::layouts.app')

@php($obj = \App\Support\Cms::get_current_obj())

@section('content')
    <div class="prose max-w-none">
        <h1 class="mb-4">{{ $obj->title ?? $obj->name ?? 'Item' }}</h1>
        @if(!empty($obj->content))
            <div x-data class="content">{!! $obj->content !!}</div>
        @elseif(method_exists($obj, 'translation') || property_exists($obj, 'translations'))
            @php($t = $obj->translation ?? ($obj->translations->first() ?? null))
            @if($t)
                <div x-data class="content">{!! $t->content ?? '' !!}</div>
            @endif
        @else
            <p>Content coming soon.</p>
        @endif
    </div>
@endsection
