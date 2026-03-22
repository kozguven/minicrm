@extends('layouts.app')

@section('content')
    @php
        $selectedPermissions = collect(old('permissions', $role->permissions->pluck('key')->all()));
    @endphp

    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Rolü Düzenle</h1>
                <p class="muted" style="margin: 0;">Rol adını ve atanmış izinleri güncelleyin.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url("/roles/{$role->id}") }}" class="stack">
                @csrf
                @method('PUT')

                <div>
                    <label for="name">Rol Adı</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $role->name) }}" required>
                </div>

                <div class="stack" style="gap: 0.75rem;">
                    <div>
                        <h2 style="margin: 0 0 0.35rem; font-size: 1.1rem;">İzinler</h2>
                        <p class="muted" style="margin: 0;">Modül bazlı görüntüleme, oluşturma, düzenleme ve dışa aktarma yetkilerini seçin.</p>
                    </div>

                    @forelse ($permissions as $permission)
                        <label style="display: flex; gap: 0.75rem; align-items: center; border: 1px solid var(--border); border-radius: 14px; padding: 0.85rem 1rem; margin: 0;">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->key }}"
                                style="width: auto;"
                                @checked($selectedPermissions->contains($permission->key))
                            >
                            <span>{{ $permission->key }}</span>
                        </label>
                    @empty
                        <p class="muted" style="margin: 0;">Seçilebilir izin bulunmuyor.</p>
                    @endforelse
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="button" type="submit">Değişiklikleri Kaydet</button>
                    <a class="button" href="{{ url('/roles') }}" style="background: #e5e7eb; color: var(--text);">Vazgeç</a>
                </div>
            </form>
        </div>
    </section>
@endsection
