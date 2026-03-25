@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Kişiler"
                title="Kişiyi Düzenle"
                subtitle="Kişi bilgilerini ve bağlı şirketi güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <form method="POST" action="{{ url("/contacts/{$contact->id}") }}" class="form-stack">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="field-label" for="company_id">Şirket</label>
                    <select class="select" id="company_id" name="company_id" required>
                        <option value="">Şirket seçin</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) old('company_id', $contact->company_id) === (string) $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="first_name">Ad</label>
                    <input class="input" id="first_name" name="first_name" type="text" value="{{ old('first_name', $contact->first_name) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="last_name">Soyad</label>
                    <input class="input" id="last_name" name="last_name" type="text" value="{{ old('last_name', $contact->last_name) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="email">E-posta</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email', $contact->email) }}">
                </div>

                <div class="field">
                    <label class="field-label" for="phone">Telefon</label>
                    <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $contact->phone) }}">
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Kişiyi Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/contacts') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
