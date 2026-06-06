# SYSTEM AUDIT

Audit dilakukan berdasarkan struktur file, route list, controller, model, migration, view, config, dan test suite. Tidak ada code aplikasi yang diubah.

## 1. Audit Flow Bisnis

### Ringkasan Flow

Flow target:

Landing Page -> Detail Paket -> Auth Check -> Login/Register -> Booking Form -> Checkout -> Payment Gateway -> Payment Callback/Webhook -> Booking Confirmed -> E-ticket -> Pesanan Saya -> Review.

Status implementasi:

| Flow | Status | File utama | Penilaian |
| --- | --- | --- | --- |
| Landing page | ADA | `HomeController@index`, `public/home.blade.php` | Baik |
| Daftar/detail paket | ADA | `HomeController@packages`, `HomeController@package` | Baik |
| Auth check booking | ADA | `routes/web.php` | Baik |
| Login/register | ADA | Auth controllers + FormRequest | Basic, cukup |
| Booking form | ADA | `BookingController@create/store` | Baik |
| Checkout/invoice | ADA | `BookingController@checkout` | Baik |
| Payment gateway | ADA | `MidtransService`, `BookingController@pay` | Baik |
| Callback/webhook | ADA | `PaymentController@notification` | Baik untuk demo/beta |
| Booking confirmed otomatis | ADA | `MidtransService@handleNotification` | Baik |
| E-ticket | ADA | `Ticket`, `BookingController@ticket` | Cukup, QR belum ada |
| Pesanan Saya | ADA | `BookingController@index/show` | Baik |
| Review setelah completed | ADA | `ReviewController`, `BookingPolicy` | Baik |

### Kelemahan Flow

- CRITICAL: booking/payment expiry belum diproses otomatis. Ada `expires_at`, tetapi tidak ada scheduler/command/job yang mengubah status menjadi expired.
- MEDIUM: `Booking::canPay()` belum mengecek `expires_at`, sehingga booking lewat batas waktu masih bisa menampilkan flow bayar jika status masih `waiting_payment`.
- MEDIUM: tidak ada slot/availability/capacity per tanggal.
- MEDIUM: QR e-ticket belum dibuat, hanya ticket code.
- LOW: user dashboard lama masih ada walau route aktif sudah diarahkan ke Pesanan Saya.

### Kesimpulan Flow Bisnis

Flow utama sudah ada dan mirip sistem Traveloka/Tiket.com versi sederhana. Belum cukup untuk real production karena expiry, availability, testing, dan admin operation belum lengkap.

## 2. Audit Route dan Middleware

Hasil `php artisan route:list -v` menunjukkan 49 route.

### Public/Guest Route

Route public:

- `GET /`
- `GET /destination`
- `GET /packages`
- `GET /packages/{service:slug}`
- `GET /gallery`
- `GET /reviews`
- `GET /contact`

Status:

- Aman untuk public.
- Tidak memakai auth.
- Tidak ada write action public selain login/register dan webhook.

Issue:

- LOW: `GET /reviews` public aman, tetapi review langsung visible setelah dibuat user. Untuk production bisa memakai moderation queue.

### Auth Route

Route auth:

- `GET /login`, `POST /login` dengan `guest`.
- `GET /register`, `POST /register` dengan `guest`.
- `POST /logout` dengan `auth`.

Issue:

- MEDIUM: login throttling khusus tidak terlihat di `LoginRequest` atau controller.
- LOW: reset password route BELUM ADA.

### User Booking Route

Semua route user memakai `auth` dan `role:user`.

Status:

- Admin tidak masuk user flow.
- User access per booking dijaga oleh `BookingPolicy`.
- `pay`, `view`, `viewTicket`, dan `createReview` sudah memakai policy/FormRequest.

Issue:

- MEDIUM: `canPay()` tidak memeriksa expiry.

### Admin Route

Semua route `/admin/*` memakai `auth` dan `role:admin`.

Status:

- Admin route terlindungi.
- Role middleware redirect user biasa ke `my-bookings.index`.

Issue:

- MEDIUM: tidak ada policy granular untuk admin action, hanya role admin global.
- MEDIUM: complete/cancel booking belum punya audit log.

