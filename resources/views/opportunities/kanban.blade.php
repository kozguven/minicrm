@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Firsatlar"
                title="Pipeline Kanban"
                subtitle="Asama bazli gorunum ile firsatlari daha hizli yonetin."
            >
                <a class="btn btn-secondary" href="{{ url('/opportunities') }}">Liste Gorunumu</a>
            </x-ui.page-header>

            <div class="kanban-grid" data-kanban-board>
                @foreach ($stages as $stage)
                    <section class="kanban-column" data-stage-dropzone data-stage-id="{{ $stage->id }}">
                        <header class="kanban-column__header">
                            <h2 class="section-title">{{ $stage->name }}</h2>
                            <span class="badge badge--info">{{ $stage->opportunities->count() }}</span>
                        </header>

                        <div class="content-list">
                            @forelse ($stage->opportunities as $opportunity)
                                @php
                                    $canUpdate = auth()->user()?->can('update', $opportunity) ?? false;
                                @endphp
                                <article
                                    class="content-card {{ $canUpdate ? 'kanban-card kanban-card--draggable' : '' }}"
                                    data-opportunity-id="{{ $opportunity->id }}"
                                    @if ($canUpdate) draggable="true" @endif
                                >
                                    <h3 class="content-card__title">{{ $opportunity->title }}</h3>
                                    <p class="muted">
                                        {{ $opportunity->contact?->first_name }} {{ $opportunity->contact?->last_name }}
                                        @if ($opportunity->contact?->company)
                                            · {{ $opportunity->contact->company->name }}
                                        @endif
                                    </p>
                                    <div class="inline-actions" style="margin-top: 0.45rem;">
                                        <a class="btn btn-ghost" href="{{ url("/opportunities/{$opportunity->id}") }}">Detay</a>
                                    </div>
                                </article>
                            @empty
                                <x-ui.empty-state>Bu asamada firsat yok.</x-ui.empty-state>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </x-ui.panel>

    <script>
        (() => {
            const board = document.querySelector('[data-kanban-board]');
            if (!board) return;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) return;

            let draggedCard = null;

            board.querySelectorAll('[data-opportunity-id]').forEach((card) => {
                if (!card.classList.contains('kanban-card--draggable')) return;

                card.addEventListener('dragstart', () => {
                    draggedCard = card;
                    card.classList.add('is-dragging');
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                });
            });

            board.querySelectorAll('[data-stage-dropzone]').forEach((zone) => {
                zone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    zone.classList.add('is-drop-target');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('is-drop-target');
                });

                zone.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    zone.classList.remove('is-drop-target');

                    if (!draggedCard) return;

                    const opportunityId = draggedCard.getAttribute('data-opportunity-id');
                    const stageId = zone.getAttribute('data-stage-id');
                    if (!opportunityId || !stageId) return;

                    const response = await fetch(`/opportunities/${opportunityId}/stage`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            opportunity_stage_id: Number(stageId),
                        }),
                    });

                    if (!response.ok) {
                        alert('Asama guncellenemedi. Yetki veya dogrulama hatasi olabilir.');
                        return;
                    }

                    window.location.reload();
                });
            });
        })();
    </script>
@endsection
