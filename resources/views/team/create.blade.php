@extends('layouts.app')

@section('content')
    @php
        $selectedRoleIds = collect(old('role_ids', []))->map(fn ($roleId) => (int) $roleId);
    @endphp

    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="Yönetim / Takım"
                title="Yeni Takım Üyesi"
                subtitle="Ad, e-posta, şifre ve bir veya daha fazla rol belirleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">{{ $errors->first() }}</x-ui.notice>
            @endif

            <form method="POST" action="{{ url('/team') }}" class="form-stack">
                @csrf

                <div class="field">
                    <label class="field-label" for="name">Ad Soyad</label>
                    <input class="input" id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="email">E-posta</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="password">Şifre</label>
                    <input class="input" id="password" name="password" type="password" required>
                </div>

                <section class="form-stack">
                    <h2 class="section-title">Roller</h2>
                    <p class="muted">Üyeye en az bir rol atayın.</p>

                    @forelse ($roles as $role)
                        <label class="checkbox-row">
                            <input
                                class="checkbox"
                                type="checkbox"
                                name="role_ids[]"
                                value="{{ $role->id }}"
                                @checked($selectedRoleIds->contains($role->id))
                            >
                            <span>{{ $role->name }}</span>
                        </label>
                    @empty
                        <x-ui.empty-state>Atanabilir rol bulunmuyor.</x-ui.empty-state>
                    @endforelse
                </section>

                <div class="inline-actions form-actions">
                    <button class="btn btn-primary" type="submit">Takım Üyesini Kaydet</button>
                    <a class="btn btn-secondary" href="{{ url('/team') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
