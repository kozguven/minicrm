@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Fırsatlar"
                title="Yeni Fırsat"
                subtitle="İlgili kişi, aşama ve tahmini kapanış bilgisiyle yeni fırsat ekleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <form method="POST" action="{{ url('/opportunities') }}" class="form-stack">
                @csrf

                <div class="field">
                    <label class="field-label" for="contact_id">Kişi</label>
                    <select class="select" id="contact_id" name="contact_id" required>
                        <option value="">Kişi seçin</option>
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

                <div class="field">
                    <label class="field-label" for="opportunity_stage_id">Aşama</label>
                    <select class="select" id="opportunity_stage_id" name="opportunity_stage_id" required>
                        <option value="">Aşama seçin</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}" @selected((string) old('opportunity_stage_id') === (string) $stage->id)>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="title">Fırsat Başlığı</label>
                    <input class="input" id="title" name="title" type="text" value="{{ old('title') }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="value">Tutar</label>
                    <input class="input" id="value" name="value" type="number" min="0" step="0.01" value="{{ old('value') }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="owner_user_id">Sorumlu Kullanıcı</label>
                    <select class="select" id="owner_user_id" name="owner_user_id">
                        <option value="">Atama yok</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('owner_user_id') === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="probability">Olasılık (%)</label>
                    <input class="input" id="probability" name="probability" type="number" min="0" max="100" value="{{ old('probability', 50) }}">
                </div>

                <div class="field">
                    <label class="field-label" for="expected_close_date">Beklenen Kapanış Tarihi</label>
                    <input class="input" id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date') }}">
                </div>

                <div class="field">
                    <label class="field-label" for="next_step">Sonraki Adım</label>
                    <input class="input" id="next_step" name="next_step" type="text" value="{{ old('next_step') }}">
                </div>

                <div class="field">
                    <label class="field-label" for="next_step_due_at">Sonraki Adım Tarihi</label>
                    <input class="input" id="next_step_due_at" name="next_step_due_at" type="datetime-local" value="{{ old('next_step_due_at') }}">
                </div>

                <div class="field">
                    <label class="field-label" for="health_status">Sağlık Durumu</label>
                    <select class="select" id="health_status" name="health_status">
                        <option value="commit" @selected(old('health_status') === 'commit')>Commit</option>
                        <option value="watch" @selected(old('health_status', 'watch') === 'watch')>Watch</option>
                        <option value="risk" @selected(old('health_status') === 'risk')>Risk</option>
                    </select>
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Fırsatı Kaydet</button>
                    <a class="btn btn-secondary" href="{{ url('/opportunities') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
