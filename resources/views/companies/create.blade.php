@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 640px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Sirketler</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Sirket</h1>
                <p class="muted" style="margin: 0;">Sirket adi ve istege bagli web sitesi bilgisini girin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/companies') }}" class="stack">
                @csrf

                <div>
                    <label for="name">Sirket Adi</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div>
                    <label for="website">Website</label>
                    <input id="website" name="website" type="url" value="{{ old('website') }}">
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Sirketi Kaydet</button>
                    <a class="button" href="{{ url('/companies') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                </div>
            </form>
        </div>
    </section>
@endsection
