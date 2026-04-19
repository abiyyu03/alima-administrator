@props(['href', 'icon' => 'circle', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
          {{ $active
              ? 'bg-green-700 text-white font-medium'
              : 'text-green-200 hover:bg-green-800 hover:text-white' }}">

    <x-sidebar-icon :name="$icon" class="w-4 h-4 flex-shrink-0" />
    <span>{{ $slot }}</span>
</a>
