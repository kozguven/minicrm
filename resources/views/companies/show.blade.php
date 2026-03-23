@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Şirketler"
                title="Şirket Detayı"
                subtitle="Şirket bilgilerini ve bağlı kişi kayıtlarını buradan takip edin."
            >
                <a class="btn btn-secondary" href="{{ url('/companies') }}">Listeye Dön</a>
            </x-ui.page-header>

            <article class="content-card">
                <h2 class="content-card__title">{{ $company->name }}</h2>
                <p class="muted">{{ $company->website ?: 'Web sitesi eklenmedi' }}</p>
                <p class="muted">Kayıtlı kişi: {{ $company->contacts->count() }}</p>
            </article>

            <section class="surface-stack">
                <h2 class="section-title">İlgili Kişiler</h2>

                @if ($company->contacts->isEmpty())
                    <x-ui.empty-state>Bu şirkete bağlı kişi kaydı bulunmuyor.</x-ui.empty-state>
                @else
                    <div class="content-list">
                        @foreach ($company->contacts as $contact)
                            <article class="content-card">
                                <div class="content-card__header">
                                    <div>
                                        <h3 class="content-card__title">{{ $contact->first_name }} {{ $contact->last_name }}</h3>
                                        <p class="muted">{{ $contact->email ?: 'E-posta eklenmedi' }}</p>
                                    </div>
                                    <a class="btn btn-ghost" href="{{ url("/contacts/{$contact->id}") }}">Detay</a>
                                </div>
                                <p class="muted">Fırsat sayısı: {{ $contact->opportunities_count }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </x-ui.panel>
@endsection
