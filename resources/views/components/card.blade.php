@props(['title' => null, 'actions' => null])

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    @if($title || $actions)
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 md:px-6 py-4 border-b border-gray-100">
            @if($title)
                <h2 class="text-base font-semibold text-gray-800">{{ $title }}</h2>
            @endif
            @if($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="p-4 md:p-6">
        {{ $slot }}
    </div>
</div>
