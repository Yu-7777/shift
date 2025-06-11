{{-- resources/views/components/underlined-input.blade.php --}}
<input
    {{ $attributes->merge([
        'class' => 'w-full border-0 border-b-2 border-gray-300 focus:border-teal-400 focus:outline-none focus:ring-0 focus:border-teal-400'
    ]) }}
/>