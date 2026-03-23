@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 640px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Gorevler</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Gorev</h1>
                <p class="muted" style="margin: 0;">Firsata bagli bir takip gorevi olusturun ve is bitis zamanini belirleyin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ url('/tasks') }}" class="stack">
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
                    <label for="title">Gorev Basligi</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                </div>

                <div>
                    <label for="due_at">Termin</label>
                    <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at') }}">
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Gorevi Kaydet</button>
                    <a class="button" href="{{ url('/tasks') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                </div>
            </form>
        </div>
    </section>
@endsection
