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
                ['label' => 'Raporlar', 'href' => '/reports/pipeline', 'patterns' => ['reports', 'reports/*']],
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
                            <button class="btn btn-ghost" type="button" data-command-open>Ctrl/Cmd + K</button>

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

    @auth
        <div class="command-palette" data-command-palette hidden>
            <div class="command-palette__backdrop" data-command-close></div>
            <div class="command-palette__panel" role="dialog" aria-modal="true" aria-label="Komut Paleti">
                <div class="field">
                    <label class="field-label" for="command-palette-search">Hızlı Komut</label>
                    <input
                        class="input"
                        id="command-palette-search"
                        type="text"
                        placeholder="Komut veya sayfa ara..."
                        data-command-input
                    >
                </div>

                <div class="command-palette__list" data-command-list>
                    <a class="command-palette__item" href="/search/global" data-command-item>Global Arama</a>

                    @can('create', \App\Models\Company::class)
                        <a class="command-palette__item" href="/companies/create" data-command-item>Yeni Şirket</a>
                    @endcan
                    @can('create', \App\Models\Contact::class)
                        <a class="command-palette__item" href="/contacts/create" data-command-item>Yeni Kişi</a>
                    @endcan
                    @can('create', \App\Models\Opportunity::class)
                        <a class="command-palette__item" href="/opportunities/create" data-command-item>Yeni Fırsat</a>
                    @endcan
                    @can('create', \App\Models\CrmTask::class)
                        <a class="command-palette__item" href="/tasks/create" data-command-item>Yeni Görev</a>
                    @endcan

                    <a class="command-palette__item" href="/opportunities/kanban" data-command-item>Pipeline Kanban</a>
                    <a class="command-palette__item" href="/reports/pipeline" data-command-item>Pipeline Raporu</a>
                    <a class="command-palette__item" href="/reports/forecast" data-command-item>Tahmin Paneli</a>
                    <a class="command-palette__item" href="/reports/funnel" data-command-item>Funnel Raporu</a>
                    <a class="command-palette__item" href="/reports/sales-cycle" data-command-item>Satis Dongusu</a>
                    <a class="command-palette__item" href="/reports/performance" data-command-item>Kullanici Performansi</a>
                    <a class="command-palette__item" href="/reports/data-quality" data-command-item>Veri Kalite Paneli</a>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const palette = document.querySelector('[data-command-palette]');
                if (!palette) return;

                const input = palette.querySelector('[data-command-input]');
                const items = Array.from(palette.querySelectorAll('[data-command-item]'));
                const openButton = document.querySelector('[data-command-open]');

                const setOpen = (open) => {
                    palette.hidden = !open;
                    if (open) {
                        input.value = '';
                        items.forEach((item) => {
                            item.hidden = false;
                        });
                        setTimeout(() => input.focus(), 0);
                    }
                };

                openButton?.addEventListener('click', () => setOpen(true));
                palette.querySelectorAll('[data-command-close]').forEach((element) => {
                    element.addEventListener('click', () => setOpen(false));
                });

                input?.addEventListener('input', () => {
                    const query = input.value.trim().toLowerCase();

                    items.forEach((item) => {
                        item.hidden = query !== '' && !item.textContent.toLowerCase().includes(query);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                        event.preventDefault();
                        setOpen(true);
                    }

                    if (event.key === 'Escape' && !palette.hidden) {
                        setOpen(false);
                    }
                });
            })();
        </script>
    @endauth
</body>
</html>
