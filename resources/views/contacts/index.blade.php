@extends('layouts.app')

@section('content')
    <section class="card" style="width: min(100%, 820px);">
        <div class="stack">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="muted" style="margin: 0 0 0.35rem; font-weight: 600;">CRM &gt; Kisiler</p>
                    <h1 style="margin: 0 0 0.35rem; font-size: 1.75rem;">Kisiler</h1>
                    <p class="muted" style="margin: 0;">Sirketlere bagli ilgili kisileri listeleyin ve hizlica yenilerini ekleyin.</p>
                </div>
                <a class="button" href="{{ url('/contacts/create') }}">Yeni Kisi</a>
            </div>

            @if ($contacts->isEmpty())
                <p class="muted" style="margin: 0;">Henüz kişi kaydı bulunmuyor.</p>
            @else
                <div class="stack">
                    @foreach ($contacts as $contact)
                        <article style="border: 1px solid var(--border); border-radius: 16px; padding: 1rem;">
                            <div class="stack" style="gap: 0.35rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                    <h2 style="margin: 0; font-size: 1.15rem;">{{ $contact->first_name }} {{ $contact->last_name }}</h2>
                                    <span class="muted">{{ $contact->company?->name }}</span>
                                </div>
                                <p class="muted" style="margin: 0;">
                                    {{ $contact->email ?: 'E-posta eklenmedi' }}
                                </p>
                                <p class="muted" style="margin: 0;">
                                    {{ $contact->phone ?: 'Telefon eklenmedi' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
