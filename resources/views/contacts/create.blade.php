@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 640px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Kisiler</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Kisi</h1>
                <p class="muted" style="margin: 0;">Bir sirket secin ve kisi bilgilerini kaydedin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/contacts') }}" class="stack">
                @csrf

                <div>
                    <label for="company_id">Sirket</label>
                    <select
                        id="company_id"
                        name="company_id"
                        required
                        style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem 0.95rem; font: inherit; background: #fff;"
                    >
                        <option value="">Sirket secin</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) old('company_id') === (string) $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="first_name">Ad</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required>
                </div>

                <div>
                    <label for="last_name">Soyad</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required>
                </div>

                <div>
                    <label for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}">
                </div>

                <div>
                    <label for="phone">Telefon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Kisiyi Kaydet</button>
                    <a class="button" href="{{ url('/contacts') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                </div>
            </form>
        </div>
    </section>
@endsection
