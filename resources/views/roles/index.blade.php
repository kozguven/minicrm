@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Roller ve İzinler</h1>
                    <p class="muted" style="margin: 0;">Ekip rollerini yönetin ve modül bazlı aksiyon izinlerini eşleyin.</p>
                </div>
                <a class="button" href="{{ url('/roles/create') }}">Yeni Rol</a>
            </div>

            @if ($roles->isEmpty())
                <p class="muted" style="margin: 0;">Henüz rol tanımlanmadı.</p>
            @else
                <div class="stack">
                    @foreach ($roles as $role)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                <div>
                                    <h2 style="margin: 0 0 0.35rem; font-size: 1.15rem;">{{ $role->name }}</h2>
                                    <p class="muted" style="margin: 0;">
                                        {{ $role->permissions->sortBy('key')->pluck('key')->join(', ') ?: 'İzin atanmadı' }}
                                    </p>
                                </div>
                                <a class="button" href="{{ url("/roles/{$role->id}/edit") }}">Düzenle</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
