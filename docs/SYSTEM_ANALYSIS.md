# SYSTEM ANALYSIS

Audit ini hanya membaca dan menganalisis project. Tidak ada perubahan code aplikasi, migration, route, controller, model, view, atau konfigurasi runtime.

## 1. Ringkasan Sistem

Project adalah website booking/pariwisata berbasis Laravel untuk destinasi Sendang Gile dan Tiu Kelep. Sistem sudah mengarah ke flow booking modern:

Landing page -> detail paket -> login/register jika belum login -> booking form -> checkout -> Midtrans Snap -> webhook Midtrans -> booking confirmed -> e-ticket -> Pesanan Saya -> review setelah completed.

Kondisi utama:

- Framework terdeteksi: Laravel 13.12.0, PHP 8.4.21.
- Payment gateway: Midtrans package `midtrans/midtrans-php`.
- Public website: ADA, bebas diakses guest.
- Booking wajib login: ADA, route booking dilindungi `auth` dan `role:user`.
- User dashboard besar: secara route aktif sudah diarahkan ke `my-bookings`, tetapi file lama `UserDashboardController`, `layouts/traveler.blade.php`, `partials/user-sidebar.blade.php`, dan `user/dashboard.blade.php` masih ada.
- Admin dashboard: ADA.
- Payment utama gateway: ADA melalui Midtrans Snap.
- Manual upload bukti: TIDAK AKTIF di route, tetapi artifact lama masih ada di `PaymentController`, `StorePaymentRequest`, dan `resources/views/payments/create.blade.php`.
- Webhook/callback: ADA di `POST /payments/midtrans/notification`, public, CSRF dikecualikan, signature divalidasi di backend.
- Testing production flow: BELUM ADA.
- Production readiness: BELUM SIAP karena environment masih local/debug, testing minim, tidak ada scheduler expiry, dan default seeded credential berbahaya jika dipakai di production.

## 2. Role Pengguna

### Guest/Public

Fungsi:

- Melihat landing page.
- Melihat destinasi.
- Melihat daftar/detail paket.
- Melihat galeri.
- Melihat review public.
- Melihat kontak.
- Login/register.

Route utama:

