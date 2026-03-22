@extends('layouts.app')

@section('content')
    @php
        $selectedRoles = collect(old('roles', []));
    @endphp

    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div>
                <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">Yonetim &gt; Takim</p>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Yeni Takim Uyesi</h1>
                <p class="muted" style="margin: 0;">Ad, e-posta, sifre ve bir veya daha fazla rol belirleyin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/team') }}" class="stack">
                @csrf

                <div>
                    <label for="name">Ad Soyad</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div>
                    <label for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div>
                    <label for="password">Sifre</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="stack" style="gap: 0.75rem;">
                    <div>
                        <h2 style="margin: 0 0 0.35rem; font-size: 1.1rem;">Roller</h2>
                        <p class="muted" style="margin: 0;">Uyeye en az bir rol atayin.</p>
                    </div>

                    @forelse ($roles as $role)
                        <label style="display: flex; gap: 0.75rem; align-items: center; border: 1px solid var(--border); border-radius: 14px; padding: 0.85rem 1rem; margin: 0;">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->name }}"
                                style="width: auto;"
                                @checked($selectedRoles->contains($role->name))
                            >
                            <span>{{ $role->name }}</span>
                        </label>
                    @empty
                        <p class="muted" style="margin: 0;">Atanabilir rol bulunmuyor.</p>
                    @endforelse
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Takim Uyesini Kaydet</button>
                    <a class="button" href="{{ url('/team') }}" style="background: #e5e7eb; color: var(--text);">Vazgec</a>
                </div>
            </form>
        </div>
    </section>
@endsection
