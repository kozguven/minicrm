@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Fırsatlar"
                title="Fırsatlar"
                subtitle="Potansiyel satışları aşama bazlı takip edin ve boru hattını güncel tutun."
            >
                <a class="btn btn-ghost" href="{{ url('/opportunities/kanban') }}">Kanban</a>
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

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="stage-filter">Aşama</label>
                    <select class="select" id="stage-filter" name="stage_id">
                        <option value="">Tümü</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}" @selected((string) $stage->id === $filters['stage_id'])>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="sort-filter">Sıralama</label>
                    <select class="select" id="sort-filter" name="sort">
                        <option value="expected_close_desc" @selected($filters['sort'] === 'expected_close_desc')>Kapanış (Yeni)</option>
                        <option value="expected_close_asc" @selected($filters['sort'] === 'expected_close_asc')>Kapanış (Eski)</option>
                        <option value="value_desc" @selected($filters['sort'] === 'value_desc')>Tutar (Yüksek)</option>
                        <option value="value_asc" @selected($filters['sort'] === 'value_asc')>Tutar (Düşük)</option>
                        <option value="title_asc" @selected($filters['sort'] === 'title_asc')>Başlık (A-Z)</option>
                    </select>
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '' || $filters['stage_id'] !== '' || $filters['sort'] !== 'expected_close_desc')
                    <a class="btn btn-ghost" href="{{ url('/opportunities') }}">Temizle</a>
                @endif
            </form>

            @can('create', \App\Models\Opportunity::class)
                <form id="bulk-stage-form" method="POST" action="{{ url('/opportunities/bulk-stage') }}" class="inline-actions">
                    @csrf
                    @method('PATCH')

                    <div class="field" style="flex: 1 1 220px;">
                        <label class="field-label" for="bulk-opportunity-stage">Toplu Aşama</label>
                        <select class="select" id="bulk-opportunity-stage" name="opportunity_stage_id" required>
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-secondary" type="submit">Seçili Fırsatları Güncelle</button>
                </form>
            @endcan

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
                                        @can('update', $opportunity)
                                            <label class="checkbox-row" style="margin-bottom: 0.35rem;">
                                                <input
                                                    class="checkbox"
                                                    type="checkbox"
                                                    name="opportunity_ids[]"
                                                    value="{{ $opportunity->id }}"
                                                    form="bulk-stage-form"
                                                >
                                                <span>Toplu işleme ekle</span>
                                            </label>
                                        @endcan

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
                                        <div class="inline-actions" style="margin-top: 0.45rem; justify-content: flex-end;">
                                            <a class="btn btn-ghost" href="{{ url("/opportunities/{$opportunity->id}") }}">Detay</a>
                                            @can('update', $opportunity)
                                                <a class="btn btn-ghost" href="{{ url("/opportunities/{$opportunity->id}/edit") }}">Düzenle</a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>

                                <p class="muted">Beklenen kapanış: {{ $opportunity->expected_close_date ?: 'Belirlenmedi' }}</p>
                                <p class="muted">
                                    Olasilik: %{{ (int) ($opportunity->probability ?? 0) }}
                                    · Beklenen gelir: {{ number_format($opportunity->expectedRevenue(), 2, ',', '.') }} TL
                                </p>
                                <p class="muted">
                                    Sonraki adım: {{ $opportunity->next_step ?: 'Belirtilmedi' }}
                                    @if ($opportunity->next_step_due_at)
                                        · {{ $opportunity->next_step_due_at->format('d.m.Y H:i') }}
                                    @endif
                                </p>

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

                {{ $opportunities->links() }}
            @endif
        </div>
    </x-ui.panel>
@endsection