- `GET /`
- `GET /destination`
- `GET /packages`
- `GET /packages/{service:slug}`
- `GET /gallery`
- `GET /reviews`
- `GET /contact`
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`

Kesimpulan: public website sudah bebas diakses tanpa login.

### Auth User

Fungsi:

- Membuat booking.
- Melihat checkout.
- Membayar via Midtrans.
- Melihat Pesanan Saya.
- Melihat detail pesanan.
- Melihat e-ticket setelah paid.
- Membuat review setelah booking completed.

Route utama:

- `GET /dashboard` redirect ke `my-bookings.index`
- `GET /bookings/create`
- `POST /bookings`
- `GET /bookings/{booking}/checkout`
- `POST /bookings/{booking}/pay`
- `GET /my-bookings`
- `GET /my-bookings/{booking}`
- `GET /my-bookings/{booking}/ticket`
- `GET /my-bookings/{booking}/review`
- `POST /my-bookings/{booking}/review`

Kesimpulan: user tidak perlu dashboard besar. Implementasi aktif sudah memakai Pesanan Saya.

### Admin

Fungsi:

- Dashboard admin.
- Kelola paket/layanan.
- Kelola booking.
- Complete/cancel booking.
- Melihat data payment gateway.
- Melihat raw response Midtrans.
- Kelola galeri.
- Kelola review visibility.
- Kelola site settings.

Route utama:

- `GET /admin/dashboard`
- `resource /admin/services`
- `GET /admin/bookings`
- `GET /admin/bookings/{booking}`
- `GET /admin/bookings/{booking}/ticket`
- `PATCH /admin/bookings/{booking}/complete`
- `PATCH /admin/bookings/{booking}/cancel`
- `resource /admin/galleries`
- `GET /admin/reviews`
- `PATCH /admin/reviews/{review}/visibility`
- `GET /admin/site-settings`
- `PUT /admin/site-settings`

Kesimpulan: admin panel sudah cukup untuk demo operasional dasar, tetapi belum lengkap untuk real production karena BELUM ADA manajemen user, laporan/export, payment monitoring khusus, refund, audit log, dan scheduler expiry.

## 3. Flow User Aktual

### 1. Landing Page

Status: ADA.

File:

- `HomeController@index`
- `resources/views/public/home.blade.php`

Alur:

- Mengambil `SiteSetting`, paket aktif, galeri aktif, dan review visible.
- Menampilkan CTA booking dan paket unggulan.

Catatan:

- Sudah sesuai public website.
- `resources/views/welcome.blade.php` masih ada dan tampak tidak dipakai oleh route aktif.

### 2. Daftar/Detail Paket Wisata

Status: ADA.

File:

- `HomeController@packages`
- `HomeController@package`
- `resources/views/public/packages.blade.php`
- `resources/views/public/package.blade.php`

Alur:

- Daftar paket hanya `Service::active()`.
- Detail paket memakai route model binding `service:slug`.
- Detail menampilkan harga, pricing type, review paket, dan CTA booking.

Catatan:

- Booking dari public detail memakai form GET ke `bookings.create`.
- Jika guest, middleware `auth` mengarah ke login.
- Parameter tanggal dan peserta dari detail paket dikirim ke form booking.

### 3. Auth Check Saat Booking

Status: ADA.

File:

- `routes/web.php`

Alur:

- Semua route booking berada dalam middleware `auth` dan `role:user`.
- Guest yang klik booking diarahkan ke login oleh middleware auth.

Catatan:

- Admin tidak bisa memakai user booking flow karena dibatasi `role:user`.

### 4. Login/Register

Status: ADA.

File:

- `AuthenticatedSessionController`
- `RegisteredUserController`
- `LoginRequest`
- `RegisterRequest`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

Alur:

- Login memakai email/password.
- Setelah login admin diarahkan ke admin dashboard.
- User diarahkan ke Pesanan Saya.
- Register membuat role `user`.

Catatan:

- Login throttling khusus BELUM ADA.
- Reset password route BELUM ADA.

### 5. Booking Form

Status: ADA.

File:

- `BookingController@create`
- `BookingController@store`
- `StoreBookingRequest`
- `resources/views/bookings/create.blade.php`

Alur:

- User memilih paket aktif.
- Input tanggal, peserta, contact name, phone, email, notes.
- Backend mengambil harga dari database, bukan dari frontend.
- Booking dan payment dibuat dalam DB transaction.
- Status booking awal `waiting_payment`.
- Payment awal `unpaid`.
- Booking expires 1 hari setelah dibuat.

Catatan:

- Hitung harga sudah aman karena dari database.
- Belum ada validasi kapasitas slot atau kuota tanggal.
- `canPay()` belum mengecek `expires_at`.

### 6. Checkout/Invoice

Status: ADA.

File:

- `BookingController@checkout`
- `resources/views/bookings/checkout.blade.php`

Alur:

- Policy `view` memastikan user hanya melihat booking sendiri, admin bisa melihat.
- Checkout menampilkan invoice, status booking/payment, total, dan tombol Midtrans jika gateway ready.

Catatan:

- UI cukup jelas.
- Jika Midtrans key kosong, tombol payment disabled dan pesan konfigurasi tampil.

### 7. Payment Gateway

Status: ADA.

File:

- `BookingController@pay`
- `MidtransService@createSnapToken`
- `MidtransService@createTransaction`
- `resources/views/bookings/checkout.blade.php`

Alur:

- Frontend fetch `POST /bookings/{booking}/pay`.
- Backend membuat Snap token.
- Server key hanya dipakai backend.
- Client key dipakai di Snap JS.
- Amount dihitung dari `booking->total_price`.
- Snap callbacks frontend hanya redirect/reload, tidak mengubah status paid.

Catatan:

- Prinsip "paid dari webhook, bukan frontend JavaScript" sudah diikuti.
- Belum ada logging error payment.

### 8. Payment Callback/Webhook

Status: ADA.

File:

- `PaymentController@notification`
- `MidtransService@handleNotification`
- `MidtransService@validateSignature`
- `bootstrap/app.php`

Alur:

- Route public `POST /payments/midtrans/notification`.
- CSRF dikecualikan khusus endpoint ini.
- Signature Midtrans divalidasi dengan `order_id + status_code + gross_amount + server_key`.
- Payment dicari berdasarkan `order_id` dan dikunci `lockForUpdate`.
- Booking dikunci dalam DB transaction.
- `settlement` dan `capture` dengan fraud accepted/null menjadi paid.
- Paid mengubah booking ke `confirmed` dan membuat ticket.
- Pending mengubah booking ke `waiting_payment`.
- Expire mengubah payment dan booking ke expired.
- Cancel/deny/failure mengubah payment failed/cancelled dan booking cancelled.

Catatan:

- Webhook sudah cukup kuat untuk demo/beta.
- MEDIUM: belum ada validasi eksplisit bahwa `gross_amount` payload sama dengan expected amount database.
- MEDIUM: raw response disimpan, tetapi tidak ada tabel `payment_logs` untuk riwayat semua callback.

### 9. Booking Confirmed Otomatis

Status: ADA.

File:

- `MidtransService@handleNotification`

Alur:

- Booking confirmed hanya dari webhook paid.

Catatan:

- Sudah sesuai target.

### 10. E-ticket

Status: ADA.

File:

- `MidtransService@ensureTicket`
- `BookingController@ticket`
- `resources/views/bookings/ticket.blade.php`
- `Ticket` model dan `tickets` table

Alur:

- Ticket dibuat setelah payment paid.
- Ticket bisa dilihat user pemilik booking atau admin.
- Ticket tersedia hanya jika booking confirmed/completed dan payment paid.

Catatan:

- QR code masih optional/BELUM ADA generator QR.
- Ticket code dipakai sebagai voucher check-in.

### 11. Pesanan Saya

Status: ADA.

File:

- `BookingController@index`
- `BookingController@show`
- `resources/views/bookings/index.blade.php`
- `resources/views/bookings/show.blade.php`

Alur:

- User melihat daftar booking miliknya.
- Tombol Bayar, E-ticket, dan Review muncul berdasarkan status.

Catatan:

- Sesuai konsep user cukup punya Pesanan Saya.

### 12. Review Setelah Booking Completed

Status: ADA.

File:

- `ReviewController@create`
- `ReviewController@store`
- `StoreReviewRequest`
- `BookingPolicy@createReview`
- `resources/views/reviews/create.blade.php`

Alur:

- User bisa review hanya jika booking completed dan belum punya review.
- Review langsung visible public.

Catatan:

- Admin dapat hide/show review.
- MEDIUM: tidak ada moderasi pending sebelum visible.

## 4. Flow User Ideal

Flow ideal yang disarankan:

1. Guest membuka landing page.
2. Guest melihat paket/detail paket.
3. Guest klik booking.
4. Jika guest, redirect login/register lalu kembali ke booking form.
5. User membuat booking.
6. Booking status `waiting_payment`, payment status `unpaid/pending`.
7. User checkout dengan Midtrans Snap.
8. Midtrans mengirim webhook.
9. Backend validasi signature, order_id, amount, status, fraud status.
10. Backend update payment paid.
11. Backend update booking confirmed.
12. Backend generate ticket/QR.
13. User melihat E-ticket di Pesanan Saya.
14. Admin mark completed setelah kunjungan.
15. User dapat review setelah completed.

Gap dari flow ideal:

- CRITICAL: tidak ada scheduler/command expiry untuk booking/payment yang melewati `expires_at`.
- MEDIUM: `canPay()` tidak mengecek `expires_at`.
- MEDIUM: tidak ada `payment_logs` sehingga history webhook tidak lengkap.
- MEDIUM: QR e-ticket belum dibuat.
- LOW: user dashboard lama masih tersisa walaupun route aktif sudah memakai Pesanan Saya.

## 5. Flow Admin

### Login Admin

Status: ADA.

File:

- `AuthenticatedSessionController@store`
- `RoleMiddleware`

Alur:

- Login dengan role admin diarahkan ke `/admin/dashboard`.
- Semua route admin memakai `auth` dan `role:admin`.

### Dashboard Admin

Status: ADA.

File:

- `AdminDashboardController@index`
- `resources/views/admin/dashboard.blade.php`

Isi:

- Total users.
- Total bookings.
- Waiting payment.
- Confirmed.
- Completed.
- Cancelled.
- Revenue paid.
- Latest bookings.
- Recent payments.
- Latest services.

### Kelola Paket Wisata

Status: ADA.

File:

- `AdminServiceController`
- `StoreServiceRequest`
- `resources/views/admin/services/*`

Catatan:

- CRUD paket tersedia.
- MEDIUM: form service tidak menyediakan upload gambar paket, walaupun model/migration punya `image_path`.

### Kelola Booking

Status: ADA.

File:

- `AdminBookingController`
- `resources/views/admin/bookings/*`

Catatan:

- List, detail, filter status/tanggal, complete, cancel tersedia.
- MEDIUM: filter date belum divalidasi via FormRequest.

### Monitoring Pembayaran

Status: SEBAGIAN ADA.

File:

- `AdminDashboardController@index`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/bookings/show.blade.php`

Catatan:

- Admin bisa melihat payment di dashboard dan detail booking.
- Payment detail menampilkan status, transaction status, payment type, fraud, gross amount, paid_at, raw response.
- BELUM ADA halaman payment monitoring terpisah.
- BELUM ADA filter payment status.

### Kelola User

Status: BELUM ADA.

Catatan:

- Tidak ada route/controller/view admin untuk user management.

### Kelola Galeri

Status: ADA.

File:

- `AdminGalleryController`
- `StoreGalleryRequest`
- `resources/views/admin/galleries/*`

### Kelola Review

Status: ADA.

File:

- `AdminReviewController`
- `resources/views/admin/reviews/index.blade.php`

Catatan:

- Admin hanya bisa toggle visibility.
- Tidak ada edit/delete review.

### Laporan Transaksi dan Export

Status: BELUM ADA.

Catatan:

- Tidak ditemukan route laporan atau export.

### Mark Booking Completed

Status: ADA.

File:

- `AdminBookingController@complete`

Alur:

- Hanya booking `confirmed` bisa ditandai `completed`.
- Ticket menjadi `used` dan `checked_in_at` diisi.

### Cancel Booking

Status: ADA.

File:

- `AdminBookingController@cancel`

Alur:

- Booking completed tidak bisa dibatalkan.
- Payment non-paid ikut cancelled.
- Ticket ikut cancelled.

Catatan:

- MEDIUM: belum ada flow refund untuk payment paid yang dibatalkan.

## 6. Modul Sistem

| Modul | Status | File utama | Catatan |
| --- | --- | --- | --- |
| Public pages | ADA | `HomeController`, `GalleryController`, `ReviewController` | Bebas guest |
| Auth | ADA | `AuthenticatedSessionController`, `RegisteredUserController` | Login/register basic |
| Booking | ADA | `BookingController`, `StoreBookingRequest` | Harga dari DB, transaction saat create |
| Checkout | ADA | `bookings.checkout` | Midtrans Snap |
| Payment gateway | ADA | `MidtransService` | Webhook backend, signature validasi |
| Ticket | ADA | `Ticket`, `bookings.ticket` | QR belum dibuat |
| Review | ADA | `ReviewController`, `StoreReviewRequest` | Setelah completed |
| Admin dashboard | ADA | `AdminDashboardController` | Basic monitoring |
| Admin package CRUD | ADA | `AdminServiceController` | Upload image package belum ada |
| Admin booking | ADA | `AdminBookingController` | Complete/cancel |
| Admin payment monitoring | SEBAGIAN | dashboard dan booking detail | Tidak ada list payment khusus |
| Admin user management | BELUM ADA | TIDAK DITEMUKAN | Perlu untuk production |
| Report/export | BELUM ADA | TIDAK DITEMUKAN | Perlu untuk operasional |
| Expiry scheduler | BELUM ADA | TIDAK DITEMUKAN | Critical untuk booking/payment expiry |
| Test suite | SEBAGIAN | `tests/*` | Hanya default smoke tests |

## 7. Struktur Route

### Public/Guest Route

| Method | URI | Name | Action | Middleware | Status |
| --- | --- | --- | --- | --- | --- |
| GET | `/` | `home` | `HomeController@index` | web | AMAN |
| GET | `/destination` | `destination` | `HomeController@destination` | web | AMAN |
| GET | `/packages` | `packages.index` | `HomeController@packages` | web | AMAN |
| GET | `/packages/{service:slug}` | `packages.show` | `HomeController@package` | web | AMAN |
| GET | `/gallery` | `gallery` | `GalleryController@index` | web | AMAN |
| GET | `/reviews` | `reviews` | `ReviewController@index` | web | AMAN |
| GET | `/contact` | `contact` | `HomeController@contact` | web | AMAN |
| GET | `/login` | `login` | `AuthenticatedSessionController@create` | guest | AMAN |
| POST | `/login` | none | `AuthenticatedSessionController@store` | guest | MEDIUM: login throttling khusus belum ada |
| GET | `/register` | `register` | `RegisteredUserController@create` | guest | AMAN |
| POST | `/register` | none | `RegisteredUserController@store` | guest | AMAN |

### Auth User Route

| Method | URI | Name | Action | Middleware | Status |
| --- | --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | closure redirect | auth, role:user | AMAN |
| ANY | `/bookings` | `bookings.index` | redirect to `/my-bookings` | auth, role:user | AMAN |
| GET | `/bookings/create` | `bookings.create` | `BookingController@create` | auth, role:user | AMAN |
| POST | `/bookings` | `bookings.store` | `BookingController@store` | auth, role:user | AMAN |
| GET | `/bookings/{booking}/checkout` | `bookings.checkout` | `BookingController@checkout` | auth, role:user | AMAN via policy |
| POST | `/bookings/{booking}/pay` | `bookings.pay` | `BookingController@pay` | auth, role:user | AMAN via policy |
| GET | `/my-bookings` | `my-bookings.index` | `BookingController@index` | auth, role:user | AMAN |
| GET | `/my-bookings/{booking}` | `my-bookings.show` | `BookingController@show` | auth, role:user | AMAN via policy |
| GET | `/my-bookings/{booking}/ticket` | `my-bookings.ticket` | `BookingController@ticket` | auth, role:user | AMAN via policy |
| GET | `/my-bookings/{booking}/review` | `reviews.create` | `ReviewController@create` | auth, role:user | AMAN via policy |
| POST | `/my-bookings/{booking}/review` | `reviews.store` | `ReviewController@store` | auth, role:user | AMAN via FormRequest authorization |

### Admin Route

Semua route admin memakai `web`, `auth`, dan `role:admin`.

Status umum: AMAN untuk akses role admin.

Gap:

- BELUM ADA admin user management.
- BELUM ADA report/export.
- BELUM ADA payment monitoring route khusus.

### Checkout/Payment Route

| Method | URI | Name | Action | Middleware | Status |
| --- | --- | --- | --- | --- | --- |
| GET | `/bookings/{booking}/checkout` | `bookings.checkout` | `BookingController@checkout` | auth, role:user | AMAN |
| POST | `/bookings/{booking}/pay` | `bookings.pay` | `BookingController@pay` | auth, role:user | AMAN |
| POST | `/payments/midtrans/notification` | `payments.midtrans.notification` | `PaymentController@notification` | web, no auth | AMAN secara konsep karena signature divalidasi |

### API Route

Status: TIDAK DITEMUKAN.

`routes/api.php` tidak ada.

## 8. Struktur Database

### Tabel Utama

| Tabel | Status | Fungsi |
| --- | --- | --- |
| `users` | ADA | User/admin dan credential |
| `services` | ADA | Paket/layanan wisata |
| `bookings` | ADA | Pesanan user |
| `payments` | ADA | Data payment gateway dan legacy upload |
| `tickets` | ADA | E-ticket/voucher |
| `reviews` | ADA | Review user |
| `galleries` | ADA | Galeri public |
| `site_settings` | ADA | Pengaturan konten situs |
| `payment_logs` | BELUM ADA | Riwayat webhook/payment callback |
| `roles/permissions` | BELUM ADA | Role hanya enum di users |

### Dukungan Flow Booking -> Payment -> Paid -> Ticket -> Pesanan Saya

Status: ADA.

Bukti:

- `bookings` punya `booking_code`, `status`, `expires_at`, total.
- `payments` punya `booking_id`, `order_id`, `status`, `gross_amount`, `paid_at`, `raw_response`.
- `tickets` punya `booking_id`, `ticket_code`, `status`.
- `Booking` hasOne `Payment`, hasOne `Ticket`, hasOne `Review`.
- `MidtransService@handleNotification` mengubah payment paid, booking confirmed, lalu membuat ticket.

Gap:

- `payment_logs` BELUM ADA.
- Scheduler expiry BELUM ADA.
- QR ticket BELUM ADA.

## 9. Struktur Folder

### `app/`

Fungsi: kode aplikasi Laravel.

Kondisi: rapi dan sesuai struktur Laravel.

Catatan maintainability:

- Domain booking/payment sudah terpisah cukup baik.
- Service khusus payment sudah ada di `app/Services/MidtransService.php`.
- Beberapa artifact lama manual payment masih tersisa.

### `app/Http/Controllers/`

Fungsi: controller public, auth, booking, payment, review, admin.

Kondisi:

- Controller tidak terlalu besar.
- `BookingController` 128 baris, masih wajar.
- `AdminBookingController` 78 baris, masih wajar.
- `PaymentController` memiliki method manual upload yang tidak aktif di route.

Masalah:

- MEDIUM: business transition booking/payment masih tersebar di controller dan service.
- LOW: `UserDashboardController` tidak dipakai oleh route aktif.

### `app/Http/Middleware/`

Fungsi: middleware custom.

Isi:

- `RoleMiddleware`

Kondisi:

- Sederhana dan efektif untuk `role:user` dan `role:admin`.

Masalah:

- LOW: role authorization hanya berbasis kolom `users.role`, belum granular permission.

### `app/Http/Requests/`

Fungsi: validasi request.

Isi penting:

- `LoginRequest`
- `RegisterRequest`
- `StoreBookingRequest`
- `StorePaymentRequest`
- `StoreReviewRequest`
- `StoreServiceRequest`
- `StoreGalleryRequest`

Kondisi:

- Form Request sudah dipakai dengan baik untuk flow utama.

Masalah:

- `StorePaymentRequest` manual upload sudah tidak aktif.
- Admin booking filter/action belum memakai FormRequest khusus.
- Webhook belum punya Request class, tetapi validasi signature ada di service.

### `app/Models/`

Fungsi: model Eloquent.

Isi:

- `User`, `Service`, `Booking`, `Payment`, `Ticket`, `Review`, `Gallery`, `SiteSetting`.

Kondisi:

- Relasi utama sudah ada.
- Cast decimal/date/array sudah ada.
- Status constants sudah ada.

Masalah:

- MEDIUM: `User` memakai fillable termasuk `role`; aman saat ini karena register eksplisit, tetapi risk untuk perubahan future.
- LOW: status belum memakai PHP enum/value object.

### `app/Services/`

Status: ADA.

Isi:

- `MidtransService`.

Kondisi:

- Payment logic inti sudah dipisah dari controller.

Masalah:

- MEDIUM: belum ada logging/observability khusus payment exception.
- MEDIUM: belum ada dedicated service/action untuk booking expiry dan admin status transition.

### `routes/`

Isi:

- `web.php`
- `console.php`
- `api.php` TIDAK DITEMUKAN.

Kondisi:

- Route public/user/admin jelas.
- Route middleware benar.

Masalah:

- LOW: semua route aplikasi ada di `web.php`; masih wajar untuk ukuran sekarang, tetapi bisa dipisah jika bertambah.

### `database/migrations/`

Kondisi:

- Semua migration status `Ran`.
- Database mendukung booking/payment/ticket.

Masalah:

- MEDIUM: migration menunjukkan transisi dari manual upload ke gateway, sehingga ada kolom legacy.
- MEDIUM: tidak ada `payment_logs`.
- MEDIUM: tidak ada tabel availability/slot/capacity.

### `database/seeders/`

Kondisi:

- Seeder membuat admin/user contoh, paket, galeri, site settings.

Masalah:

- CRITICAL: default admin credential di seeder berbahaya jika seeder dijalankan di production.

### `resources/views/`

Kondisi:

- Public, auth, booking, admin views cukup lengkap.
- UI memakai Tailwind dan komponen reusable.

Masalah:

- LOW: `welcome.blade.php`, `user/dashboard.blade.php`, `layouts/traveler.blade.php`, `partials/user-sidebar.blade.php`, dan `payments/create.blade.php` tampak legacy/tidak aktif.
- MEDIUM: `payments/create.blade.php` memanggil route `payments.store` yang tidak terdaftar.

### `resources/css/`

Kondisi:

- `app.css` memuat Tailwind, theme, komponen tombol/form/card/table/dashboard.
- Responsive style tersedia.

Masalah:

- LOW: style banyak memakai hardcoded color, belum design token/variable lengkap.

### `config/`

Kondisi:

- `config/midtrans.php` ADA.
- Server key/client key dari env.
- Snap JS URL mengikuti mode production/sandbox.

Masalah:

- CRITICAL: `.env` dan `.env.example` saat audit memakai `APP_DEBUG=true`.
- MEDIUM: `SESSION_ENCRYPT=false`.

### `public/`

Kondisi:

- Asset image public tersedia.
- Build assets Vite tersedia.

Masalah:

- LOW: beberapa image adalah sample/static.

### `storage/`

Kondisi:

- Dipakai Laravel untuk log/cache/upload.

Masalah:

- MEDIUM: perlu pastikan `php artisan storage:link` dan permission production benar.
- MEDIUM: route framework `GET/PUT storage/{path}` muncul di route list; perlu validasi konfigurasi file serving untuk production.

### `tests/`

Kondisi:

- Pest tersedia.
- Hanya default tests.

Masalah:

- CRITICAL: BELUM ADA test booking/payment/webhook/security.

## 10. Kesimpulan Sistem Saat Ini

Sistem sudah memiliki fondasi flow booking Traveloka/Tiket.com versi sederhana:

- Public page bebas login.
- Booking wajib login.
- Pesanan Saya tersedia.
- Checkout via Midtrans Snap.
- Webhook backend memegang status paid.
- E-ticket dibuat setelah paid.
- Admin bisa monitoring booking/payment dasar.

Namun sistem belum production ready. Masalah paling penting:

- CRITICAL: environment masih debug/local.
- CRITICAL: default seeded admin credential harus diamankan sebelum production.
- CRITICAL: test suite belum menguji flow bisnis dan security.
- CRITICAL: expiry booking/payment belum ditangani scheduler/command.
- MEDIUM: tidak ada payment log history.
- MEDIUM: admin panel belum punya user management, report/export, payment monitoring khusus, refund.
- MEDIUM: stale manual payment artifacts masih tersisa.

Kategori sementara: layak demo/beta ringan, belum layak production.
