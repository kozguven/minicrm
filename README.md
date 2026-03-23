# Mini CRM (MVP)

2-15 kişilik ekipler için self-hosted, Türkçe odaklı, sade ama güçlü bir mini CRM.

## Öne Çıkanlar

- `Today` ekranı ile önceliklendirilmiş günlük aksiyon listesi
- Şirket ve kişi yönetimi
- Fırsat pipeline akışı ve aşama geçişleri
- Görev ve gecikme takibi
- Fırsat -> satış dönüşümü
- Temel dashboard metrikleri
- Rol/izin matrisi ve takım yönetimi
- Kritik işlemler için audit log kayıtları

## Teknoloji

- Laravel 13 (Blade + Eloquent + Policies + Form Requests)
- SQLite/MySQL uyumlu migration yapısı
- PHPUnit feature/unit testleri

## Hızlı Başlangıç

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Detaylı kurulum için: [docs/INSTALL.md](/Users/keremozguven/Projeler/minicrm/.worktrees/codex-mini-crm-mvp/docs/INSTALL.md)

## Demo Giriş

- Admin: `admin@minicrm.local` / `secret123`
- Satış: `satis@minicrm.local` / `secret123`

## Test Komutları

```bash
php artisan test
./vendor/bin/pint --test
```

## Ortam Özeti

`php artisan about` çıktısı (2026-03-23):

- Laravel: `13.1.1`
- PHP: `8.5.3`
- Locale: `tr`
- DB Driver: `sqlite`
