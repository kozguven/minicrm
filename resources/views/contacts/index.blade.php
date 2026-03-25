@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
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

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="contact-lead-status">Lead Durumu</label>
                    <select class="select" id="contact-lead-status" name="lead_status">
                        <option value="all" @selected($filters['lead_status'] === 'all')>Tümü</option>
                        <option value="new" @selected($filters['lead_status'] === 'new')>Yeni</option>
                        <option value="contacted" @selected($filters['lead_status'] === 'contacted')>Temas Kuruldu</option>
                        <option value="qualified" @selected($filters['lead_status'] === 'qualified')>Qualified</option>
                        <option value="lost" @selected($filters['lead_status'] === 'lost')>Kaybedildi</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="contact-priority">Öncelik</label>
                    <select class="select" id="contact-priority" name="priority">
                        <option value="all" @selected($filters['priority'] === 'all')>Tümü</option>
                        <option value="high" @selected($filters['priority'] === 'high')>Yüksek</option>
                        <option value="medium" @selected($filters['priority'] === 'medium')>Orta</option>
                        <option value="low" @selected($filters['priority'] === 'low')>Düşük</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="contact-sort">Sıralama</label>
                    <select class="select" id="contact-sort" name="sort">
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>İsim (A-Z)</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>İsim (Z-A)</option>
                        <option value="last_contact_desc" @selected($filters['sort'] === 'last_contact_desc')>Son Temas (Yeni)</option>
                        <option value="last_contact_asc" @selected($filters['sort'] === 'last_contact_asc')>Son Temas (Eski)</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="contact-last-from">Son Temas Başlangıç</label>
                    <input
                        class="input"
                        id="contact-last-from"
                        name="last_contact_from"
                        type="date"
                        value="{{ $filters['last_contact_from'] }}"
                    >
                </div>

                <div class="field" style="flex: 1 1 170px;">
                    <label class="field-label" for="contact-last-to">Son Temas Bitiş</label>
                    <input
                        class="input"
                        id="contact-last-to"
                        name="last_contact_to"
                        type="date"
                        value="{{ $filters['last_contact_to'] }}"
                    >
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if (
                    $filters['q'] !== '' ||
                    $filters['lead_status'] !== 'all' ||
                    $filters['priority'] !== 'all' ||
                    $filters['sort'] !== 'name_asc' ||
                    $filters['last_contact_from'] !== '' ||
                    $filters['last_contact_to'] !== ''
                )
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
                                <div class="inline-actions">
                                    <a class="btn btn-ghost" href="{{ url("/contacts/{$contact->id}") }}">Detay</a>
                                    @can('update', $contact)
                                        <a class="btn btn-ghost" href="{{ url("/contacts/{$contact->id}/edit") }}">Düzenle</a>
                                    @endcan
                                </div>
                            </div>
                            <p class="muted">{{ $contact->email ?: 'E-posta eklenmedi' }}</p>
                            <p class="muted">{{ $contact->phone ?: 'Telefon eklenmedi' }}</p>
                        </article>
                    @endforeach
                </div>

                {{ $contacts->links() }}
            @endif
        </div>
    </x-ui.panel>
@endsection
