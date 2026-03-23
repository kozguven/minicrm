# Mini CRM Kurulum Rehberi

## Gereksinimler

- PHP 8.2+
- Composer
- Node.js 20+
- SQLite (veya mevcut `.env` ayarına uygun MySQL/PostgreSQL)

## 1) Projeyi Hazırla

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## 2) Veritabanını Kur ve Demo Veriyi Yükle

```bash
php artisan migrate:fresh --seed
```

Bu komut roller, izinler, demo müşteri verisi ve örnek fırsat/görev/satış kayıtlarını üretir.

## 3) Frontend Derleme

```bash
npm run build
```

Geliştirme sırasında:

```bash
npm run dev
```

## 4) Uygulamayı Çalıştır

```bash
php artisan serve
```

Varsayılan adres: `http://127.0.0.1:8000`

## 5) Demo Giriş Bilgileri

- E-posta: `admin@minicrm.local`
- Şifre: `secret123`

Alternatif demo kullanıcı:

- E-posta: `satis@minicrm.local`
- Şifre: `secret123`

## Doğrulama

Kurulum sonrası hızlı kontrol:

```bash
php artisan test --filter=SmokeInstallFlowTest
```
