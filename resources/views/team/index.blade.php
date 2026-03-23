@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="Yönetim / Takım"
                title="Takım Yönetimi"
                subtitle="Ekip üyelerini oluşturun ve rollerini tek ekrandan takip edin."
            >
                <a class="btn btn-primary" href="{{ url('/team/create') }}">Yeni Takım Üyesi</a>
            </x-ui.page-header>

            @if ($teamMembers->isEmpty())
                <x-ui.empty-state>Henüz ekip üyesi bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($teamMembers as $teamMember)
                        <article class="content-card">
                            <div class="content-card__header">
                                <h2 class="content-card__title">{{ $teamMember->name }}</h2>
                                <span class="muted">{{ $teamMember->email }}</span>
                            </div>
                            <p class="muted">{{ $teamMember->roles->pluck('name')->join(', ') ?: 'Rol atanmadı' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
