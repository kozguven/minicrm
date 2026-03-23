@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Anlaşmalar"
                title="Anlaşma Detayı"
                subtitle="Kapanış kaydı ve bağlı fırsat bilgisini inceleyin."
            >
                <a class="btn btn-secondary" href="{{ url('/deals') }}">Listeye Dön</a>
            </x-ui.page-header>

            <article class="content-card">
                <h2 class="content-card__title">{{ $deal->opportunity?->title ?: 'Fırsat bilgisi yok' }}</h2>
                <p class="muted">
                    Tutar:
                    @if ($deal->amount !== null)
                        {{ number_format((float) $deal->amount, 2, ',', '.') }} TL
                    @else
                        Belirtilmedi
                    @endif
                </p>
                <p class="muted">Kapanış: {{ $deal->closed_at?->format('d.m.Y H:i') ?: 'Kapanış bekleniyor' }}</p>
                <p class="muted">Aşama: {{ $deal->opportunity?->opportunityStage?->name ?: 'Belirsiz' }}</p>

                @if ($deal->opportunity)
                    <p class="muted">
                        Fırsat detayı:
                        <a href="{{ url("/opportunities/{$deal->opportunity->id}") }}">Aç</a>
                    </p>
                @endif

                @if ($deal->opportunity?->contact)
                    <p class="muted">
                        Kişi:
                        <a href="{{ url("/contacts/{$deal->opportunity->contact->id}") }}">
                            {{ $deal->opportunity->contact->first_name }} {{ $deal->opportunity->contact->last_name }}
                        </a>
                    </p>
                @endif

                @if ($deal->opportunity?->contact?->company)
                    <p class="muted">
                        Şirket:
                        <a href="{{ url("/companies/{$deal->opportunity->contact->company->id}") }}">
                            {{ $deal->opportunity->contact->company->name }}
                        </a>
                    </p>
                @endif
            </article>
        </div>
    </x-ui.panel>
@endsection
