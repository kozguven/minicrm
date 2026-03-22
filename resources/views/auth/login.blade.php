@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="stack">
            <div>
                <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Giriş Yap</h1>
                <p class="muted" style="margin: 0;">Mini CRM hesabınıza erişmek için oturum açın.</p>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="stack">
                @csrf

                <div>
                    <label for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>

                <div>
                    <label for="password">Şifre</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>

                <button class="button" type="submit">Giriş Yap</button>
            </form>
        </div>
    </section>
@endsection
