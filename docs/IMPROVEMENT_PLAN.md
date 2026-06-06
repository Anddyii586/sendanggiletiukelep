# IMPROVEMENT PLAN

Dokumen ini hanya rekomendasi. Tidak ada code yang diubah dalam audit ini.

## 1. Masalah Prioritas CRITICAL

### CRITICAL 1: Production environment belum aman

Masalah:

- `.env` saat audit `APP_DEBUG=true`.
- `.env.example` juga `APP_DEBUG=true`.
- `APP_ENV=local`.
- Config/routes belum cached.

Risiko:

- Stack trace dan informasi internal bisa bocor di production.

Rekomendasi:

- Set production:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://domain-production`
  - `MIDTRANS_IS_PRODUCTION=true`
- Jalankan:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
  - `npm run build`

### CRITICAL 2: Default seeded credential

Masalah:

- `DatabaseSeeder` membuat `admin@example.com` dengan password default.
- Seeder juga membuat user contoh.

Risiko:

- Jika seeder dijalankan di production, admin default bisa dipakai pihak tidak sah.

Rekomendasi:

- Jangan seed admin default di production.
- Buat command/manual bootstrap admin yang meminta password aman.
- Jika tetap butuh seed lokal, guard dengan environment check.

### CRITICAL 3: Expiry booking/payment belum otomatis

Masalah:

- Booking punya `expires_at`.
- Payment punya `expired_at`.
- Tetapi tidak ada scheduler/command/job untuk mengubah status setelah expired.
- `Booking::canPay()` belum cek `expires_at`.

Risiko:

- User bisa membayar booking yang seharusnya expired.
- Data waiting payment menumpuk.

Rekomendasi:

- Tambah command `bookings:expire`.
- Jalankan via scheduler tiap 5 menit.
- Update booking `waiting_payment` yang `expires_at < now()` menjadi `expired`.
- Update payment unpaid/pending menjadi `expired`.
- Ubah `canPay()` agar false jika `expires_at` sudah lewat.

### CRITICAL 4: Testing production flow BELUM ADA

Masalah:

- Hanya 2 default tests.
- Tidak ada test booking/payment/webhook/security.

Risiko:

- Regression status paid/confirmed/ticket tidak terdeteksi.

Rekomendasi:

- Buat feature tests untuk booking, checkout, webhook, authorization, admin actions.
- Mock Midtrans/Snap atau isolate `MidtransService`.

## 2. Masalah Prioritas MEDIUM

### MEDIUM 1: Payment logs belum ada

Masalah:

- `payments.raw_response` hanya menyimpan payload terakhir.

Rekomendasi:

- Tambah tabel `payment_logs`:
  - `id`
  - `payment_id`
  - `order_id`
  - `event_type`
  - `transaction_status`
  - `fraud_status`
  - `payload`
  - `signature_valid`
  - `processed_at`
  - `error_message`

### MEDIUM 2: Webhook belum cek amount expected

Masalah:

- Signature divalidasi, tetapi `gross_amount` tidak dibandingkan eksplisit dengan amount database.

Rekomendasi:

- Setelah payment ditemukan, bandingkan normalized `gross_amount` payload dengan `payment.gross_amount` atau `booking.total_price`.
- Tolak/flag mismatch.

### MEDIUM 3: Refund/cancel paid belum ada

Masalah:

- Admin bisa cancel booking non-completed.
- Jika payment sudah paid, flow refund tidak ada.

Rekomendasi:

- Tambah status `refunded`.
- Tambah refund flow dengan Midtrans API jika dibutuhkan.
- Jika admin cancel booking paid, wajib alasan dan status refund.

### MEDIUM 4: Admin module belum lengkap

BELUM ADA:

- User management.
- Payment monitoring page khusus.
- Report/export.
- Audit log admin action.

Rekomendasi:

- Tambah admin users index/show.
- Tambah payments index dengan filter status, date, order_id.
- Tambah reports transactions dengan export CSV/XLSX.
- Tambah audit_logs untuk complete/cancel/refund/hide review.

### MEDIUM 5: Stale manual payment artifacts

File:

