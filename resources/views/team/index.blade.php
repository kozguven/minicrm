@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">Yonetim &gt; Takim</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Takim Yonetimi</h1>
                    <p class="muted" style="margin: 0;">Ekip uyelerini olusturun ve rollerini tek ekrandan takip edin.</p>
                </div>
                <a class="button" href="{{ url('/team/create') }}">Yeni Takim Uyesi</a>
            </div>

            @if ($teamMembers->isEmpty())
                <p class="muted" style="margin: 0;">Henüz ekip üyesi bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($teamMembers as $teamMember)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.35rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                    <h2 style="margin: 0; font-size: 1.15rem;">{{ $teamMember->name }}</h2>
                                    <span class="muted">{{ $teamMember->email }}</span>
                                </div>
                                <p class="muted" style="margin: 0;">
                                    {{ $teamMember->roles->pluck('name')->join(', ') ?: 'Rol atanmadı' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
