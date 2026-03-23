@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 960px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Firsatlar</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Firsatlar</h1>
                    <p class="muted" style="margin: 0;">Potansiyel satislari asama bazli takip edin ve boru hattini guncel tutun.</p>
                </div>
                <a class="button" href="{{ url('/opportunities/create') }}">Yeni Firsat</a>
            </div>

            @if ($opportunities->isEmpty())
                <p class="muted" style="margin: 0;">Henuz firsat kaydi bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($opportunities as $opportunity)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                                    <div class="stack" style="gap: 0.35rem;">
                                        <h2 style="margin: 0; font-size: 1.15rem;">{{ $opportunity->title }}</h2>
                                        <p class="muted" style="margin: 0;">
                                            {{ $opportunity->contact?->first_name }} {{ $opportunity->contact?->last_name }}
                                            @if ($opportunity->contact?->company)
                                                · {{ $opportunity->contact->company->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="stack" style="gap: 0.35rem; text-align: right;">
                                        <span class="muted">{{ $opportunity->opportunityStage?->name }}</span>
                                        <strong>{{ number_format((float) $opportunity->value, 2, ',', '.') }} TL</strong>
                                    </div>
                                </div>

                                <p class="muted" style="margin: 0;">
                                    Beklenen kapanis: {{ $opportunity->expected_close_date ?: 'Belirlenmedi' }}
                                </p>

                                @can('update', $opportunity)
                                    <form method="POST" action="{{ url("/opportunities/{$opportunity->id}/stage") }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
                                        @csrf
                                        @method('PATCH')

                                        <div style="flex: 1 1 220px;">
                                            <label for="stage-{{ $opportunity->id }}">Asama</label>
                                            <select
                                                id="stage-{{ $opportunity->id }}"
                                                name="opportunity_stage_id"
                                                style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem 0.95rem; font: inherit; background: #fff;"
                                            >
                                                @foreach ($stages as $stage)
                                                    <option value="{{ $stage->id }}" @selected($opportunity->opportunity_stage_id === $stage->id)>
                                                        {{ $stage->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button class="button" type="submit">Asamayi Guncelle</button>
                                    </form>
                                @endcan
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
