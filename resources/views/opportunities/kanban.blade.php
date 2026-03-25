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

            <div class="kanban-grid">
                @foreach ($stages as $stage)
                    <section class="kanban-column">
                        <header class="kanban-column__header">
                            <h2 class="section-title">{{ $stage->name }}</h2>
                            <span class="badge badge--info">{{ $stage->opportunities->count() }}</span>
                        </header>

                        <div class="content-list">
                            @forelse ($stage->opportunities as $opportunity)
                                <article class="content-card" draggable="true">
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
@endsection
