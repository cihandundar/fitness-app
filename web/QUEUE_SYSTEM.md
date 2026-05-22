# 🚀 Laravel Queue System - Dokümantasyon

## 📦 Oluşturulan Job'lar

### 1. SendWelcomeEmail
Yeni üyeliklerde hoş geldin email'i gönderir.

**Kullanım:**
```php
use App\Jobs\SendWelcomeEmail;

SendWelcomeEmail::dispatch($user, $temporaryPassword);
```

### 2. SendMembershipExpiryReminder
Üyeliği bitmek üzere olan user'lara hatırlatma email'i gönderir.

**Kullanım:**
```php
use App\Jobs\SendMembershipExpiryReminder;

SendMembershipExpiryReminder::dispatch($membership, 3); // 3 gün öncesi
```

### 3. SendAppointmentReminder
Randevusu yaklaşan kişilere hatırlatma gönderir (hem user hem trainer).

**Kullanım:**
```php
use App\Jobs\SendAppointmentReminder;

// Kullanıcıya hatırlatma
SendAppointmentReminder::dispatch($appointment, 'user');

// Eğitmene hatırlatma
SendAppointmentReminder::dispatch($appointment, 'trainer');
```

### 4. GenerateClientReport
Müşteri ilerleme raporu oluşturur (PDF).

**Kullanım:**
```php
use App\Jobs\GenerateClientReport;

GenerateClientReport::dispatch($client, $trainer, 'monthly', $startDate, $endDate);
```

### 5. ProcessWorkoutImage
Yüklenen antrenman görsellerini işler (resize, optimize).

**Kullanım:**
```php
use App\Jobs\ProcessWorkoutImage;

ProcessWorkoutImage::dispatch($imagePath, 'workout', $workoutId);
```

### 6. ProcessPaymentWebhook
Ödeme webhook'larını işler.

**Kullanım:**
```php
use App\Jobs\ProcessPaymentWebhook;

ProcessPaymentWebhook::dispatch($payload, 'iyzico');
```

---

## 📅 Scheduled Tasks (Console Commands)

### 1. appointments:send-reminders
Randevu hatırlatmalarını gönderir.

```bash
# 2 saat öncesinden hatırlatma
php artisan appointments:send-reminders --hours=2

# Test modu
php artisan appointments:send-reminders --dry-run
```

**Schedule:** Her saat başı çalışır

### 2. memberships:check-expiry
Üyelik bitiş kontrolü ve hatırlatma.

```bash
# 3 gün öncesinden hatırlatma
php artisan memberships:check-expiry --days=3

# Test modu
php artisan memberships:check-expiry --days=3 --dry-run
```

**Schedule:** Her gün gece yarısı çalışır

### 3. users:cleanup-inactive
Pasif kullanıcıları temizler.

```bash
# 365 gündür aktif olmayanları temizle (soft delete)
php artisan users:cleanup-inactive --days=365 --soft

# Test modu
php artisan users:cleanup-inactive --days=365 --dry-run
```

**Schedule:** Her hafta Pazar günü 02:00'da çalışır

---

## 🔧 Queue Worker Kurulumu

### Development
```bash
# Terminal 1 - Queue worker
php artisan queue:work

# Queue'yu dinlemeye başla
php artisan queue:listen

# Tek bir job işle ve çık
php artisan queue:work --once
```

### Production (Supervisor)

1. Supervisor kurun:
```bash
sudo apt-get install supervisor
```

2. Konfigürasyon dosyasını oluşturun:
`laravel-worker.conf` dosyasını `/etc/supervisor/conf.d/laravel-worker.conf` olarak kopyalayın

3. Yolları güncelleyin:
```conf
command=php /var/www/fitness-app/api/artisan queue:work --sleep=3 --tries=3
stdout_logfile=/var/www/fitness-app/api/storage/logs/worker.log
```

4. Supervisor'u başlatın:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 📊 Queue Yönetimi

### Queue Durumunu Kontrol Et
```bash
# Bekleyen job'ları gör
php artisan queue:monitor

# Failed job'ları gör
php artisan queue:failed

# Failed job detayı
php artisan queue:failed <job-id>
```

### Retry ve Temizleme
```bash
# Tekrar dene
php artisan queue:retry <job-id>
php artisan queue:retry all

# Temizle
php artisan queue:flush
```

### Queue Testing
```bash
# Test job ekle
php artisan tinker
>>> App\Jobs\SendWelcomeEmail::dispatch(App\Models\User::first());

# Test command çalıştır
php artisan appointments:send-reminders --dry-run
```

---

## 🗄️ Queue Tabloları

```sql
-- Job tablosu
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT 0,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
);

-- Failed jobs tablosu
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

---

## 🎯 Queue Öncelikleri

Queue sırası (öncelik):
1. `emails` - Email gönderimleri
2. `notifications` - Bildirimler
3. `payments` - Ödeme işlemleri
4. `reports` - Rapor oluşturma
5. `images` - Görsel işleme
6. `default` - Diğer işlemler

---

## 📈 Monitor Edilecek Metrikler

- Pending jobs sayısı
- Failed jobs sayısı
- Average processing time
- Queue depth (birikme)
- Worker CPU/memory kullanımı

Logları izleyin:
```bash
tail -f storage/logs/worker.log
```

---

*Son güncelleme: 2026-05-07*
*Laravel 11 Queue System*
