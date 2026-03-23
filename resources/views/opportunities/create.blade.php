@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 640px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Firsatlar</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Firsat</h1>
                <p class="muted" style="margin: 0;">Ilgili kisi, asama ve tahmini kapanis bilgisiyle yeni firsat ekleyin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ url('/opportunities') }}" class="stack">
                @csrf

                <div>
                    <label for="contact_id">Kisi</label>
                    <select
                        id="contact_id"
                        name="contact_id"
                        required
                        style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem 0.95rem; font: inherit; background: #fff;"
                    >
                        <option value="">Kisi secin</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected((string) old('contact_id') === (string) $contact->id)>
                                {{ $contact->first_name }} {{ $contact->last_name }}
                                @if ($contact->company)
                                    - {{ $contact->company->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="opportunity_stage_id">Asama</label>
                    <select
                        id="opportunity_stage_id"
                        name="opportunity_stage_id"
                        required
                        style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem 0.95rem; font: inherit; background: #fff;"
                    >
                        <option value="">Asama secin</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}" @selected((string) old('opportunity_stage_id') === (string) $stage->id)>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title">Firsat Basligi</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                </div>

                <div>
                    <label for="value">Tutar</label>
                    <input id="value" name="value" type="number" min="0" step="0.01" value="{{ old('value') }}" required>
                </div>

                <div>
                    <label for="expected_close_date">Beklenen Kapanis Tarihi</label>
                    <input id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date') }}">
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Firsati Kaydet</button>
                    <a class="button" href="{{ url('/opportunities') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                </div>
            </form>
        </div>
    </section>
@endsection
