# Mini CRM Balanced Showcase Design

Date: 2026-03-23
Status: Approved (Design Validation Complete)
Product: Webizmo referans amaçlı, ücretsiz dağıtılabilir mini CRM

## 1. Product Goal

Bu ürün, 2-15 kişilik ekiplerin minimum CRM ihtiyaçlarını karşılayan, self-hosted ve ücretsiz dağıtılabilir bir mini CRM olacaktır. Hedef, yalnızca işlevsel olmak değil; tasarım kalitesi, kullanım hızı ve ilk 30 saniyedeki net yönlendirme ile etkileyici bir deneyim sunmaktır.

## 2. Target Users And Context

- Hedef ekip: 2-15 kişilik küçük ekipler
- Dağıtım modeli: Tamamen self-hosted açık kaynak
- Varsayılan dil: Türkçe
- Kullanım önceliği: Tek sayfa "Günüm" ekranı üzerinden hızlı aksiyon
- İlk 30 saniye hedefi: Aramalar, kritik fırsatlar ve geciken görevlerin tek listede öncelikli görünmesi
- Deneyim yönü: Hız + premium görünüm dengesi

## 3. MVP Scope (Must Have)

MVP aşağıdaki 6 modülü birlikte içerir:

1. Müşteri/Şirket kartları
2. Fırsat/Pipeline yönetimi
3. Görev ve hatırlatıcılar
4. Teklif veya satış kaydı
5. Basit raporlama paneli
6. Tam özelleştirilebilir rol/izin matrisi

## 4. UX Principles

1. Tek merkezli çalışma: Kullanıcı ana olarak "Günüm" ekranında çalışır.
2. Az tıklama: Sık aksiyonlar (müşteri ekleme, fırsat açma, görev atama) hızlı form akışıyla tamamlanır.
3. Öncelik netliği: Sistem, kullanıcıya ne yapması gerektiğini sıralı gösterir.
4. Premium ama sade: Görsel kalite yüksek, dikkat dağıtıcı öğeler düşük tutulur.
5. Hata yerine yönlendirme: Teknik dil yerine kullanıcıya düzeltme adımı sunan mesajlar verilir.

## 5. Information Architecture

Ana navigasyon:

- Günüm
- Müşteriler
- Fırsatlar
- Görevler
- Teklif/Satış
- Raporlar
- Yönetim (Rol/İzin, Takım)

Günüm ekranı öncelik sırası:

1. Aranacak kişiler
2. Kritik fırsatlar
3. Geciken görevler

## 6. System Architecture (High-Level)

Mimari, tek uygulama içinde modüler Laravel yapısı olarak ilerler:

- Presentation Layer: Blade + Laravel route/controller yapısı
- Domain Modules:
  - Contacts (müşteri/şirket)
  - Pipeline (fırsat aşamaları)
  - Tasks (görev/hatırlatıcı)
  - Deals (teklif/satış)
  - Reporting (özet metrikler)
  - Access Control (rol/izin matrisi)
- Data Layer: Eloquent modelleri ve ilişkisel veritabanı

Bu yapı MVP hızını korur, aynı zamanda modül sınırları net kaldığı için sonraki geliştirmelere açık kalır.

## 7. Core Data Flow

Temel iş akışı:

1. Kullanıcı müşteri/şirket kaydı açar.
2. Kayıtla ilişkili fırsat oluşturur.
3. Fırsata görev/hatırlatıcı atar.
4. Fırsat ilerledikçe teklif/satış kaydı oluşur.
5. Kritik/bugünkü işler "Günüm" ekranına düşer.
6. Dashboard özet metrikleri güncellenir.

## 8. Roles And Permissions

MVP’de esnek bir yetkilendirme katmanı bulunur:

- Rol oluşturma ve düzenleme
- İzin tipleri: görüntüle, oluştur, düzenle, sil, dışa aktar
- Modül bazlı izin
- Gerekirse kayıt bazlı kısıtlama için genişlemeye uygun model

Bu seçim, farklı şirketlerin süreçlerine uyum için kritik kabul edilir.

## 9. Error Handling And Reliability

- Form doğrulama: Alan seviyesinde anlık ve açıklayıcı hata mesajları
- Kritik işlem güvenliği: satış dönüşümü, izin ihlali ve veri çakışması kontrolleri
- Audit log: Özellikle rol/izin ve satış tarafında değişikliklerin izlenmesi
- Boş durum tasarımı: Kullanıcıya "sonraki doğru adım" önerisi

## 10. Installation And Distribution

Dağıtım hedefi klasik PHP/Laravel kurulum akışıdır:

- Standart Laravel kurulum adımları
- Ortam değişkenleriyle hızlı yapılandırma
- Migrasyon/seed ile örnek demo verisi
- Self-hosted kullanım için sade kurulum dokümantasyonu

Not: Docker ilk sürümde birincil hedef değildir.

## 11. Testing Strategy

Öncelikli test kapsamı:

- Feature tests:
  - Günüm öncelik sıralamasının doğruluğu
  - Rol/izin matrisinin erişim davranışları
  - Fırsat -> teklif/satış dönüşümünde veri bütünlüğü
- Unit tests:
  - Öncelik hesaplama kuralları
  - İzin çözümleme mantığı
  - Pipeline durum geçiş kuralları
- Smoke test:
  - Kurulum sonrası örnek veriyle kısa demo akışının çalışması

## 12. MVP Success Criteria

MVP tamam kabul şartları:

1. Yeni ekip üyesi 10 dakika içinde ilk müşteri, fırsat ve görev kaydını açabilir.
2. Kullanıcı ilk 30 saniyede öncelikli aksiyonları net görür.
3. Yetkisiz kullanıcı, korumalı işlemlere erişemez.
4. Self-hosted kurulum dokümantasyonu ile kurulum tamamlanabilir.
5. Arayüz premium algı verirken kullanım hızı düşmez.

## 13. Scope Guardrails (YAGNI)

MVP dışında bırakılır:

- Gelişmiş otomasyon ve workflow motoru
- Derin analitik/BI düzeyi raporlama
- AI tabanlı öneri motoru
- Çok karmaşık entegrasyonlar

Amaç: Referans etkisi ve minimum CRM değerini hızla, güvenilir şekilde sunmak.

