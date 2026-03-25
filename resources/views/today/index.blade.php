@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Günüm"
                title="Günüm"
                subtitle="Bugün önce hangi işi ele almanız gerektiğini tek ekranda görün."
            >
                <a class="btn btn-ghost" href="/dashboard">Dashboard</a>
            </x-ui.page-header>

            @if (session('status'))
                <x-ui.notice tone="success">{{ session('status') }}</x-ui.notice>
            @endif

            @if (! $canViewCrm)
                <x-ui.permission-panel>
                    {{ $permissionMessage }}
                </x-ui.permission-panel>
            @else
                <div class="surface-stack">
                    @foreach ($sections as $section)
                        <section class="content-card">
                            <div class="surface-stack" style="gap: 0.8rem;">
                                <div class="content-card__header">
                                    <div>
                                        <h2 class="section-title">{{ $section['title'] }}</h2>
                                        <p class="muted">{{ $section['items']->count() }} kayıt</p>
                                    </div>
                                </div>

                                @if ($section['items']->isEmpty())
                                    <x-ui.empty-state>{{ $section['empty_message'] }}</x-ui.empty-state>
                                @else
                                    <div class="content-list">
                                        @foreach ($section['items'] as $item)
                                            <article class="content-card">
                                                @if ($section['type'] === 'critical_follow_up')
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->summary }}</h3>
                                                            <span class="badge badge--danger">Kritik</span>
                                                        </div>
                                                        <p class="muted">{{ $item->follow_up_due_at?->format('d.m.Y H:i') ?: 'Tarih yok' }}</p>
                                                        <p class="muted">
                                                            {{ $item->contact?->first_name }} {{ $item->contact?->last_name }}
                                                            @if ($item->contact?->company)
                                                                · {{ $item->contact->company->name }}
                                                            @endif
                                                        </p>

                                                        <div class="inline-actions">
                                                            <a class="btn btn-ghost" href="{{ url("/contacts/{$item->contact_id}") }}">Kişi Detayı</a>

                                                            @can('update', $item)
                                                                <form method="POST" action="{{ url("/contact-interactions/{$item->id}/toggle-follow-up") }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="btn btn-secondary" type="submit">Tamamlandı Olarak İşaretle</button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                @elseif ($section['type'] === 'overdue_next_step')
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->title }}</h3>
                                                            <span class="badge badge--danger">Gecikmis</span>
                                                        </div>
                                                        <p class="muted">Next-step: {{ $item->next_step ?: 'Belirtilmedi' }}</p>
                                                        <p class="muted">Termin: {{ $item->next_step_due_at?->format('d.m.Y H:i') ?: 'Tarih yok' }}</p>
                                                        <a class="btn btn-ghost" href="{{ url("/opportunities/{$item->id}") }}">Fırsat Detayı</a>
                                                    </div>
                                                @elseif ($section['type'] === 'sla_violation')
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->title }}</h3>
                                                            <span class="badge badge--danger">SLA</span>
                                                        </div>
                                                        <p class="muted">{{ $item->opportunity?->title ?: 'Fırsat bilgisi yok' }}</p>
                                                        <p class="muted">{{ $item->due_at?->format('d.m.Y H:i') ?: 'Termin yok' }}</p>

                                                        @can('update', $item)
                                                            <div class="inline-actions">
                                                                <a class="btn btn-ghost" href="{{ url("/tasks/{$item->id}") }}">Görev Detayı</a>
                                                                <form method="POST" action="{{ url("/tasks/{$item->id}/toggle-complete") }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="btn btn-secondary" type="submit">Tamamlandı Olarak İşaretle</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <a class="btn btn-ghost" href="{{ url("/tasks/{$item->id}") }}">Görev Detayı</a>
                                                        @endcan
                                                    </div>
                                                @elseif ($section['type'] === 'call')
                                                    @php
                                                        $todayOpportunity = $item->opportunities->first();
                                                    @endphp

                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->first_name }} {{ $item->last_name }}</h3>
                                                            <span class="muted">{{ $item->phone }}</span>
                                                        </div>
                                                        <p class="muted">{{ $item->company?->name ?: 'Şirket bilgisi yok' }}</p>
                                                        <p class="muted">{{ $todayOpportunity?->title ?: 'Bugünlük fırsat bulunmuyor' }}</p>
                                                        <a class="btn btn-ghost" href="{{ url("/contacts/{$item->id}") }}">Kişi Detayı</a>
                                                    </div>
                                                @elseif ($section['type'] === 'critical_opportunity')
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->title }}</h3>
                                                            <span class="muted">{{ $item->expected_close_date ?: 'Tarih yok' }}</span>
                                                        </div>
                                                        <p class="muted">
                                                            {{ $item->contact?->first_name }} {{ $item->contact?->last_name }}
                                                            @if ($item->contact?->company)
                                                                · {{ $item->contact->company->name }}
                                                            @endif
                                                        </p>
                                                        <p class="muted">Aşama: {{ $item->opportunityStage?->name ?: 'Belirsiz' }}</p>
                                                        <a class="btn btn-ghost" href="{{ url("/opportunities/{$item->id}") }}">Fırsat Detayı</a>
                                                    </div>
                                                @elseif ($section['type'] === 'due_follow_up')
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->summary }}</h3>
                                                            <span class="muted">{{ $item->follow_up_due_at?->format('d.m.Y H:i') ?: 'Tarih yok' }}</span>
                                                        </div>
                                                        <p class="muted">
                                                            {{ $item->contact?->first_name }} {{ $item->contact?->last_name }}
                                                            @if ($item->contact?->company)
                                                                · {{ $item->contact->company->name }}
                                                            @endif
                                                        </p>
                                                        <p class="muted">Kanal: {{ strtoupper($item->channel) }}</p>

                                                        <div class="inline-actions">
                                                            <a class="btn btn-ghost" href="{{ url("/contacts/{$item->contact_id}") }}">Kişi Detayı</a>

                                                            @can('update', $item)
                                                                <form method="POST" action="{{ url("/contact-interactions/{$item->id}/toggle-follow-up") }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="btn btn-secondary" type="submit">Tamamlandı Olarak İşaretle</button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="surface-stack" style="gap: 0.35rem;">
                                                        <div class="content-card__header">
                                                            <h3 class="content-card__title">{{ $item->title }}</h3>
                                                            <span class="muted">{{ $item->due_at?->format('d.m.Y H:i') ?: 'Termin yok' }}</span>
                                                        </div>
                                                        <p class="muted">{{ $item->opportunity?->title ?: 'Fırsat bilgisi yok' }}</p>
                                                        <p class="muted">
                                                            {{ $item->opportunity?->contact?->first_name }} {{ $item->opportunity?->contact?->last_name }}
                                                            @if ($item->opportunity?->contact?->company)
                                                                · {{ $item->opportunity->contact->company->name }}
                                                            @endif
                                                        </p>

                                                        @can('update', $item)
                                                            <div class="inline-actions">
                                                                <a class="btn btn-ghost" href="{{ url("/tasks/{$item->id}") }}">Görev Detayı</a>

                                                                <form method="POST" action="{{ url("/tasks/{$item->id}/toggle-complete") }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="btn btn-secondary" type="submit">Tamamlandı Olarak İşaretle</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <a class="btn btn-ghost" href="{{ url("/tasks/{$item->id}") }}">Görev Detayı</a>
                                                        @endcan
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