- `PaymentController@create/store`
- `StorePaymentRequest`
- `resources/views/payments/create.blade.php`
- Legacy status `approved/rejected`, `waiting_verification`

Masalah:

- View manual payment memanggil route `payments.store` yang tidak terdaftar.
- Bisa membingungkan maintenance.

Rekomendasi:

- Setelah dipastikan tidak dibutuhkan, hapus atau arsipkan artifact manual payment.
- Jika ingin tetap menyimpan legacy, beri komentar eksplisit dan jangan ada view broken.

### MEDIUM 6: Login throttling belum jelas

Rekomendasi:

- Tambahkan rate limiter login.
- Batasi percobaan login per email/IP.
- Tambah lockout message.

### MEDIUM 7: Package image management belum lengkap

Masalah:

- `services.image_path` ada, tetapi form service tidak upload/manage image.

Rekomendasi:

- Tambah upload image pada `StoreServiceRequest`.
- Validasi image max size/dimension.
- Hapus image lama saat update jika local.

## 3. Masalah Prioritas LOW

- LOW: `welcome.blade.php` tidak dipakai.
- LOW: `UserDashboardController` dan traveler dashboard view tidak dipakai route aktif.
- LOW: status string masih banyak di Blade.
- LOW: site settings validation terlalu generic.
- LOW: review langsung visible.
- LOW: no QR code generation untuk ticket.
- LOW: no image optimization/resizing.
- LOW: no password reset flow.

## 4. Rekomendasi Refactor

### Booking Domain

Pindahkan logic berikut ke service/action:

- `CreateBookingAction`
- `CalculateBookingPrice`
- `ExpireBookingAction`
- `CompleteBookingAction`
- `CancelBookingAction`

Benefit:

- Controller lebih tipis.
- Mudah dites.
- Status transition konsisten.

### Payment Domain

Pecah `MidtransService` menjadi:

- `MidtransSnapService`
- `HandleMidtransNotificationAction`
- `ValidateMidtransSignature`
- `CreateTicketAfterPaidAction`
- `RecordPaymentLogAction`

Benefit:

- Webhook logic lebih mudah dites.
- Logging dan idempotency lebih jelas.

### Status

Gunakan PHP enum atau value object untuk:

- Booking status.
- Payment status.
- Ticket status.

Benefit:

- Mengurangi typo status string.
- Memusatkan transition rules.

### Authorization

Tambahkan policy atau gate untuk admin actions:

- complete booking.
- cancel booking.
- hide/show review.
- manage service.
- manage gallery.

Benefit:

- Role admin tetap ada, tetapi authorization lebih eksplisit.

## 5. Rekomendasi Database

### Tambah `payment_logs`

Prioritas: MEDIUM.

Tujuan:

- Simpan semua webhook/callback.
- Audit trail payment.
- Debugging status mismatch.

### Tambah `audit_logs`

Prioritas: MEDIUM.

Tujuan:

- Simpan admin action:
  - complete booking.
  - cancel booking.
  - refund.
  - update package.
  - toggle review.

### Tambah capacity/availability

Prioritas: MEDIUM.

Opsional tergantung bisnis:

- `service_schedules`
- `service_availabilities`
- `booking_slots`

Tujuan:

- Batasi kuota per tanggal.
- Hindari overbooking.

### Snapshot booking

Prioritas: LOW/MEDIUM.

Tambahkan ke bookings jika histori perlu stabil:

- `service_name`
- `service_price`
- `pricing_type_snapshot`

Tujuan:

- Invoice lama tidak berubah saat admin mengubah paket.

### Status constraint

Prioritas: LOW.

Tambahkan DB check constraint jika DB mendukung:

- booking status allowed values.
- payment status allowed values.
- ticket status allowed values.

## 6. Rekomendasi Security

Prioritas wajib sebelum production:

1. Set `APP_DEBUG=false`.
2. Jangan seed default admin di production.
3. Enforce HTTPS.
4. Set secure cookie:
   - `SESSION_SECURE_COOKIE=true`
   - evaluasi `SESSION_ENCRYPT=true`
