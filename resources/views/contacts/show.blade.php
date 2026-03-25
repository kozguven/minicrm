@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Kişiler"
                title="Kişi Detayı"
                subtitle="İletişim bilgileri, görüşme geçmişi ve bu kişiye bağlı fırsatlar."
            >
                @can('update', $contact)
                    <a class="btn btn-ghost" href="{{ url("/contacts/{$contact->id}/edit") }}">Düzenle</a>
                @endcan
                <a class="btn btn-secondary" href="{{ url('/contacts') }}">Listeye Dön</a>
            </x-ui.page-header>

            @if (session('status'))
                <x-ui.notice tone="success">{{ session('status') }}</x-ui.notice>
            @endif

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.notice>
            @endif

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

            <article class="content-card">
                <h2 class="section-title">Sonraki En Iyi Aksiyon</h2>
                <p class="muted">{{ $bestNextAction }}</p>
            </article>

            <section class="surface-stack">
                <h2 class="section-title">Aktivite Zaman Cizgisi</h2>

                @if ($timelineEvents->isEmpty())
                    <x-ui.empty-state>Bu kisi icin zaman cizgisi olayi bulunmuyor.</x-ui.empty-state>
                @else
                    <div class="content-list">
                        @foreach ($timelineEvents as $event)
                            <article class="content-card">
                                <div class="content-card__header">
                                    <h3 class="content-card__title">{{ $event['title'] }}</h3>
                                    <span class="muted">{{ $event['occurred_at']->format('d.m.Y H:i') }}</span>
                                </div>
                                <p class="muted">{{ $event['detail'] }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="surface-stack" id="gorusmeler">
                <h2 class="section-title">Görüşme Geçmişi</h2>

                @can('create', \App\Models\ContactInteraction::class)
                    <article class="content-card">
                        <form class="form-stack" method="POST" action="{{ url('/contact-interactions') }}">
                            @csrf
                            <input type="hidden" name="contact_id" value="{{ $contact->id }}">

                            <div class="field">
                                <label class="field-label" for="channel">Kanal</label>
                                <select class="select" id="channel" name="channel" required>
                                    <option value="call" @selected(old('channel', 'call') === 'call')>Telefon</option>
                                    <option value="meeting" @selected(old('channel') === 'meeting')>Toplanti</option>
                                    <option value="email" @selected(old('channel') === 'email')>E-posta</option>
                                    <option value="whatsapp" @selected(old('channel') === 'whatsapp')>WhatsApp</option>
                                    <option value="other" @selected(old('channel') === 'other')>Diger</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field-label" for="happened_at">Görüşme Tarihi</label>
                                <input
                                    class="input"
                                    id="happened_at"
                                    type="datetime-local"
                                    name="happened_at"
                                    value="{{ old('happened_at', now()->format('Y-m-d\TH:i')) }}"
                                    required
                                >
                            </div>

                            <div class="field">
                                <label class="field-label" for="summary">Görüşme Özeti</label>
                                <input class="input" id="summary" type="text" name="summary" value="{{ old('summary') }}" required>
                            </div>

                            <div class="field">
                                <label class="field-label" for="notes">Detay Notu</label>
                                <textarea class="textarea" id="notes" name="notes">{{ old('notes') }}</textarea>
                            </div>

                            <div class="field">
                                <label class="field-label" for="follow_up_due_at">Takip tarihi</label>
                                <input
                                    class="input"
                                    id="follow_up_due_at"
                                    type="datetime-local"
                                    name="follow_up_due_at"
                                    value="{{ old('follow_up_due_at') }}"
                                >
                            </div>

                            <div class="inline-actions">
                                <button class="btn btn-primary" type="submit">Görüşme Kaydet</button>
                            </div>
                        </form>
                    </article>
                @endcan

                @if ($contact->contactInteractions->isEmpty())
                    <x-ui.empty-state>Bu kişi için henüz görüşme kaydı bulunmuyor.</x-ui.empty-state>
                @else
                    <div class="content-list">
                        @foreach ($contact->contactInteractions as $interaction)
                            <article class="content-card">
                                <div class="content-card__header">
                                    <div>
                                        <h3 class="content-card__title">{{ $interaction->summary }}</h3>
                                        <p class="muted">
                                            {{ $interaction->happened_at?->format('d.m.Y H:i') ?: 'Tarih yok' }}
                                            · {{ strtoupper($interaction->channel) }}
                                            @if ($interaction->user)
                                                · {{ $interaction->user->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if ($interaction->notes)
                                    <p class="muted">{{ $interaction->notes }}</p>
                                @endif

                                @if ($interaction->follow_up_due_at)
                                    <p class="muted">
                                        Takip tarihi: {{ $interaction->follow_up_due_at->format('d.m.Y H:i') }}
                                        · {{ $interaction->follow_up_completed_at ? 'Tamamlandi' : 'Takip bekliyor' }}
                                    </p>

                                    @can('update', $interaction)
                                        <form method="POST" action="{{ url("/contact-interactions/{$interaction->id}/toggle-follow-up") }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-secondary" type="submit">
                                                {{ $interaction->follow_up_completed_at ? 'Takibi Yeniden Aç' : 'Takibi Tamamla' }}
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

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
