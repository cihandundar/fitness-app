# 🛡️ FitApp Geliştirme Standartları & Kuralları

Bu belge, projenin teknik kalitesini ve tasarım bütünlüğünü korumak için Senior Developer rehberliğinde oluşturulmuştur.

## 🎨 Tasarım Sistemi & Estetik
- **Renk Paleti:** Ana arka plan `bg-slate-950`. Kartlar `bg-slate-900/50`. Vurgu renkleri `violet-500` ve `purple-600` gradientleri.
- **Glassmorphism:** Sidebar ve Header gibi sabit alanlarda `backdrop-blur-xl` ve `bg-opacity` kullanılmalıdır.
- **Modern Dokunuşlar:** Kenar yuvarlaklıkları için `rounded-2xl` veya `rounded-3xl` tercih edilmelidir. İnce borderlar (`border-slate-800/50`) derinlik hissi için şarttır.

## 📐 Layout & Dizilim Kuralları
- **Grid Sistemi:**
    - Çoklu kart yapılarında (İstatistikler, Program Listeleri) `grid` kullanılmalıdır.
    - Responsive yapıda `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4` gibi kademeli geçişler zorunludur.
- **Flexbox:**
    - Navbar, Header, Sidebar içeriği ve buton içindeki ikon-metin hizalamaları için `flex` kullanılmalıdır.
    - Basit dikey/yatay ortalamalarda flex tercih edilmelidir.

## 🛠️ Teknik Standartlar (Frontend - Nuxt 4)
- **Komponent Yapısı:** Tekrarlanan UI elemanları (Buton, Input, Card) `web/app/components` altında modüler hale getirilmelidir.
- **State Management:** Kimlik doğrulama ve kullanıcı verileri için `Pinia` (AuthStore) kullanılmalıdır.
- **API İstekleri:** Tüm istekler `axios` üzerinden ve `composables` (useApi vb.) yardımıyla yönetilmelidir.
- **TypeScript:** Tip güvenliği için `interface` tanımlamaları eksiksiz yapılmalıdır.

## 🔌 API Standartları (Backend - Laravel 12)
- **Resource Controllers:** CRUD işlemleri için `apiResource` kullanılmalıdır.
- **Validation:** İstekler `FormRequest` sınıfları ile doğrulanmalıdır.
- **JSON Response:** API yanıtları her zaman tutarlı bir yapıda dönmelidir (data, message, success).

## 🚀 Yol Haritası (Sıradaki Adımlar)
1. **[ ] Programlar Sayfası:** Kullanıcının aktif antrenman programlarını görebileceği ve yeni program seçebileceği arayüzün kodlanması.
2. **[ ] Egzersiz Kütüphanesi:** API'den gelen egzersiz verilerinin filtrelenebilir bir grid yapısında sunulması.
3. **[ ] İlerleme Takibi (Charts):** Kullanıcının kilo ve performans değişimini görselleştirecek grafik entegrasyonu.
4. **[ ] Profil Yönetimi:** Kullanıcı bilgilerinin güncellenmesi ve avatar yükleme işlevi.

---
*Bu kurallar projenin profesyonel standartlarda kalmasını sağlar. Her yeni özellik bu kurallara göre denetlenmelidir.*
