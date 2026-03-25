@extends('layouts.app')

@section('content')
    @php
        $isOverdue = $task->completed_at === null && $task->due_at !== null && $task->due_at->isPast();
        $badgeText = $task->completed_at ? 'Tamamlandı' : ($isOverdue ? 'Gecikmiş' : 'Planlandı');
        $badgeClass = $task->completed_at ? 'badge--success' : ($isOverdue ? 'badge--danger' : 'badge--info');
    @endphp

    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Görevler"
                title="Görev Detayı"
                subtitle="Görevin durumunu, terminini ve bağlı fırsat bilgisini yönetin."
            >
                @can('update', $task)
                    <a class="btn btn-ghost" href="{{ url("/tasks/{$task->id}/edit") }}">Düzenle</a>
                @endcan
                <a class="btn btn-secondary" href="{{ url('/tasks') }}">Listeye Dön</a>
            </x-ui.page-header>

            @if (session('status'))
                <x-ui.notice tone="success">{{ session('status') }}</x-ui.notice>
            @endif

            <article class="content-card">
                <div class="content-card__header">
                    <h2 class="content-card__title">{{ $task->title }}</h2>
                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                </div>
                <p class="muted">Termin: {{ $task->due_at?->format('d.m.Y H:i') ?: 'Belirlenmedi' }}</p>
                <p class="muted">
                    Fırsat:
                    @if ($task->opportunity)
                        <a href="{{ url("/opportunities/{$task->opportunity->id}") }}">{{ $task->opportunity->title }}</a>
                    @else
                        Belirtilmedi
                    @endif
                </p>
                @if ($task->opportunity?->contact)
                    <p class="muted">
                        Kişi:
                        <a href="{{ url("/contacts/{$task->opportunity->contact->id}") }}">
                            {{ $task->opportunity->contact->first_name }} {{ $task->opportunity->contact->last_name }}
                        </a>
                    </p>
                @endif
                @if ($task->opportunity?->contact?->company)
                    <p class="muted">
                        Şirket:
                        <a href="{{ url("/companies/{$task->opportunity->contact->company->id}") }}">
                            {{ $task->opportunity->contact->company->name }}
                        </a>
                    </p>
                @endif

                @can('update', $task)
                    <form method="POST" action="{{ url("/tasks/{$task->id}/toggle-complete") }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-secondary" type="submit">
                            {{ $task->completed_at ? 'Tekrar Aç' : 'Tamamlandı Olarak İşaretle' }}
                        </button>
                    </form>
                @endcan
            </article>
        </div>
    </x-ui.panel>
@endsection
