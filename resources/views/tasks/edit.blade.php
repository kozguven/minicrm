@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Görevler"
                title="Görevi Düzenle"
                subtitle="Görev başlığını, ilişkili fırsatı ve termin bilgisini güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <form method="POST" action="{{ url("/tasks/{$task->id}") }}" class="form-stack">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="field-label" for="opportunity_id">Fırsat</label>
                    <select class="select" id="opportunity_id" name="opportunity_id" required>
                        <option value="">Fırsat seçin</option>
                        @foreach ($opportunities as $opportunity)
                            <option value="{{ $opportunity->id }}" @selected((string) old('opportunity_id', $task->opportunity_id) === (string) $opportunity->id)>
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
                    <label class="field-label" for="title">Görev Başlığı</label>
                    <input class="input" id="title" name="title" type="text" value="{{ old('title', $task->title) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="due_at">Termin</label>
                    <input
                        class="input"
                        id="due_at"
                        name="due_at"
                        type="datetime-local"
                        value="{{ old('due_at', optional($task->due_at)->format('Y-m-d\\TH:i')) }}"
                    >
                </div>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Görevi Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/tasks') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
