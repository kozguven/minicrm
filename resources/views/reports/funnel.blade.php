@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Funnel Raporu"
                subtitle="Lead'den kazanima kadar donusum oranlarini izleyin."
            >
                <a class="btn btn-secondary" href="{{ url('/reports/pipeline') }}">Pipeline</a>
            </x-ui.page-header>

            <div class="metric-grid">
                <x-ui.metric-card label="Toplam Lead" :value="$total_leads" />
                <x-ui.metric-card label="Qualified Lead" :value="$qualified_leads" />
                <x-ui.metric-card label="Won Anlasma" :value="$won_deals" />
                <x-ui.metric-card label="Lead -> Qualified" :value="number_format($lead_to_qualified_rate, 2, ',', '.') . '%'" />
                <x-ui.metric-card label="Qualified -> Won" :value="number_format($qualified_to_won_rate, 2, ',', '.') . '%'" />
                <x-ui.metric-card label="Lead -> Won" :value="number_format($lead_to_won_rate, 2, ',', '.') . '%'" />
            </div>
        </div>
    </x-ui.panel>
@endsection
