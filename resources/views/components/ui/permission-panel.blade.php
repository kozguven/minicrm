@props([
    'title' => 'Yetki Erişimi Gerekli',
])

<section {{ $attributes->class(['permission-panel']) }}>
    <h2 class="permission-panel__title">{{ $title }}</h2>
    <p class="permission-panel__text">{{ $slot }}</p>
</section>
