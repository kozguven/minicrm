@extends('layouts.app')

@section('content')
    <x-ui.panel size="lg">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Şirketler"
                title="Şirketler"
                subtitle="Müşteri firmalarınızı ve bağlı kişi kayıtlarını tek listede takip edin."
            >
                <a class="btn btn-primary" href="{{ url('/companies/create') }}">Yeni Şirket</a>
            </x-ui.page-header>

            @if ($companies->isEmpty())
                <x-ui.empty-state>Henüz şirket kaydı bulunmuyor.</x-ui.empty-state>
            @else
                <div class="content-list">
                    @foreach ($companies as $company)
                        <article class="content-card">
                            <h2 class="content-card__title">{{ $company->name }}</h2>
                            <p class="muted">{{ $company->website ?: 'Web sitesi eklenmedi' }}</p>
                            <p class="muted">{{ $company->contacts_count }} kişi</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui.panel>
@endsection
