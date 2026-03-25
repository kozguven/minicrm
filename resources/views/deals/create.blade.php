@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Anlaşmalar"
                title="Yeni Anlaşma"
                subtitle="Fırsatı seçin, kapanış tutarını girin ve anlaşmayı kaydedin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            @if ($opportunities->isEmpty())
                <x-ui.empty-state>Anlaşmaya dönüştürülecek uygun fırsat bulunmuyor.</x-ui.empty-state>

                <div class="inline-actions">
                    <a class="btn btn-primary" href="{{ url('/deals') }}">Anlaşmalara Dön</a>
                    <a class="btn btn-secondary" href="{{ url('/opportunities') }}">Fırsatlara Git</a>
                </div>
            @else
                <form method="POST" action="{{ url('/deals') }}" class="form-stack">
                    @csrf

                    <div class="field">
                        <label class="field-label" for="opportunity_id">Fırsat</label>
                        <select class="select" id="opportunity_id" name="opportunity_id" required>
                            <option value="">Fırsat seçin</option>
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

                    <div class="field">
                        <label class="field-label" for="amount">Anlaşma Tutarı</label>
                        <input class="input" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}">
                    </div>

                    <div class="field">
                        <label class="field-label" for="closed_at">Kapanış Tarihi</label>
                        <input class="input" id="closed_at" name="closed_at" type="datetime-local" value="{{ old('closed_at') }}">
                    </div>

                    <div class="inline-actions form-actions">
                        <button class="btn btn-primary" type="submit">Anlaşmayı Kaydet</button>
                        <a class="btn btn-secondary" href="{{ url('/deals') }}">Vazgeç</a>
                    </div>
                </form>
            @endif
        </div>
    </x-ui.panel>
@endsection
