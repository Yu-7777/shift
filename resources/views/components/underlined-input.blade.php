{{-- resources/views/components/underlined-input.blade.php --}}
@props(['label', 'name', 'type' => 'text'])
<div class="w-full max-w-lg grid grid-cols-[auto_1fr] gap-x-4 items-center border-b-2 border-gray-300">
    <label for="{{ $name }}" class="w-36 text-base text-gray-700">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" {{ $attributes->merge(['class'=>'w-full border-0 focus:outline-none focus:ring-0 py-2']) }} />
</div>
<x-input-error :messages="$errors->get($name)" class="mt-2" />