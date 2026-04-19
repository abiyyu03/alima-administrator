@props([
    'variant' => 'primary',
    'size'    => 'md',
    'href'    => null,
    'type'    => 'button',
])

@php
$variants = [
    'primary'   => 'bg-green-600 hover:bg-green-700 text-white',
    'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-700',
    'danger'    => 'bg-red-600 hover:bg-red-700 text-white',
    'success'   => 'bg-green-600 hover:bg-green-700 text-white',
    'outline'   => 'border border-gray-300 hover:bg-gray-50 text-gray-700',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
];
$base = 'inline-flex items-center gap-2 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1 disabled:opacity-50';
$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
