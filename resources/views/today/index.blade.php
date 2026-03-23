@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 980px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Gunum</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Today</h1>
                    <p class="muted" style="margin: 0;">Gunum icin once hangi isi ele almaniz gerektigini tek ekranda gorun.</p>
                </div>
                <a class="button" href="/dashboard">Dashboard</a>
            </div>

            @if (session('status'))
                <div style="padding: 0.85rem 1rem; border-radius: 14px; background: #ecfdf5; color: #065f46; font-weight: 600;">
                    {{ session('status') }}
                </div>
            @endif

            @if (! $canViewCrm)
                <section style="border: 1px solid var(--border); border-radius: 18px; padding: 1.25rem; background: #fffbeb;">
                    <div class="stack" style="gap: 0.5rem;">
                        <h2 style="margin: 0; font-size: 1.2rem;">Yetki Gerekli</h2>
                        <p class="muted" style="margin: 0;">{{ $permissionMessage }}</p>
                    </div>
                </section>
            @else
                <div class="stack">
                    @foreach ($sections as $section)
                        <section style="border: 1px solid var(--border); border-radius: 18px; padding: 1.1rem;">
                            <div class="stack" style="gap: 0.9rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                    <div>
                                        <h2 style="margin: 0; font-size: 1.2rem;">{{ $section['title'] }}</h2>
                                        <p class="muted" style="margin: 0.35rem 0 0;">{{ $section['items']->count() }} kayit</p>
                                    </div>
                                </div>

                                @if ($section['items']->isEmpty())
                                    <p class="muted" style="margin: 0;">{{ $section['empty_message'] }}</p>
                                @else
                                    <div class="stack" style="gap: 0.85rem;">
                                        @foreach ($section['items'] as $item)
                                            <article style="border: 1px solid var(--border); border-radius: 14px; padding: 0.95rem;">
                                                @if ($section['type'] === 'call')
                                                    @php
                                                        $todayOpportunity = $item->opportunities->first();
                                                    @endphp

                                                    <div class="stack" style="gap: 0.35rem;">
                                                        <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                                            <h3 style="margin: 0; font-size: 1.05rem;">{{ $item->first_name }} {{ $item->last_name }}</h3>
                                                            <span class="muted">{{ $item->phone }}</span>
                                                        </div>
                                                        <p class="muted" style="margin: 0;">
                                                            {{ $item->company?->name ?: 'Sirket bilgisi yok' }}
                                                        </p>
                                                        <p class="muted" style="margin: 0;">
                                                            {{ $todayOpportunity?->title ?: 'Bugunluk firsat bulunmuyor' }}
                                                        </p>
                                                    </div>
                                                @elseif ($section['type'] === 'critical_opportunity')
                                                    <div class="stack" style="gap: 0.35rem;">
                                                        <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                                            <h3 style="margin: 0; font-size: 1.05rem;">{{ $item->title }}</h3>
                                                            <span class="muted">{{ $item->expected_close_date ?: 'Tarih yok' }}</span>
                                                        </div>
                                                        <p class="muted" style="margin: 0;">
                                                            {{ $item->contact?->first_name }} {{ $item->contact?->last_name }}
                                                            @if ($item->contact?->company)
                                                                · {{ $item->contact->company->name }}
                                                            @endif
                                                        </p>
                                                        <p class="muted" style="margin: 0;">
                                                            Asama: {{ $item->opportunityStage?->name ?: 'Belirsiz' }}
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="stack" style="gap: 0.35rem;">
                                                        <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                                            <h3 style="margin: 0; font-size: 1.05rem;">{{ $item->title }}</h3>
                                                            <span class="muted">{{ $item->due_at?->format('d.m.Y H:i') ?: 'Termin yok' }}</span>
                                                        </div>
                                                        <p class="muted" style="margin: 0;">
                                                            {{ $item->opportunity?->title ?: 'Firsat bilgisi yok' }}
                                                        </p>
                                                        <p class="muted" style="margin: 0;">
                                                            {{ $item->opportunity?->contact?->first_name }} {{ $item->opportunity?->contact?->last_name }}
                                                            @if ($item->opportunity?->contact?->company)
                                                                · {{ $item->opportunity->contact->company->name }}
                                                            @endif
                                                        </p>
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
    </section>
@endsection