5. Tambah login throttling.
6. Tambah webhook amount check.
7. Tambah payment logs.
8. Tambah admin audit log.
9. Pastikan storage file serving aman.
10. Jangan expose `.env`, storage private, atau backup file.
11. Gunakan secret manager atau server env untuk Midtrans keys.
12. Tambah authorization tests.

## 7. Rekomendasi Payment

### Snap Token

- Simpan token dan redirect URL seperti sekarang.
- Pastikan token digenerate ulang jika expired.
- Tambah log ketika create transaction gagal.

### Webhook

- Validasi signature.
- Validasi order_id.
- Validasi gross_amount.
- Simpan payment log setiap callback.
- Jalankan update dalam transaction dan lock row.
- Pastikan idempotent jika callback sama datang berkali-kali.

### Status Handling

Tambahkan handling:

- refund.
- partial refund jika dibutuhkan.
- chargeback/dispute jika relevan.

### Expiry

- Scheduler internal tetap wajib meskipun Midtrans mengirim expire webhook.
- Jangan hanya bergantung pada gateway callback.

## 8. Rekomendasi UI/UX

### User/Public

- Pertahankan konsep Pesanan Saya sebagai pusat user.
- Hapus/arsipkan Traveler Dashboard lama jika tidak digunakan.
- Tambah payment status polling ringan di checkout/detail pesanan.
- Tambah QR code pada e-ticket.
- Tambah state "menunggu konfirmasi payment gateway" setelah Snap pending.
- Tambah email/notification setelah booking confirmed jika diperlukan.

### Admin

- Tambah menu Payments.
- Tambah filter payment status dan order_id.
- Tambah report/export.
- Tambah user management.
- Tambah audit log.
- Tambah confirmation modal untuk cancel/complete.
- Tambah alasan cancel.

## 9. Roadmap Bertahap

### Phase 1: Critical Fixes

Target: membuat sistem aman untuk staging/controlled beta.

Checklist:

- Set production env template:
  - `APP_DEBUG=false`
  - `APP_ENV=production`
  - secure session cookie
- Hilangkan default admin/user seeder dari production.
- Tambah booking/payment expiry scheduler.
- Update `Booking::canPay()` agar cek `expires_at`.
- Tambah login throttling.
- Tambah feature tests:
  - guest tidak bisa checkout.
  - user tidak bisa akses booking orang lain.
  - webhook fake signature ditolak.
  - paid webhook membuat booking confirmed dan ticket.

### Phase 2: Payment/Checkout/E-ticket

Target: payment lebih production-grade.

Checklist:

- Tambah `payment_logs`.
- Tambah gross amount check di webhook.
- Tambah logging error payment.
- Tambah QR code e-ticket.
- Tambah polling/status refresh UX.
- Tambah refund status jika dibutuhkan.
- Tambah test:
  - settlement.
  - capture accept.
  - pending.
  - expire.
  - cancel.
  - deny/failure.
  - duplicate webhook.

### Phase 3: Admin Monitoring

Target: admin siap operasional.

Checklist:

- Tambah payment monitoring page.
- Tambah filter payment by status/date/order_id.
- Tambah report/export transaksi.
- Tambah user management.
- Tambah audit log admin action.
- Tambah cancel reason.
- Tambah paid cancellation/refund workflow.

### Phase 4: Testing/Deployment

Target: production readiness.

Checklist:

- Lengkapi unit/feature tests.
- Tambah CI untuk `php artisan test`.
- Tambah Pint/lint jika ingin standar style otomatis.
- Tambah production deployment checklist.
- Setup backup database.
- Setup error monitoring.
- Setup log rotation/daily logs.
- Setup scheduler dan queue worker supervisor.
- Verifikasi HTTPS.
- Verifikasi storage permission.
- Run load/manual UAT.

## 10. Target Kelayakan Setelah Roadmap

Jika Phase 1 selesai:

- Status naik dari demo/beta ringan ke beta aman terbatas.

Jika Phase 1 dan Phase 2 selesai:

- Payment dan booking flow lebih siap untuk internal use.

Jika Phase 1 sampai Phase 4 selesai:

- Baru layak dinilai production ready.
