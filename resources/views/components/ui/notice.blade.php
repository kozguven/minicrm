@props([
    'tone' => 'info',
])

@php
    $toneClass = match ($tone) {
        'success' => 'notice--success',
        'warning' => 'notice--warning',
        'danger' => 'notice--danger',
        default => 'notice--info',
    };
@endphp

<div {{ $attributes->class(['notice', $toneClass]) }}>
    {{ $slot }}
</div>
