@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Anlaşmalar"
                title="Anlaşmayı Düzenle"
                subtitle="Kapanış tutarı ve kapanış tarihini güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <article class="content-card">
                <p class="muted">
                    Fırsat: {{ $deal->opportunity?->title ?: 'Belirtilmedi' }}
                </p>
                <p class="muted">
                    Kişi:
                    @if ($deal->opportunity?->contact)
                        {{ $deal->opportunity->contact->first_name }} {{ $deal->opportunity->contact->last_name }}
                    @else
                        Belirtilmedi
                    @endif
                </p>
            </article>

            <form method="POST" action="{{ url("/deals/{$deal->id}") }}" class="form-stack">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="field-label" for="amount">Anlaşma Tutarı</label>
                    <input class="input" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $deal->amount) }}">
                </div>

                <div class="field">
                    <label class="field-label" for="closed_at">Kapanış Tarihi</label>
                    <input
                        class="input"
                        id="closed_at"
                        name="closed_at"
                        type="datetime-local"
                        value="{{ old('closed_at', optional($deal->closed_at)->format('Y-m-d\\TH:i')) }}"
                    >
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Anlaşmayı Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/deals') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
