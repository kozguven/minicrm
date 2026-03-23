@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<header class="page-header">
    <div class="page-header__content">
        @if ($eyebrow)
            <p class="page-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="page-header__actions">
            {{ $slot }}
        </div>
    @endif
</header>
