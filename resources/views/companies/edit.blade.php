@extends('layouts.app')

@section('content')
    <x-ui.panel size="md">
        <div class="surface-stack">
            <x-ui.page-header
                eyebrow="CRM / Şirketler"
                title="Şirketi Düzenle"
                subtitle="Şirket bilgilerini güncelleyin."
            />

            @if ($errors->any())
                <x-ui.notice tone="danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-ui.notice>
            @endif

            <form method="POST" action="{{ url("/companies/{$company->id}") }}" class="form-stack">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="field-label" for="name">Şirket Adı</label>
                    <input class="input" id="name" name="name" type="text" value="{{ old('name', $company->name) }}" required>
                </div>

                <div class="field">
                    <label class="field-label" for="website">Web Sitesi</label>
                    <input class="input" id="website" name="website" type="url" value="{{ old('website', $company->website) }}">
                </div>

                <div class="inline-actions form-actions">
                    <button class="btn btn-primary" type="submit">Şirketi Güncelle</button>
                    <a class="btn btn-secondary" href="{{ url('/companies') }}">Vazgeç</a>
                </div>
            </form>
        </div>
    </x-ui.panel>
@endsection
