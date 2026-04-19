@props(['label' => null, 'error' => null, 'name' => ''])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' =>
            'w-full rounded-lg border px-3 py-2 text-sm shadow-sm transition focus:outline-none focus:ring-2 focus:ring-green-400 ' .
            ($error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white')
        ]) }}
    >

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
