@php
    $items = $items ?? [];
    $level = $level ?? 0;
@endphp
@if(!empty($items))
<ul class="flex gap-4 items-center {{ $level > 0 ? 'ml-4 flex-col' : '' }}">
    @foreach($items as $item)
        @php $hasChildren = !empty($item['children']); @endphp
        <li class="group relative">
            <a href="{{ $item['url'] }}" @class([
                'px-2 py-1 rounded hover:bg-gray-100 transition',
                'font-medium' => $level === 0,
            ]) @if(!empty($item['target'])) target="{{ $item['target'] }}" @endif
               @if(!empty($item['rel'])) rel="{{ $item['rel'] }}" @endif>
                {{ $item['title'] }}
            </a>
            @if($hasChildren)
                <div class="absolute left-0 top-full mt-2 hidden group-hover:block">
                    @include('theme::partials.nav', ['items' => $item['children'], 'level' => $level + 1])
                </div>
            @endif
        </li>
    @endforeach
</ul>
@endif
