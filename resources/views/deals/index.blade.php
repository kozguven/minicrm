@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 960px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Anlasmalar</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Anlasmalar</h1>
                    <p class="muted" style="margin: 0;">Kapanan satislari takip edin ve firsat donusumlerini tek ekranda izleyin.</p>
                </div>
                @can('create', \App\Models\Deal::class)
                    <a class="button" href="{{ url('/deals/create') }}">Yeni Anlasma</a>
                @endcan
            </div>

            @if (session('status'))
                <div style="padding: 0.9rem 1rem; border-radius: 14px; background: rgba(15, 118, 110, 0.12); color: var(--accent-strong);">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($deals->isEmpty())
                <p class="muted" style="margin: 0;">Henuz anlasma kaydi bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($deals as $deal)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                                    <div class="stack" style="gap: 0.35rem;">
                                        <h2 style="margin: 0; font-size: 1.15rem;">{{ $deal->opportunity?->title }}</h2>
                                        <p class="muted" style="margin: 0;">
                                            {{ $deal->opportunity?->contact?->first_name }} {{ $deal->opportunity?->contact?->last_name }}
                                            @if ($deal->opportunity?->contact?->company)
                                                · {{ $deal->opportunity->contact->company->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="stack" style="gap: 0.35rem; text-align: right;">
                                        <strong>
                                            @if ($deal->amount !== null)
                                                {{ number_format((float) $deal->amount, 2, ',', '.') }} TL
                                            @else
                                                Tutar bekleniyor
                                            @endif
                                        </strong>
                                        <span class="muted">{{ optional($deal->closed_at)->format('Y-m-d H:i') ?: 'Kapanis bekleniyor' }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
