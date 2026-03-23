@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="Yönetim / Roller"
                title="Roller ve İzinler"
                subtitle="Ekip rollerini yönetin ve modül bazlı aksiyon izinlerini eşleyin."
            >
                <a class="btn btn-primary" href="{{ url('/roles/create') }}">Yeni Rol</a>
            </x-ui.page-header>

            @if ($roles->isEmpty())
                <x-ui.empty-state>Henüz rol tanımlanmadı.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($roles as $role)
                        <article class="content-card">
                            <div class="content-card__header">
                                <div>
                                    <h2 class="content-card__title">{{ $role->name }}</h2>
                                    <p class="muted">{{ $role->permissions->sortBy('key')->pluck('key')->join(', ') ?: 'İzin atanmadı' }}</p>
                                </div>
                                <a class="btn btn-secondary" href="{{ url("/roles/{$role->id}/edit") }}">Düzenle</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
