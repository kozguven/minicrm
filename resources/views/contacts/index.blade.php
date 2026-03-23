@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Kişiler"
                title="Kişiler"
                subtitle="Şirketlere bağlı ilgili kişileri listeleyin ve hızlıca yenilerini ekleyin."
            >
                <a class="btn btn-primary" href="{{ url('/contacts/create') }}">Yeni Kişi</a>
            </x-ui.page-header>

            <form method="GET" action="{{ url('/contacts') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 300px;">
                    <label class="field-label" for="contact-search">Arama</label>
                    <input
                        class="input"
                        id="contact-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] }}"
                        placeholder="Kişi, e-posta, telefon veya şirket ara"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '')
                    <a class="btn btn-ghost" href="{{ url('/contacts') }}">Temizle</a>
                @endif
            </form>

            @if ($contacts->isEmpty())
                <x-ui.empty-state>Henüz kişi kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($contacts as $contact)
                        <article class="content-card">
                            <div class="content-card__header">
                                <div>
                                    <h2 class="content-card__title">{{ $contact->first_name }} {{ $contact->last_name }}</h2>
                                    <span class="muted">{{ $contact->company?->name }}</span>
                                </div>
                                <a class="btn btn-ghost" href="{{ url("/contacts/{$contact->id}") }}">Detay</a>
                            </div>
                            <p class="muted">{{ $contact->email ?: 'E-posta eklenmedi' }}</p>
                            <p class="muted">{{ $contact->phone ?: 'Telefon eklenmedi' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
