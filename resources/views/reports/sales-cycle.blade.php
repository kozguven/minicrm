@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Satis Dongusu Raporu"
                subtitle="Ortalama kapanis suresi ve asama bazli bekleme dagilimi."
            >
                <a class="btn btn-secondary" href="{{ url('/reports/pipeline') }}">Pipeline</a>
            </x-ui.page-header>

            <div class="metric-grid">
                <x-ui.metric-card
                    label="Ortalama Kapanis Suresi"
                    :value="number_format($average_close_days, 1, ',', '.') . ' gun'"
                />
                <x-ui.metric-card
                    label="Darbogaz Asama"
                    :value="$bottleneck_stage"
                    :hint="number_format($bottleneck_avg_days, 1, ',', '.') . ' gun ortalama bekleme'"
                />
            </div>

            @if ($stage_aging->isEmpty())
                <x-ui.empty-state>Asama bazli acik firsat verisi bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($stage_aging as $item)
                        <article class="content-card">
                            <div class="content-card__header">
                                <h2 class="content-card__title">{{ $item['stage'] }}</h2>
                                <span class="badge badge--info">{{ $item['open_count'] }} acik firsat</span>
                            </div>
                            <p class="muted">
                                Ortalama bekleme:
                                {{ number_format($item['avg_days'], 1, ',', '.') }} gun
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
