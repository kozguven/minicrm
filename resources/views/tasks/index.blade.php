@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 960px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Gorevler</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Gorevler</h1>
                    <p class="muted" style="margin: 0;">Hatirlatmalari takip edin, geciken isleri hizla gorunur hale getirin.</p>
                </div>
                @can('create', \App\Models\CrmTask::class)
                    <a class="button" href="{{ url('/tasks/create') }}">Yeni Gorev</a>
                @endcan
            </div>

            @if (session('status'))
                <div style="padding: 0.85rem 1rem; border-radius: 14px; background: #ecfdf5; color: #065f46; font-weight: 600;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($tasks->isEmpty())
                <p class="muted" style="margin: 0;">Henuz gorev kaydi bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($tasks as $task)
                        @php
                            $isOverdue = $task->completed_at === null && $task->due_at !== null && $task->due_at->isPast();
                            $badgeText = $task->completed_at ? 'Tamamlandi' : ($isOverdue ? 'Gecikmis' : 'Planlandi');
                            $badgeBackground = $task->completed_at ? '#ecfdf5' : ($isOverdue ? '#fef2f2' : '#eff6ff');
                            $badgeColor = $task->completed_at ? '#065f46' : ($isOverdue ? '#b91c1c' : '#1d4ed8');
                        @endphp

                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                                    <div class="stack" style="gap: 0.35rem;">
                                        <h2 style="margin: 0; font-size: 1.15rem;">{{ $task->title }}</h2>
                                        <p class="muted" style="margin: 0;">
                                            {{ $task->opportunity?->title }}
                                            @if ($task->opportunity?->contact)
                                                · {{ $task->opportunity->contact->first_name }} {{ $task->opportunity->contact->last_name }}
                                            @endif
                                            @if ($task->opportunity?->contact?->company)
                                                · {{ $task->opportunity->contact->company->name }}
                                            @endif
                                        </p>
                                    </div>

                                    <span style="display: inline-flex; align-items: center; border-radius: 999px; padding: 0.45rem 0.8rem; background: {{ $badgeBackground }}; color: {{ $badgeColor }}; font-weight: 700;">
                                        {{ $badgeText }}
                                    </span>
                                </div>

                                <p class="muted" style="margin: 0;">
                                    Termin:
                                    {{ $task->due_at?->format('d.m.Y H:i') ?? 'Belirlenmedi' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
