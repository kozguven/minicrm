@extends('layouts.app')

@section('content')
    <x-ui.panel size="sm">
        <div class="surface-stack">
            <x-ui.page-header
                title="Giriş Yap"
                subtitle="Mini CRM hesabınıza erişmek için oturum açın."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">{{ $errors->first() }}</x-ui.notice>
            @endif

            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf

                <div class="field">
                    <label class="field-label" for="email">E-posta</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>

                <div class="field">
                    <label class="field-label" for="password">Şifre</label>
                    <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>

                <button class="btn btn-primary" type="submit">Giriş Yap</button>
            </form>
        </div>
    </x-ui.panel>
@endsection
