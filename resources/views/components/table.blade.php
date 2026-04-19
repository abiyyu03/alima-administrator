@props(['striped' => false])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 text-sm']) }}>
        {{ $slot }}
    </table>
</div>
