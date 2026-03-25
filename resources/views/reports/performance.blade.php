@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Kullanici Performans Raporu"
                subtitle="Takim bazli gorev ve takip tamamlama performansini izleyin."
            >
                <a class="btn btn-secondary" href="{{ url('/reports/pipeline') }}">Pipeline</a>
            </x-ui.page-header>

            @if ($users->isEmpty())
                <x-ui.empty-state>Performans olcumu icin yeterli kullanici aktivitesi bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($users as $userMetric)
                        <article class="content-card">
                            <h2 class="content-card__title">{{ $userMetric['name'] }}</h2>
                            <div class="metric-grid" style="margin-top: 0.6rem;">
                                <x-ui.metric-card label="Acik Gorev Yuku" :value="$userMetric['open_tasks']" />
                                <x-ui.metric-card
                                    label="Gecikme Orani"
                                    :value="number_format($userMetric['overdue_rate'], 2, ',', '.') . '%'"
                                />
                                <x-ui.metric-card
                                    label="Takip Tamamlama Orani"
                                    :value="number_format($userMetric['follow_up_completion_rate'], 2, ',', '.') . '%'"
                                />
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
