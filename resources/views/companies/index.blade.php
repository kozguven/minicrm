@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Sirketler</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Sirketler</h1>
                    <p class="muted" style="margin: 0;">Musteri firmalarinizi ve bagli kisi kayitlarini tek listede takip edin.</p>
                </div>
                <a class="button" href="{{ url('/companies/create') }}">Yeni Sirket</a>
            </div>

            @if ($companies->isEmpty())
                <p class="muted" style="margin: 0;">Henüz şirket kaydı bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($companies as $company)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.35rem;">
                                <h2 style="margin: 0; font-size: 1.15rem;">{{ $company->name }}</h2>
                                <p class="muted" style="margin: 0;">
                                    {{ $company->website ?: 'Website eklenmedi' }}
                                </p>
                                <p class="muted" style="margin: 0;">
                                    {{ $company->contacts_count }} kisi
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
