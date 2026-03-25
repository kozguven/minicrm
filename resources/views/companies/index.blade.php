@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
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

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="company-sort">Sıralama</label>
                    <select class="select" id="company-sort" name="sort">
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>İsim (A-Z)</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>İsim (Z-A)</option>
                        <option value="contacts_desc" @selected($filters['sort'] === 'contacts_desc')>Kişi Sayısı</option>
                        <option value="recent" @selected($filters['sort'] === 'recent')>En Yeni</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="company-created-from">Kayıt Başlangıç</label>
                    <input
                        class="input"
                        id="company-created-from"
                        name="created_from"
                        type="date"
                        value="{{ $filters['created_from'] }}"
                    >
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="company-created-to">Kayıt Bitiş</label>
                    <input
                        class="input"
                        id="company-created-to"
                        name="created_to"
                        type="date"
                        value="{{ $filters['created_to'] }}"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '' || $filters['sort'] !== 'name_asc' || $filters['created_from'] !== '' || $filters['created_to'] !== '')
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
                                <div class="inline-actions">
                                    <a class="btn btn-ghost" href="{{ url("/companies/{$company->id}") }}">Detay</a>
                                    @can('update', $company)
                                        <a class="btn btn-ghost" href="{{ url("/companies/{$company->id}/edit") }}">Düzenle</a>
                                    @endcan
                                </div>
                            </div>
                            <p class="muted">{{ $company->website ?: 'Web sitesi eklenmedi' }}</p>
                            <p class="muted">{{ $company->contacts_count }} kişi</p>
                        </article>
                    @endforeach
                </div>

                {{ $companies->links() }}
            @endif
        </div>
    </x-ui.panel>
@endsection
