@props([
    'size' => 'xl',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'panel--sm',
        'md' => 'panel--md',
        'lg' => 'panel--lg',
        default => 'panel--xl',
    };
@endphp

<section {{ $attributes->class(['panel', $sizeClass]) }}>
    {{ $slot }}
</section>
