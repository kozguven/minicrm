@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 980px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Dashboard</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Dashboard</h1>
                    <p class="muted" style="margin: 0;">Ekip performansinin anlik ozetini bu ekrandan takip edin.</p>
                </div>
                <a class="button" href="/today">Today ekranina don</a>
            </div>

            @if (! $canViewCrm)
                <section style="border: 1px solid var(--border); border-radius: 18px; padding: 1.25rem; background: #fffbeb;">
                    <div class="stack" style="gap: 0.5rem;">
                        <h2 style="margin: 0; font-size: 1.2rem;">Yetki Gerekli</h2>
                        <p class="muted" style="margin: 0;">{{ $permissionMessage }}</p>
                    </div>
                </section>
            @else
                <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                    <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                        <p class="muted" style="margin: 0 0 0.6rem; font-weight: 600;">Açık Fırsatlar</p>
                        <p style="margin: 0; font-size: 2rem; font-weight: 700;">{{ $metrics['open_opportunities'] }}</p>
                    </article>

                    <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                        <p class="muted" style="margin: 0 0 0.6rem; font-weight: 600;">Haftalık Kapanan Satış</p>
                        <p style="margin: 0; font-size: 2rem; font-weight: 700;">{{ $metrics['weekly_closed_deals'] }}</p>
                    </article>
                </div>
            @endif
        </div>
    </section>
@endsection
