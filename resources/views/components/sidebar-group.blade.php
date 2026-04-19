@props(['label'])

<div class="pt-4">
    <p class="px-3 mb-1 text-xs font-semibold text-green-400 uppercase tracking-wider">{{ $label }}</p>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
