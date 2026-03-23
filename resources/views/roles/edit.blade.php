@extends('layouts.app')

@section('content')
    @php
        $selectedPermissions = collect(old('permissions', $role->permissions->pluck('key')->all()));
    @endphp

    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="Yönetim / Roller"
                title="Rolü Düzenle"
                subtitle="Rol adını ve atanmış izinleri güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">{{ $errors->first() }}</x-ui.notice>
            @endif

            <form method="POST" action="{{ url("/roles/{$role->id}") }}" class="form-stack">
                @csrf
                @method('PUT')

                <div class="field">
                    <label class="field-label" for="name">Rol Adı</label>
                    <input class="input" id="name" name="name" type="text" value="{{ old('name', $role->name) }}" required>
                </div>

                <section class="form-stack">
                    <h2 class="section-title">İzinler</h2>
                    <p class="muted">Modül bazlı görüntüleme, oluşturma, düzenleme ve dışa aktarma yetkilerini seçin.</p>

                    @forelse ($permissions as $permission)
                        <label class="checkbox-row">
                            <input
                                class="checkbox"
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->key }}"
                                @checked($selectedPermissions->contains($permission->key))
                            >
                            <span>{{ $permission->key }}</span>
                        </label>
                    @empty
                        <x-ui.empty-state>Seçilebilir izin bulunmuyor.</x-ui.empty-state>
                    @endforelse
                </section>

                <div class="inline-actions">
                    <button class="btn btn-primary" type="submit">Değişiklikleri Kaydet</button>
                    <a class="btn btn-secondary" href="{{ url('/roles') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
