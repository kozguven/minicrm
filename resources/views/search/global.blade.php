@extends('layouts.app')

@section('content')
    <x-ui.panel size="xl">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Arama"
                title="Global Arama"
                subtitle="Sirket, kisi, firsat ve gorev kayitlarini tek noktadan tarayin."
            >
                <a class="btn btn-secondary" href="{{ url('/today') }}">Gunume Don</a>
            </x-ui.page-header>

            <form method="GET" action="{{ url('/search/global') }}" class="inline-actions">
                <div class="field" style="flex: 1 1 380px;">
                    <label class="field-label" for="global-search">Arama ifadesi</label>
                    <input
                        class="input"
                        id="global-search"
                        name="q"
                        type="text"
                        value="{{ $query }}"
                        placeholder="Sirket, kisi, firsat veya gorev ara"
                    >
                </div>
                <button class="btn btn-primary" type="submit">Ara</button>
            </form>

            @php
                $hasResults = $results['companies']->isNotEmpty()
                    || $results['contacts']->isNotEmpty()
                    || $results['opportunities']->isNotEmpty()
                    || $results['tasks']->isNotEmpty();
            @endphp

            @if ($query === '')
                <x-ui.empty-state>Arama yapmak icin bir ifade girin.</x-ui.empty-state>
            @elseif (! $hasResults)
                <x-ui.empty-state>"{{ $query }}" icin sonuc bulunamadi.</x-ui.empty-state>
            @else
                <div class="content-list">
                    <article class="content-card">
                        <h2 class="section-title">Sirketler</h2>
                        @if ($results['companies']->isEmpty())
                            <p class="muted">Sonuc yok.</p>
                        @else
                            @foreach ($results['companies'] as $company)
                                <p class="muted">{{ $company->name }}</p>
                            @endforeach
                        @endif
                    </article>

                    <article class="content-card">
                        <h2 class="section-title">Kisiler</h2>
                        @if ($results['contacts']->isEmpty())
                            <p class="muted">Sonuc yok.</p>
                        @else
                            @foreach ($results['contacts'] as $contact)
                                <p class="muted">{{ $contact->first_name }} {{ $contact->last_name }}</p>
                            @endforeach
                        @endif
                    </article>

                    <article class="content-card">
                        <h2 class="section-title">Firsatlar</h2>
                        @if ($results['opportunities']->isEmpty())
                            <p class="muted">Sonuc yok.</p>
                        @else
                            @foreach ($results['opportunities'] as $opportunity)
                                <p class="muted">{{ $opportunity->title }}</p>
                            @endforeach
                        @endif
                    </article>

                    <article class="content-card">
                        <h2 class="section-title">Gorevler</h2>
                        @if ($results['tasks']->isEmpty())
                            <p class="muted">Sonuc yok.</p>
                        @else
                            @foreach ($results['tasks'] as $task)
                                <p class="muted">{{ $task->title }}</p>
                            @endforeach
                        @endif
                    </article>
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
