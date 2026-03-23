@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 640px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Anlasmalar</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Anlasma</h1>
                <p class="muted" style="margin: 0;">Firsati secin, kapanis tutarini girin ve anlasmayi kaydedin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($opportunities->isEmpty())
                <p class="muted" style="margin: 0;">Anlasmaya donusturulecek uygun firsat bulunmuyor.</p>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a class="button" href="{{ url('/deals') }}">Anlasmalara Don</a>
                    <a class="button" href="{{ url('/opportunities') }}" style="background: #e5e7eb; color: var(--text);">Firsatlara Git</a>
                </div>
            @else
                <form method="POST" action="{{ url('/deals') }}" class="stack">
                    @csrf

                    <div>
                        <label for="opportunity_id">Firsat</label>
                        <select
                            id="opportunity_id"
                            name="opportunity_id"
                            required
                            style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem 0.95rem; font: inherit; background: #fff;"
                        >
                            <option value="">Firsat secin</option>
                            @foreach ($opportunities as $opportunity)
                                <option value="{{ $opportunity->id }}" @selected((string) old('opportunity_id') === (string) $opportunity->id)>
                                    {{ $opportunity->title }}
                                    @if ($opportunity->contact)
                                        - {{ $opportunity->contact->first_name }} {{ $opportunity->contact->last_name }}
                                    @endif
                                    @if ($opportunity->contact?->company)
                                        - {{ $opportunity->contact->company->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="amount">Anlasma Tutari</label>
                        <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}">
                    </div>

                    <div>
                        <label for="closed_at">Kapanis Tarihi</label>
                        <input id="closed_at" name="closed_at" type="datetime-local" value="{{ old('closed_at') }}">
                    </div>

                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button class="button" type="submit">Anlasmayi Kaydet</button>
                        <a class="button" href="{{ url('/deals') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
