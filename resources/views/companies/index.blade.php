@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Şirketler"
                title="Şirketler"
                subtitle="Müşteri firmalarınızı ve bağlı kişi kayıtlarını tek listede takip edin."
            >
                <a class="btn btn-primary" href="{{ url('/companies/create') }}">Yeni Şirket</a>
            </x-ui.page-header>

            <form method="GET" action="{{ url('/companies') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 300px;">
                    <label class="field-label" for="company-search">Arama</label>
                    <input
                        class="input"
                        id="company-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] }}"
                        placeholder="Şirket adı veya web sitesi ara"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '')
                    <a class="btn btn-ghost" href="{{ url('/companies') }}">Temizle</a>
                @endif
            </form>

            @if ($companies->isEmpty())
                <x-ui.empty-state>Henüz şirket kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($companies as $company)
                        <article class="content-card">
                            <div class="content-card__header">
                                <h2 class="content-card__title">{{ $company->name }}</h2>
                                <a class="btn btn-ghost" href="{{ url("/companies/{$company->id}") }}">Detay</a>
                            </div>
                            <p class="muted">{{ $company->website ?: 'Web sitesi eklenmedi' }}</p>
                            <p class="muted">{{ $company->contacts_count }} kişi</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
