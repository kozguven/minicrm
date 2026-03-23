@extends('layouts.app')

@section('content')
    @php
        $deal = $opportunity->deal;
    @endphp

    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Fırsatlar"
                title="Fırsat Detayı"
                subtitle="Fırsatın aşama, değer ve görev bilgisini tek ekranda izleyin."
            >
                <a class="btn btn-secondary" href="{{ url('/opportunities') }}">Listeye Dön</a>
            </x-ui.page-header>

            <article class="content-card">
                <div class="content-card__header">
                    <div>
                        <h2 class="content-card__title">{{ $opportunity->title }}</h2>
                        <p class="muted">Aşama: {{ $opportunity->opportunityStage?->name ?: 'Belirsiz' }}</p>
                    </div>
                    <strong>{{ number_format((float) $opportunity->value, 2, ',', '.') }} TL</strong>
                </div>
                <p class="muted">Beklenen kapanış: {{ $opportunity->expected_close_date ?: 'Belirlenmedi' }}</p>
                <p class="muted">
                    İlgili kişi:
                    @if ($opportunity->contact)
                        <a href="{{ url("/contacts/{$opportunity->contact->id}") }}">
                            {{ $opportunity->contact->first_name }} {{ $opportunity->contact->last_name }}
                        </a>
                    @else
                        Belirtilmedi
                    @endif
                </p>
                @if ($opportunity->contact?->company)
                    <p class="muted">
                        Şirket:
                        <a href="{{ url("/companies/{$opportunity->contact->company->id}") }}">
                            {{ $opportunity->contact->company->name }}
                        </a>
                    </p>
                @endif

                @if ($deal)
                    <x-ui.notice tone="success">
                        Anlaşmaya dönüştü.
                        <a href="{{ url("/deals/{$deal->id}") }}">Anlaşma detayını aç</a>
                    </x-ui.notice>
                @endif
            </article>

            <section class="surface-stack">
                <h2 class="section-title">Bağlı Görevler</h2>

                @if ($opportunity->tasks->isEmpty())
                    <x-ui.empty-state>Bu fırsata bağlı görev bulunmuyor.</x-ui.empty-state>
                @else
                    <div class="content-list">
                        @foreach ($opportunity->tasks as $task)
                            @php
                                $isOverdue = $task->completed_at === null && $task->due_at !== null && $task->due_at->isPast();
                                $badgeText = $task->completed_at ? 'Tamamlandı' : ($isOverdue ? 'Gecikmiş' : 'Planlandı');
                                $badgeClass = $task->completed_at ? 'badge--success' : ($isOverdue ? 'badge--danger' : 'badge--info');
                            @endphp

                            <article class="content-card">
                                <div class="content-card__header">
                                    <div>
                                        <h3 class="content-card__title">{{ $task->title }}</h3>
                                        <p class="muted">Termin: {{ $task->due_at?->format('d.m.Y H:i') ?: 'Belirlenmedi' }}</p>
                                    </div>
                                    <div class="inline-actions">
                                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                        <a class="btn btn-ghost" href="{{ url("/tasks/{$task->id}") }}">Detay</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </x-ui.panel>
@endsection
