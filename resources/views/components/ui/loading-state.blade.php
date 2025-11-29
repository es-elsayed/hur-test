@props([
    'message' => 'جاري التحميل...',
    'class' => ''
])

<div {{ $attributes->merge(['class' => "py-8 text-center $class"]) }}>
    <x-icons.spinner />
    <p class="mt-2 text-gray-600">{{ $message }}</p>
</div>