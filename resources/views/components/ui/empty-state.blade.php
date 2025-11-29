@props([
    'message' => 'لا توجد بيانات',
    'class' => ''
])

<div {{ $attributes->merge(['class' => "py-8 text-center $class"]) }}>
    <x-icons.empty-box class="mx-auto w-12 h-12 text-gray-400" />
    <p class="mt-2 text-gray-600">{{ $message }}</p>
</div>