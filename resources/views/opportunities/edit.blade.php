@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Fırsatlar"
                title="Fırsatı Düzenle"
                subtitle="Fırsat detaylarını güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <form method="POST" action="{{ url("/opportunities/{$opportunity->id}") }}" class="form-stack">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="field-label" for="contact_id">Kişi</label>
                    <select class="select" id="contact_id" name="contact_id" required>
                        <option value="">Kişi seçin</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected((string) old('contact_id', $opportunity->contact_id) === (string) $contact->id)>
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
                            <option value="{{ $stage->id }}" @selected((string) old('opportunity_stage_id', $opportunity->opportunity_stage_id) === (string) $stage->id)>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="title">Fırsat Başlığı</label>
                    <input class="input" id="title" name="title" type="text" value="{{ old('title', $opportunity->title) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="value">Tutar</label>
                    <input class="input" id="value" name="value" type="number" min="0" step="0.01" value="{{ old('value', $opportunity->value) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="expected_close_date">Beklenen Kapanış Tarihi</label>
                    <input
                        class="input"
                        id="expected_close_date"
                        name="expected_close_date"
                        type="date"
                        value="{{ old('expected_close_date', $opportunity->expected_close_date) }}"
                    >
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Fırsatı Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/opportunities') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
