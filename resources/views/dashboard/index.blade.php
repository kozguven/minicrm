@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Dashboard"
                title="Dashboard"
                subtitle="Ekip performansının anlık özetini takip edin, öncelikli aksiyonlara buradan geçin."
            >
                <a class="btn btn-ghost" href="/today">Günüm Ekranına Dön</a>
            </x-ui.page-header>

            @if (! $canViewCrm)
                <x-ui.permission-panel>
                    {{ $permissionMessage }}
                </x-ui.permission-panel>
            @else
                <div class="metric-grid">
                    <x-ui.metric-card
                        label="Açık Fırsatlar"
                        :value="$metrics['open_opportunities']"
                        hint="Henüz anlaşmaya dönmeyen fırsatlar"
                    />

                    <x-ui.metric-card
                        label="Haftalık Kapanan Satış"
                        :value="$metrics['weekly_closed_deals']"
                        hint="Bu hafta kapanan anlaşmalar"
                    />
                </div>

                <div class="content-card">
                    <h2 class="section-title">Hızlı Aksiyonlar</h2>
                    <p class="muted">Kritik CRM akışlarına tek tıkla geçiş yapın.</p>
                    <div class="inline-actions" style="margin-top: 0.6rem;">
                        <a class="btn btn-primary" href="/opportunities">Fırsatları Yönet</a>
                        <a class="btn btn-secondary" href="/tasks">Görevleri Gör</a>
                        <a class="btn btn-secondary" href="/deals">Anlaşmaları İncele</a>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
