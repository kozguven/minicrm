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

            <form method="GET" action="{{ url('/tasks') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 240px;">
                    <label class="field-label" for="task-search">Arama</label>
                    <input
                        class="input"
                        id="task-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] }}"
                        placeholder="Görev, fırsat, kişi veya şirket ara"
                    >
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="task-status">Durum</label>
                    <select class="select" id="task-status" name="status">
                        <option value="all" @selected($filters['status'] === 'all')>Tümü</option>
                        <option value="open" @selected($filters['status'] === 'open')>Açık</option>
                        <option value="overdue" @selected($filters['status'] === 'overdue')>Gecikmiş</option>
                        <option value="completed" @selected($filters['status'] === 'completed')>Tamamlandı</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="task-priority">Öncelik</label>
                    <select class="select" id="task-priority" name="priority">
                        <option value="all" @selected($filters['priority'] === 'all')>Tümü</option>
                        <option value="high" @selected($filters['priority'] === 'high')>Yüksek</option>
                        <option value="medium" @selected($filters['priority'] === 'medium')>Orta</option>
                        <option value="low" @selected($filters['priority'] === 'low')>Düşük</option>
                    </select>
                </div>

                <div class="field" style="flex: 1 1 180px;">
                    <label class="field-label" for="task-sort">Sıralama</label>
                    <select class="select" id="task-sort" name="sort">
                        <option value="due_asc" @selected($filters['sort'] === 'due_asc')>Termin (Yakın)</option>
                        <option value="due_desc" @selected($filters['sort'] === 'due_desc')>Termin (Uzak)</option>
                        <option value="priority_desc" @selected($filters['sort'] === 'priority_desc')>Öncelik</option>
                        <option value="title_asc" @selected($filters['sort'] === 'title_asc')>Başlık (A-Z)</option>
                    </select>
                </div>

                <button class="btn btn-secondary" type="submit">Uygula</button>
                @if ($filters['q'] !== '' || $filters['status'] !== 'all' || $filters['priority'] !== 'all' || $filters['sort'] !== 'due_asc')
                    <a class="btn btn-ghost" href="{{ url('/tasks') }}">Temizle</a>
                @endif
            </form>

            @can('create', \App\Models\CrmTask::class)
                <form id="bulk-task-form" method="POST" action="{{ url('/tasks/bulk') }}" class="inline-actions">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="complete" id="bulk-task-action-input">
                    <button class="btn btn-secondary" type="submit" onclick="document.getElementById('bulk-task-action-input').value='complete'">
                        Seçilileri Tamamla
                    </button>
                    <button class="btn btn-ghost" type="submit" onclick="document.getElementById('bulk-task-action-input').value='reopen'">
                        Seçilileri Yeniden Aç
                    </button>
                </form>
            @endcan

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
                                        @can('update', $task)
                                            <label class="checkbox-row" style="margin-bottom: 0.35rem;">
                                                <input
                                                    class="checkbox"
                                                    type="checkbox"
                                                    name="task_ids[]"
                                                    value="{{ $task->id }}"
                                                    form="bulk-task-form"
                                                >
                                                <span>Toplu işleme ekle</span>
                                            </label>
                                        @endcan

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
                                <p class="muted">Öncelik: {{ strtoupper($task->priority ?? 'medium') }}</p>

                                @can('update', $task)
                                    <div class="inline-actions">
                                        <a class="btn btn-ghost" href="{{ url("/tasks/{$task->id}") }}">Detay</a>
                                        <a class="btn btn-ghost" href="{{ url("/tasks/{$task->id}/edit") }}">Düzenle</a>

                                        <form method="POST" action="{{ url("/tasks/{$task->id}/toggle-complete") }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-secondary" type="submit">
                                                {{ $task->completed_at ? 'Tekrar Aç' : 'Tamamlandı Olarak İşaretle' }}
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a class="btn btn-ghost" href="{{ url("/tasks/{$task->id}") }}">Detay</a>
                                @endcan
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $tasks->links() }}
            @endif
        </div>
    </x-ui.panel>
@endsection
