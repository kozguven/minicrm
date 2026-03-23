@props([
    'label',
    'value',
    'hint' => null,
])

<article {{ $attributes->class(['metric-card']) }}>
    <p class="metric-card__label">{{ $label }}</p>
    <p class="metric-card__value">{{ $value }}</p>
    @if ($hint)
        <p class="metric-card__hint">{{ $hint }}</p>
    @endif
</article>
