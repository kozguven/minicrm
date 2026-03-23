<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini CRM') }}</title>

    @php
        $appStyles = file_get_contents(resource_path('css/app.css'));
    @endphp
    <style>{!! $appStyles !!}</style>
</head>
<body>
    @php
        $user = auth()->user();
        $isActive = static fn (array $patterns): bool => request()->is(...$patterns);

        $navItems = [];

        if ($user) {
            $navItems = [
                ['label' => 'Günüm', 'href' => '/today', 'patterns' => ['today']],
                ['label' => 'Dashboard', 'href' => '/dashboard', 'patterns' => ['dashboard']],
                ['label' => 'Şirketler', 'href' => '/companies', 'patterns' => ['companies', 'companies/*']],
                ['label' => 'Kişiler', 'href' => '/contacts', 'patterns' => ['contacts', 'contacts/*']],
                ['label' => 'Fırsatlar', 'href' => '/opportunities', 'patterns' => ['opportunities', 'opportunities/*']],
                ['label' => 'Görevler', 'href' => '/tasks', 'patterns' => ['tasks', 'tasks/*']],
                ['label' => 'Anlaşmalar', 'href' => '/deals', 'patterns' => ['deals', 'deals/*']],
            ];

            if ($user->isAdmin()) {
                $navItems[] = ['label' => 'Roller', 'href' => '/roles', 'patterns' => ['roles', 'roles/*']];
                $navItems[] = ['label' => 'Takım', 'href' => '/team', 'patterns' => ['team', 'team/*']];
            }
        }
    @endphp

    <div class="app-shell">
        <header class="app-header" @auth data-nav-shell="global" @endauth>
            <div class="app-header__inner">
                <div class="app-brand-row">
                    <a class="app-brand" href="{{ auth()->check() ? '/today' : '/login' }}">
                        <h1 class="app-brand__name">{{ config('app.name', 'Mini CRM') }}</h1>
                        <span class="app-brand__badge">Premium</span>
                    </a>

                    <div class="app-header__right">
                        @auth
                            @can('create', \App\Models\Opportunity::class)
                                <a class="btn btn-ghost" href="/opportunities/create">Yeni Fırsat</a>
                            @endcan

                            @can('create', \App\Models\CrmTask::class)
                                <a class="btn btn-ghost" href="/tasks/create">Yeni Görev</a>
                            @endcan

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-secondary" type="submit">Çıkış Yap</button>
                            </form>
                        @else
                            <a class="btn btn-primary" href="{{ route('login') }}">Giriş Yap</a>
                        @endauth
                    </div>
                </div>

                @auth
                    <nav class="top-nav" aria-label="Ana Menü">
                        @foreach ($navItems as $item)
                            @php
                                $itemIsActive = $isActive($item['patterns']);
                            @endphp

                            <a
                                class="top-nav-link {{ $itemIsActive ? 'is-active' : '' }}"
                                href="{{ $item['href'] }}"
                                @if ($itemIsActive) aria-current="page" @endif
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>
                @endauth
            </div>
        </header>

        <main class="{{ auth()->check() ? 'app-main' : 'auth-main' }}">
            @yield('content')
        </main>
    </div>
</body>
</html>
