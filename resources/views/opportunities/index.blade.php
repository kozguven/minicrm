@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Fırsatlar"
                title="Fırsatlar"
                subtitle="Potansiyel satışları aşama bazlı takip edin ve boru hattını güncel tutun."
            >
                @can('create', \App\Models\Opportunity::class)
                    <a class="btn btn-primary" href="{{ url('/opportunities/create') }}">Yeni Fırsat</a>
                @endcan
            </x-ui.page-header>

            <form method="GET" action="{{ url('/opportunities') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 320px;">
                    <label class="field-label" for="opportunity-search">Arama</label>
                    <input
                        class="input"
                        id="opportunity-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] }}"
                        placeholder="Fırsat, kişi, şirket veya aşama ara"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '')
                    <a class="btn btn-ghost" href="{{ url('/opportunities') }}">Temizle</a>
                @endif
            </form>

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            @if ($opportunities->isEmpty())
                <x-ui.empty-state>Henüz fırsat kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($opportunities as $opportunity)
                        <article class="content-card">
                            <div class="surface-stack" style="gap: 0.7rem;">
                                <div class="content-card__header">
                                    <div>
                                        <h2 class="content-card__title">{{ $opportunity->title }}</h2>
                                        <p class="muted">
                                            {{ $opportunity->contact?->first_name }} {{ $opportunity->contact?->last_name }}
                                            @if ($opportunity->contact?->company)
                                                · {{ $opportunity->contact->company->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="muted">{{ $opportunity->opportunityStage?->name }}</p>
                                        <strong>{{ number_format((float) $opportunity->value, 2, ',', '.') }} TL</strong>
                                        <div style="margin-top: 0.45rem;">
                                            <a class="btn btn-ghost" href="{{ url("/opportunities/{$opportunity->id}") }}">Detay</a>
                                        </div>
                                    </div>
                                </div>

                                <p class="muted">Beklenen kapanış: {{ $opportunity->expected_close_date ?: 'Belirlenmedi' }}</p>

                                @if ($opportunity->deal)
                                    <x-ui.notice tone="success">
                                        Anlaşma oluştu:
                                        @if ($opportunity->deal->amount !== null)
                                            {{ number_format((float) $opportunity->deal->amount, 2, ',', '.') }} TL
                                        @else
                                            Tutar bekleniyor
                                        @endif
                                    </x-ui.notice>
                                @elseif (auth()->user()?->can('create', \App\Models\Deal::class))
                                    <form method="POST" action="{{ url("/opportunities/{$opportunity->id}/convert") }}">
                                        @csrf
                                        <button class="btn btn-primary" type="submit">Anlaşmaya Dönüştür</button>
                                    </form>
                                @endif

                                @can('update', $opportunity)
                                    <form method="POST" action="{{ url("/opportunities/{$opportunity->id}/stage") }}" class="inline-actions">
                                        @csrf
                                        @method('PATCH')

                                        <div class="field" style="flex: 1 1 220px;">
                                            <label class="field-label" for="stage-{{ $opportunity->id }}">Aşama</label>
                                            <select class="select" id="stage-{{ $opportunity->id }}" name="opportunity_stage_id">
                                                @foreach ($stages as $stage)
                                                    <option value="{{ $stage->id }}" @selected($opportunity->opportunity_stage_id === $stage->id)>
                                                        {{ $stage->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button class="btn btn-secondary" type="submit">Aşamayı Güncelle</button>
                                    </form>
                                @endcan
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
