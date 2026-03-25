@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Anlaşmalar"
                title="Anlaşmalar"
                subtitle="Kapanan satışları takip edin ve fırsat dönüşümlerini tek ekranda izleyin."
            >
                @can('create', \App\Models\Deal::class)
                    <a class="btn btn-primary" href="{{ url('/deals/create') }}">Yeni Anlaşma</a>
                @endcan
            </x-ui.page-header>

            <form method="GET" action="{{ url('/deals') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 320px;">
                    <label class="field-label" for="deal-search">Arama</label>
                    <input
                        class="input"
                        id="deal-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] }}"
                        placeholder="Anlaşma, fırsat, kişi veya şirket ara"
                    >
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="deal-sort">Sıralama</label>
                    <select class="select" id="deal-sort" name="sort">
                        <option value="closed_desc" @selected($filters['sort'] === 'closed_desc')>Kapanış (Yeni)</option>
                        <option value="closed_asc" @selected($filters['sort'] === 'closed_asc')>Kapanış (Eski)</option>
                        <option value="amount_desc" @selected($filters['sort'] === 'amount_desc')>Tutar (Yüksek)</option>
                        <option value="amount_asc" @selected($filters['sort'] === 'amount_asc')>Tutar (Düşük)</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="deal-closed-from">Kapanış Başlangıç</label>
                    <input
                        class="input"
                        id="deal-closed-from"
                        name="closed_from"
                        type="date"
                        value="{{ $filters['closed_from'] }}"
                    >
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="deal-closed-to">Kapanış Bitiş</label>
                    <input
                        class="input"
                        id="deal-closed-to"
                        name="closed_to"
                        type="date"
                        value="{{ $filters['closed_to'] }}"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '' || $filters['closed_from'] !== '' || $filters['closed_to'] !== '' || $filters['sort'] !== 'closed_desc')
                    <a class="btn btn-ghost" href="{{ url('/deals') }}">Temizle</a>
                @endif
            </form>

            @if (session('status'))
                <x-ui.notice tone="success">{{ session('status') }}</x-ui.notice>
            @endif

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            @if ($deals->isEmpty())
                <x-ui.empty-state>Henüz anlaşma kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($deals as $deal)
                        <article class="content-card">
                            <div class="content-card__header">
                                <div>
                                    <h2 class="content-card__title">{{ $deal->opportunity?->title }}</h2>
                                    <p class="muted">
                                        {{ $deal->opportunity?->contact?->first_name }} {{ $deal->opportunity?->contact?->last_name }}
                                        @if ($deal->opportunity?->contact?->company)
                                            · {{ $deal->opportunity->contact->company->name }}
                                        @endif
                                    </p>
                                </div>

                                <div class="text-right">
                                    <strong>
                                        @if ($deal->amount !== null)
                                            {{ number_format((float) $deal->amount, 2, ',', '.') }} TL
                                        @else
                                            Tutar bekleniyor
                                        @endif
                                    </strong>
                                    <p class="muted">{{ optional($deal->closed_at)->format('d.m.Y H:i') ?: 'Kapanış bekleniyor' }}</p>
                                    <div class="inline-actions" style="margin-top: 0.45rem; justify-content: flex-end;">
                                        <a class="btn btn-ghost" href="{{ url("/deals/{$deal->id}") }}">Detay</a>
                                        @can('update', $deal)
                                            <a class="btn btn-ghost" href="{{ url("/deals/{$deal->id}/edit") }}">Düzenle</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $deals->links() }}
            @endif
        </div>
    </x-ui.panel>
@endsection
