@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Kişiler"
                title="Kişi Detayı"
                subtitle="İletişim bilgileri ve bu kişiye bağlı fırsatlar."
            >
                <a class="btn btn-secondary" href="{{ url('/contacts') }}">Listeye Dön</a>
            </x-ui.page-header>

            <article class="content-card">
                <h2 class="content-card__title">{{ $contact->first_name }} {{ $contact->last_name }}</h2>
                <p class="muted">
                    Şirket:
                    @if ($contact->company)
                        <a href="{{ url("/companies/{$contact->company->id}") }}">{{ $contact->company->name }}</a>
                    @else
                        Belirtilmedi
                    @endif
                </p>
                <p class="muted">E-posta: {{ $contact->email ?: 'Eklenmedi' }}</p>
                <p class="muted">Telefon: {{ $contact->phone ?: 'Eklenmedi' }}</p>
            </article>

            <section class="surface-stack">
                <h2 class="section-title">Fırsatlar</h2>

                @if ($contact->opportunities->isEmpty())
                    <x-ui.empty-state>Bu kişiye bağlı fırsat bulunmuyor.</x-ui.empty-state>
                @else
                    <div class="content-list">
                        @foreach ($contact->opportunities as $opportunity)
                            <article class="content-card">
                                <div class="content-card__header">
                                    <div>
                                        <h3 class="content-card__title">{{ $opportunity->title }}</h3>
                                        <p class="muted">Aşama: {{ $opportunity->opportunityStage?->name ?: 'Belirsiz' }}</p>
                                    </div>
                                    <a class="btn btn-ghost" href="{{ url("/opportunities/{$opportunity->id}") }}">Detay</a>
                                </div>
                                <p class="muted">Değer: {{ number_format((float) $opportunity->value, 2, ',', '.') }} TL</p>
                                <p class="muted">
                                    {{ $opportunity->deal ? 'Anlaşmaya dönüştü' : 'Henüz anlaşmaya dönüşmedi' }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </x-ui.panel>
@endsection
