@props([
    'variant' => 'primary',  // primary, secondary, gradient
    'type' => 'button',
    'class' => ''
])

@php
$variants = [
    'primary' => 'py-3 font-semibold text-white bg-indigo-900 rounded-lg hover:bg-indigo-800',
    'secondary' => 'px-4 py-3 w-full font-semibold text-indigo-900 bg-white rounded-lg',
    'gradient' => 'px-4 py-3 w-full font-semibold text-indigo-900 bg-white rounded-lg',
];

$buttonClass = $variants[$variant] ?? $variants['primary'];
@endphp

@if($variant === 'gradient')
<div class="bg-gradient-to-r from-sky-400 to-indigo-900 p-[1px] rounded-lg">
    <button {{ $attributes->merge(['type' => $type, 'class' => $buttonClass . ' ' . $class]) }}>
        {{ $slot }}
    </button>
</div>
@else
<button {{ $attributes->merge(['type' => $type, 'class' => $buttonClass . ' ' . $class]) }}>
    {{ $slot }}
</button>
@endif