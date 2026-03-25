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

                <div class="field">
                    <label class="field-label" for="owner_user_id">Sorumlu Kullanıcı</label>
                    <select class="select" id="owner_user_id" name="owner_user_id">
                        <option value="">Atama yok</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('owner_user_id', $contact->owner_user_id) === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="lead_source">Lead Kaynağı</label>
                    <input class="input" id="lead_source" name="lead_source" type="text" value="{{ old('lead_source', $contact->lead_source) }}">
                </div>

                <div class="field">
                    <label class="field-label" for="lead_status">Lead Durumu</label>
                    <select class="select" id="lead_status" name="lead_status">
                        <option value="new" @selected(old('lead_status', $contact->lead_status) === 'new')>Yeni</option>
                        <option value="contacted" @selected(old('lead_status', $contact->lead_status) === 'contacted')>Temas Kuruldu</option>
                        <option value="qualified" @selected(old('lead_status', $contact->lead_status) === 'qualified')>Nitelikli</option>
                        <option value="lost" @selected(old('lead_status', $contact->lead_status) === 'lost')>Kaybedildi</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="priority">Öncelik</label>
                    <select class="select" id="priority" name="priority">
                        <option value="low" @selected(old('priority', $contact->priority) === 'low')>Düşük</option>
                        <option value="medium" @selected(old('priority', $contact->priority) === 'medium')>Orta</option>
                        <option value="high" @selected(old('priority', $contact->priority) === 'high')>Yüksek</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="last_contacted_at">Son Temas Tarihi</label>
                    <input
                        class="input"
                        id="last_contacted_at"
                        name="last_contacted_at"
                        type="datetime-local"
                        value="{{ old('last_contacted_at', optional($contact->last_contacted_at)->format('Y-m-d\\TH:i')) }}"
                    >
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Kişiyi Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/contacts') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
