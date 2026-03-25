@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Tahmin Paneli"
                subtitle="Commit ve best-case tahminlerini acik firsatlar uzerinden goruntuleyin."
            >
                <a class="btn btn-secondary" href="{{ url('/dashboard') }}">Dashboard</a>
            </x-ui.page-header>

            <div class="metric-grid">
                <x-ui.metric-card
                    label="Commit Tahmin"
                    :value="number_format($commit_forecast, 2, ',', '.') . ' TL'"
                    hint="Sadece commit olarak isaretlenen firsatlar"
                />
                <x-ui.metric-card
                    label="Best-case Tahmin"
                    :value="number_format($best_case_forecast, 2, ',', '.') . ' TL'"
                    hint="Olasilikla agirliklandirilmis tahmin"
                />
                <x-ui.metric-card
                    label="Acik Firsatlar"
                    :value="$open_opportunities"
                    hint="Henüz anlasmaya donusmemis firsat sayisi"
                />
            </div>
        </div>
    </x-ui.panel>
@endsection
