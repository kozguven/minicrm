@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Veri Kalite Paneli"
                subtitle="Eksik alanlari ve atanmamis kayitlari hizlica tespit edin."
            >
                <a class="btn btn-secondary" href="{{ url('/reports/pipeline') }}">Pipeline</a>
            </x-ui.page-header>

            <div class="metric-grid">
                <x-ui.metric-card label="Eksik E-posta" :value="$missing_email" />
                <x-ui.metric-card label="Eksik Telefon" :value="$missing_phone" />
                <x-ui.metric-card label="Next-step Eksigi" :value="$next_step_missing" />
                <x-ui.metric-card label="Atanmamis Kayitlar" :value="$unassigned_total" />
            </div>

            <article class="content-card">
                <h2 class="section-title">Atama Dagilimi</h2>
                <p class="muted">Kisiler: {{ $unassigned_contacts }}</p>
                <p class="muted">Firsatlar: {{ $unassigned_opportunities }}</p>
                <p class="muted">Gorevler: {{ $unassigned_tasks }}</p>
            </article>
        </div>
    </x-ui.panel>
@endsection
