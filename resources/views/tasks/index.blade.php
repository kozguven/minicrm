@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Görevler"
                title="Görevler"
                subtitle="Hatırlatmaları takip edin, geciken işleri görünür hale getirin."
            >
                @can('create', \App\Models\CrmTask::class)
                    <a class="btn btn-primary" href="{{ url('/tasks/create') }}">Yeni Görev</a>
                @endcan
            </x-ui.page-header>

            @if (session('status'))
                <x-ui.notice tone="success">{{ session('status') }}</x-ui.notice>
            @endif

            @if ($tasks->isEmpty())
                <x-ui.empty-state>Henüz görev kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($tasks as $task)
                        @php
                            $isOverdue = $task->completed_at === null && $task->due_at !== null && $task->due_at->isPast();
                            $badgeText = $task->completed_at ? 'Tamamlandı' : ($isOverdue ? 'Gecikmiş' : 'Planlandı');
                            $badgeClass = $task->completed_at ? 'badge--success' : ($isOverdue ? 'badge--danger' : 'badge--info');
                        @endphp

                        <article class="content-card">
                            <div class="surface-stack" style="gap: 0.7rem;">
                                <div class="content-card__header">
                                    <div>
                                        <h2 class="content-card__title">{{ $task->title }}</h2>
                                        <p class="muted">
                                            {{ $task->opportunity?->title }}
                                            @if ($task->opportunity?->contact)
                                                · {{ $task->opportunity->contact->first_name }} {{ $task->opportunity->contact->last_name }}
                                            @endif
                                            @if ($task->opportunity?->contact?->company)
                                                · {{ $task->opportunity->contact->company->name }}
                                            @endif
                                        </p>
                                    </div>

                                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                </div>

                                <p class="muted">
                                    Termin: {{ $task->due_at?->format('d.m.Y H:i') ?? 'Belirlenmedi' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