### Webhook Route

Route:

- `POST /payments/midtrans/notification`

Status:

- Tidak memakai auth user, benar untuk payment gateway webhook.
- CSRF dikecualikan di `bootstrap/app.php`, benar untuk webhook eksternal.
- Signature divalidasi di `MidtransService@validateSignature`.

Issue:

- MEDIUM: belum ada validasi explicit amount terhadap expected amount database.
- MEDIUM: belum ada payment log table untuk semua callback.

### API Route

Status: TIDAK DITEMUKAN.

`routes/api.php` tidak ada. Ini bukan masalah jika sistem saat ini full web app.

## 3. Audit Database

### `users`

Kolom penting:

- `id` primary key.
- `name`.
- `email` unique.
- `password`.
- `phone` nullable.
- `role` enum `user/admin`, indexed.

Relasi:

- hasMany bookings.
- hasMany reviews.
- hasMany verifiedPayments legacy.

Issue:

- MEDIUM: role ada di fillable model. Aman pada register saat ini karena role diset eksplisit `user`, tetapi risk future jika ada endpoint mass assignment.
- CRITICAL: seeder membuat akun admin contoh. Wajib diganti/dihapus untuk production.

### `services`

Kolom penting:

- `id`.
- `name`.
- `slug` unique nullable.
- `description`.
- `price` decimal(12,2).
- `pricing_type`.
- `image_path`.
- `is_active`.
- `is_featured`.
- `sort_order`.

Relasi:

- hasMany bookings.

Issue:

- LOW: tidak ada snapshot `service_name` pada booking. Jika nama service berubah, histori booking menampilkan nama baru.
- MEDIUM: tidak ada capacity/stock/availability.
- MEDIUM: admin form service belum mengelola `image_path`.

### `bookings`

Kolom penting:

- `id`.
- `booking_code` unique.
- `user_id` foreign key.
- `service_id` foreign key restrict on delete.
- `visit_date` indexed.
- `participant_count`.
- `contact_name`, `contact_phone`, `contact_email`.
- `subtotal`, `service_fee`, `total_price` decimal.
- `status` string.
- `notes`.
- `expires_at`.

Index:

- `visit_date`.
- `status`.
- `user_id,status`.
- `service_id,visit_date`.

Relasi:

- belongsTo user.
- belongsTo service.
- hasOne payment.
- hasOne ticket.
- hasOne review.

Issue:

- CRITICAL: expiry tidak otomatis diproses.
- MEDIUM: status string tidak dibatasi DB constraint setelah migration upgrade.
- MEDIUM: tidak ada snapshot detail paket selain subtotal/total.

### `payments`

Kolom penting:

- `id`.
- `booking_id` unique foreign key.
- `order_id` unique nullable.
- `snap_token`.
- `snap_redirect_url`.
- `payment_type`.
- `transaction_status`.
- `fraud_status`.
- `gross_amount` decimal.
- `file_path` nullable legacy.
- `status`.
- `paid_at`.
- `expired_at`.
- `raw_response` json.
- `uploaded_at`, `verified_at`, `verified_by` legacy.

Relasi:

- belongsTo booking.
- belongsTo verifier legacy.

Issue:

- MEDIUM: `raw_response` hanya menyimpan payload terakhir, bukan riwayat semua webhook.
- MEDIUM: tidak ada status `refunded`.
- MEDIUM: tidak ada refund/cancel reconciliation.
- LOW: kolom legacy upload payment masih ada.

### `tickets`

Kolom penting:

- `id`.
- `booking_id` unique foreign key.
- `ticket_code` unique.
- `qr_code_path` nullable.
- `status`.
- `checked_in_at`.

Issue:

- MEDIUM: QR generation BELUM ADA.
- LOW: tidak ada scan/check-in endpoint terpisah.

### `reviews`

Kolom penting:

- `id`.
- `booking_id` unique foreign key.
- `user_id` foreign key.
- `rating`.
- `comment` nullable.
- `is_visible`.

Issue:

- LOW: rating tidak punya DB check constraint 1..5, walau validasi request sudah ada.
- MEDIUM: review langsung visible tanpa moderation step.

