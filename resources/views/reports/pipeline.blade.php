@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Raporlar"
                title="Pipeline Raporu"
                subtitle="Asama bazinda dagilim ve toplam pipeline degerini takip edin."
            >
                <a class="btn btn-secondary" href="{{ url('/dashboard') }}">Dashboard</a>
            </x-ui.page-header>

            <div class="content-list">
                @foreach ($stages as $stage)
                    <article class="content-card">
                        <div class="content-card__header">
                            <h2 class="content-card__title">{{ $stage->name }}</h2>
                            <span class="badge badge--info">{{ $stage->opportunities_count }} firsat</span>
                        </div>
                        <p class="muted">
                            Toplam deger:
                            {{ number_format((float) ($stage->opportunities_value_sum ?? 0), 2, ',', '.') }} TL
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </x-ui.panel>
@endsection