### `galleries`

Kolom penting:

- `id`.
- `title`.
- `image_path`.
- `description`.
- `is_active`.

Issue:

- LOW: tidak ada `sort_order`.
- LOW: upload validation sudah ada, tetapi image processing/resizing belum ada.

### `site_settings`

Kolom penting:

- `id`.
- `key` unique.
- `value`.

Issue:

- LOW: value bebas string sampai 2000 pada update, belum ada validasi spesifik untuk URL/email/phone.

### `payment_logs`

Status: BELUM ADA.

Impact:

- Tidak ada audit trail untuk semua callback.
- Sulit debugging payment jika Midtrans mengirim banyak notification.

## 4. Audit Model dan Relasi

### Relasi Model

| Model | Relasi | Status |
| --- | --- | --- |
| User | bookings, reviews, verifiedPayments | Benar |
| Service | bookings | Benar |
| Booking | user, service, payment, ticket, review | Benar |
| Payment | booking, verifier | Benar |
| Ticket | booking | Benar |
| Review | booking, user | Benar |
| Gallery | scope active | Cukup |
| SiteSetting | asKeyValue | Cukup |

### Fillable/Guarded

Status umum: Cukup.

Issue:

- MEDIUM: `User` fillable mencakup `role`.
- LOW: `Payment` fillable mencakup gateway fields dan legacy fields; aman karena tidak ada route mass update payment, tetapi perlu dijaga.

### Casts

Status: Baik.

- Decimal casts ada pada price/amount.
- Date/datetime casts ada pada booking/payment/ticket.
- `raw_response` cast array.
- Boolean casts ada pada service/gallery/review.

### Scope/Helper

Status: Ada.

- `Service::active()`.
- `Gallery::active()`.
- `Review::visible()`.
- `Booking::canPay()`.
- `Booking::isTicketAvailable()`.
- `Booking::canBeReviewed()`.

Issue:

- MEDIUM: `canPay()` belum cek expiry.

## 5. Audit Status Booking, Payment, dan Ticket

### Booking Status

Ideal:

- `waiting_payment`
- `confirmed`
- `cancelled`
- `expired`
- `completed`

Aktual:

- Model memiliki semua status ideal.
- Legacy `pending` dan `waiting_verification` masih ada sebagai constants lama.
- `Booking::STATUSES` hanya memakai status final aktif.

Status: Baik, dengan legacy cleanup needed.

### Payment Status

Ideal:

- `unpaid`
- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refunded`

Aktual:

- Ada `unpaid`, `pending`, `paid`, `failed`, `expired`, `cancelled`.
- `refunded` BELUM ADA.
- Legacy `approved` dan `rejected` masih ada.

Status: Cukup.

### Ticket Status

Ideal:

- `active`
- `used`
- `cancelled`
- `expired`

Aktual:

- Ada semua status ideal.

Status: Baik.

### Konsistensi Transisi

Status:

- Booking tidak confirmed dari frontend JS.
- Payment paid otomatis confirmed dan ticket generated dari webhook.
- Ticket dibuat hanya setelah paid.
- Admin bisa mark completed.

Issue:

- CRITICAL: expired booking/payment belum otomatis ditangani tanpa webhook expire.
- MEDIUM: admin cancel booking paid belum menangani refund.

## 6. Audit Payment Gateway

### Konfigurasi

File:

- `config/midtrans.php`
- `.env`
- `.env.example`

Status:

- Server key dan client key diambil dari env.
- Production/sandbox switch memakai `MIDTRANS_IS_PRODUCTION`.
- Snap JS URL mengikuti mode.

Issue:

- CRITICAL: `.env` saat audit `APP_DEBUG=true`.
- CRITICAL: `.env.example` juga `APP_DEBUG=true`, berbahaya jika dipakai sebagai template production.
- LOW: `.env.example` belum menjelaskan URL webhook yang harus didaftarkan di Midtrans dashboard.

### Snap Token

Status: Baik.

- Snap token dibuat backend.
- Amount dari database.
- Order ID dari booking/payment.
- Token disimpan di payment.
- Jika order_id konflik, ada retry dengan suffix random.

Issue:

- MEDIUM: belum ada logging jika `Snap::createTransaction()` gagal.

### Server Key dan Client Key

Status:

- Server key hanya backend.
- Client key dikirim ke Snap JS, sesuai kebutuhan.

Issue: TIDAK DITEMUKAN kebocoran server key ke view.

### Callback/Webhook

Status: Baik.

- Signature validation ada.
- DB transaction ada.
- `lockForUpdate` ada.
- Status settlement/capture/pending/expire/cancel/deny/failure ditangani.
- Fraud status diperiksa untuk capture.
- Raw response disimpan.

Issue:

- MEDIUM: tidak ada amount matching check terhadap payment expected amount.
- MEDIUM: tidak ada `payment_logs`.
- MEDIUM: tidak ada retry/error logging permanen.
- MEDIUM: tidak ada handling refund.

### Kesimpulan Payment

Payment gateway sudah cukup aman untuk demo/beta karena paid berasal dari webhook valid signature dan bukan frontend. Belum production-grade karena observability/logging, expiry, refund, dan test webhook belum kuat.

## 7. Audit Security

### Temuan CRITICAL

1. CRITICAL: `APP_DEBUG=true` pada `.env` dan `.env.example`.
   - Risk: stack trace, env leak, internal path leak di production.
   - Rekomendasi: production harus `APP_ENV=production`, `APP_DEBUG=false`.

2. CRITICAL: Seeder membuat default admin credential.
   - File: `database/seeders/DatabaseSeeder.php`.
   - Risk: akun admin default dapat terbawa ke production.
   - Rekomendasi: jangan seed akun default di production, atau generate credential aman via secret manager/manual admin bootstrap.

3. CRITICAL: test security/payment/webhook BELUM ADA.
   - Risk: regression payment/status/authorization tidak terdeteksi.

4. CRITICAL: expiry booking/payment tidak diproses otomatis.
   - Risk: user bisa membayar booking yang seharusnya expired jika status belum berubah.

### Temuan MEDIUM

1. MEDIUM: login throttling khusus tidak terlihat.
2. MEDIUM: `User` fillable mencakup `role`.
3. MEDIUM: admin action complete/cancel tidak memiliki audit log.
4. MEDIUM: no payment logs untuk callback history.
5. MEDIUM: no amount matching check di webhook terhadap database.
6. MEDIUM: route framework `GET/PUT storage/{path}` muncul; perlu pastikan file serving storage aman untuk production.
7. MEDIUM: `SESSION_ENCRYPT=false`.
8. MEDIUM: tidak ada policy granular untuk admin modules.

### Temuan LOW

1. LOW: reset password BELUM ADA.
2. LOW: role masih sederhana enum, belum permission granular.
3. LOW: review langsung visible.
4. LOW: site settings validation masih generic string.

### Yang Sudah Baik

- Route admin dilindungi `auth` dan `role:admin`.
- Route booking dilindungi `auth` dan `role:user`.
- Authorization user untuk booking milik orang lain memakai `BookingPolicy`.
- CSRF aktif untuk web route, hanya webhook dikecualikan.
- Password hashing memakai Laravel hash/cast.
- SQL injection risk rendah karena Eloquent/query builder dan tidak ada raw user input.
- XSS risk rendah karena Blade escaping.
- Server key Midtrans tidak dikirim ke frontend.

## 8. Audit Validation

### Register/Login

Status: Ada.

- `RegisterRequest`: name, email unique, phone nullable, password confirmed min 8.
- `LoginRequest`: email/password required.

Gap:

- MEDIUM: login throttle khusus BELUM ADA.
- LOW: password policy masih minimum 8, belum kompleksitas production.

### Booking

Status: Baik.

- `package_id` required exists.
- `visit_date` after/equal today.
- participant min 1 max 100.
- contact fields required.
- notes max 1000.

Gap:

- MEDIUM: belum validasi package harus active di Request; controller `Service::active()->findOrFail()` sudah menahan.
- MEDIUM: belum cek capacity/slot.

### Checkout/Payment

Status: Cukup.

- `pay` tidak menerima amount dari frontend.
- Policy memastikan pemilik booking dan status bisa dibayar.

Gap:

- MEDIUM: `canPay()` tidak cek expiry.

### Webhook

Status: Ada di service.

- Required keys diperiksa.
- Signature key divalidasi.

Gap:

- MEDIUM: belum ada FormRequest dedicated.
- MEDIUM: belum cek amount expected.

### CRUD Paket

Status: Ada.

- `StoreServiceRequest`.

Gap:

- MEDIUM: image package tidak dikelola pada form/request.

### Upload Gambar

Status: Ada untuk gallery.

- image type jpg/jpeg/png/webp max 2MB.

Gap:

- LOW: belum image dimension validation.
- LOW: belum image optimization.

### Review

Status: Baik.

- `StoreReviewRequest` authorize via policy.
- rating 1..5.
- comment nullable max 1000.

### Admin Action Complete/Cancel

Status: Validasi inline.

Gap:

- MEDIUM: belum ada FormRequest/action validation.
- MEDIUM: belum ada audit log dan alasan cancel.

## 9. Audit Kualitas Kode

### Controller

Status:

- Ukuran controller masih wajar.
- `BookingController` 128 lines.
- `AdminBookingController` 78 lines.
- `PaymentController` 50 lines.

Issue:

- MEDIUM: business logic pricing ada di `BookingController@store`.
- MEDIUM: admin status transition ada langsung di controller.
- LOW: method manual upload payment masih ada tapi tidak aktif.

### Service

Status:

- `MidtransService` adalah pemisahan yang baik untuk payment.

Issue:

- MEDIUM: service menangani create transaction, notification, validation, ticket creation sekaligus. Untuk production bisa dibagi menjadi action/service lebih kecil.

### Duplikasi/Stale Code

Temuan:

- `PaymentController@create/store` tidak diroute.
- `StorePaymentRequest` tidak aktif.
- `payments/create.blade.php` memanggil `payments.store` yang TIDAK DITEMUKAN.
- `UserDashboardController` tidak dipakai route aktif.
- `user/dashboard.blade.php` dan `layouts/traveler.blade.php` tampak legacy.
- `welcome.blade.php` tampak tidak dipakai.

Severity:

- MEDIUM untuk stale manual payment view karena route name broken jika dipanggil.
- LOW untuk dashboard/welcome legacy.

### Query Efficiency

Status:

- Banyak query memakai eager loading `with`.
- Pagination ada pada list booking, services, galleries, reviews.

Issue:

- LOW: `SiteSetting::asKeyValue()` dipanggil beberapa public controller tanpa cache.
- LOW: Admin dashboard memakai banyak aggregate query terpisah, masih wajar untuk data kecil.

### Error Handling dan Logging

Status:

- Payment create Snap try/catch mengembalikan JSON 422.
- Notification try/catch mengembalikan JSON 400.

Issue:

- MEDIUM: exception penting tidak dicatat ke log secara eksplisit.
- MEDIUM: webhook failure tidak disimpan sebagai payment log.

### Blade Logic

Status:

- Blade cukup rapi.
- Ada komponen `status-badge`, `rating-stars`, `icon`.

Issue:

- LOW: cukup banyak status string di Blade.
- LOW: `checkout.blade.php` punya JS inline cukup panjang.

## 10. Audit UI/UX

### User/Public

Halaman yang ada:

- Landing page.
- Detail paket.
- Booking form.
- Checkout/payment page.
- Pesanan Saya.
- Detail Pesanan.
- E-ticket.
- Review page.

Kondisi:

- Responsive Tailwind.
- CTA booking jelas.
- Empty state ada pada list booking/review/galeri.
- Status payment/booking memakai badge.
- User diarahkan ke Pesanan Saya, bukan dashboard besar.

Issue:

- MEDIUM: e-ticket belum punya QR nyata.
- MEDIUM: post-payment UX bergantung redirect/reload; tidak ada polling status jika webhook delay.
- LOW: loading state hanya sederhana pada tombol bayar.
- LOW: beberapa halaman memakai card cukup banyak, tetapi masih konsisten.
- LOW: user dashboard lama masih ada di file.

### Admin

Halaman yang ada:

- Dashboard.
- Paket/services.
- Booking list.
- Booking detail.
- Payment data pada booking detail.
- Review management.
- Gallery management.
- Site settings.

BELUM ADA:

- User management.
- Payment monitoring page khusus.
- Report/export.
- Refund handling.
- Audit log view.

Kesimpulan UI/UX:

- Baik untuk demo/beta.
- Belum cukup untuk operasi real tanpa tambahan admin workflow.

## 11. Audit Deployment Readiness

### Kondisi Saat Audit

Hasil `php artisan about --only=environment,cache,drivers`:

- Environment: local.
- Debug mode: ENABLED.
- URL: `sendanggile_tiukelep.test`.
- Config cache: NOT CACHED.
- Routes cache: NOT CACHED.
- Views: CACHED.
- Database: mysql.
- Queue: database.
- Session: database.
- Log: stack/single.

### Checklist

| Item | Status | Catatan |
| --- | --- | --- |
| `.env.example` lengkap | SEBAGIAN | Midtrans keys ada, tetapi `APP_DEBUG=true` |
| `APP_DEBUG=false` production | BELUM | Current debug enabled |
| `APP_URL` production | BELUM | Masih local/test |
| Midtrans env lengkap | SEBAGIAN | Local keys present, production false |
| Migration siap | ADA | Semua migration ran |
| Seeder aman | BELUM | Default admin/user |
| Storage link | PERLU VERIFIKASI | `public/storage` harus disiapkan |
| Queue worker | PERLU VERIFIKASI | Queue database, tetapi job domain belum ada |
| Scheduler | BELUM ADA | Expiry booking/payment belum ada |
| Config cache | BELUM | NOT CACHED |
| Route cache | BELUM | NOT CACHED |
| View cache | ADA | CACHED |
| Logging production | BELUM | Single/debug, perlu daily/monitoring |
| Backup strategy | BELUM ADA |
| Error monitoring | BELUM ADA |
| HTTPS requirement | BELUM TERLIHAT |
| Build asset production | ADA | `public/build` ada |

Kesimpulan deployment:

- CRITICAL: belum siap production.
- Layak local/demo.

## 12. Audit Testing

Test yang ada:

- `tests/Feature/ExampleTest.php`: GET `/` status 200.
- `tests/Unit/ExampleTest.php`: true is true.

Hasil:

- `php artisan test` passed.
- 2 tests, 2 assertions.

Gap:

- CRITICAL: booking flow belum dites.
- CRITICAL: payment webhook belum dites.
- CRITICAL: authorization booking user lain belum dites.
- CRITICAL: admin role protection belum dites.
- CRITICAL: expiry/cancel/complete belum dites.

## 13. Scoring Kelayakan Project

| Kategori | Bobot | Skor | Alasan |
| --- | ---: | ---: | --- |
| Flow bisnis | 20 | 17 | Core flow public -> booking -> checkout -> paid webhook -> ticket -> review sudah ada. Gap expiry/availability/QR. |
| Database | 15 | 13 | Tabel utama dan relasi kuat. Gap payment_logs, refund, capacity, DB constraints status. |
| Security | 15 | 10 | Auth/role/policy/signature baik. Gap debug true, default seeder admin, login throttling, expiry, audit log. |
| Payment/webhook | 15 | 13 | Backend Snap dan webhook signature baik. Gap amount check, payment logs, refund, webhook tests. |
| Admin panel | 10 | 7 | Dashboard, package, booking, gallery, review ada. Gap user management, report/export, payment monitor khusus. |
| UI/UX | 10 | 8 | Public/user/admin cukup rapi dan responsive. Gap QR, polling, stale dashboard/payment upload artifacts. |
| Testing | 10 | 1 | Hanya default smoke tests. |
| Deployment | 5 | 3 | Build/migration ada, tetapi env local/debug, no scheduler, no monitoring/backup. |
| Total | 100 | 72 | Layak demo / beta ringan, belum production ready. |

Kategori akhir: 61-75 = Layak demo / beta ringan.

Kesimpulan tegas:

Project belum production ready. Alasan utama adalah security/deployment/testing belum kuat, expiry belum otomatis, dan operasi admin/payment belum lengkap untuk real use.
