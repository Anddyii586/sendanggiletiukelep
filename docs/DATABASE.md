# Dokumentasi Database

## Database yang Digunakan

Aplikasi dikonfigurasi menggunakan MySQL melalui `.env.example` dengan nilai default `DB_CONNECTION=mysql`.

## Daftar Tabel

| Tabel | Fungsi |
| --- | --- |
| `users` | Menyimpan data user, admin, credential login, phone, dan role. |
| `services` | Menyimpan layanan atau paket wisata. |
| `bookings` | Menyimpan pesanan kunjungan user. |
| `payments` | Menyimpan data payment gateway dan status pembayaran. |
| `tickets` | Menyimpan e-ticket/voucher booking. |
| `reviews` | Menyimpan rating dan komentar user. |
| `galleries` | Menyimpan data galeri gambar. |
| `site_settings` | Menyimpan pengaturan konten situs berbasis key-value. |
| `cache`, `cache_locks` | Tabel cache Laravel. |
| `jobs`, `job_batches`, `failed_jobs` | Tabel queue Laravel. |
| `sessions` | Tabel session Laravel. |
| `password_reset_tokens` | Token reset password Laravel. |

## Kolom Penting

### `users`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `name` | Nama user. |
| `email` | Email unik untuk login. |
| `password` | Password hashed. |
| `phone` | Nomor telepon opsional. |
| `role` | Enum `user` atau `admin`. |

### `services`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `name` | Nama layanan/paket. |
| `slug` | Slug unik untuk URL public detail paket. |
| `description` | Deskripsi paket. |
| `price` | Harga paket. |
| `pricing_type` | `per_person` atau `per_trip`. |
| `image_path` | Path gambar paket. |
| `is_active` | Status aktif paket. |
| `is_featured` | Status paket unggulan. |
| `sort_order` | Urutan tampil. |

### `bookings`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `booking_code` | Kode booking unik. |
| `user_id` | Foreign key ke `users`. |
| `service_id` | Foreign key ke `services`. |
| `visit_date` | Tanggal kunjungan. |
| `participant_count` | Jumlah peserta. |
| `contact_name` | Nama pemesan. |
| `contact_phone` | Kontak pemesan. |
| `contact_email` | Email pemesan. |
| `subtotal` | Harga sebelum biaya layanan. |
| `service_fee` | Biaya layanan. |
| `total_price` | Total pembayaran. |
| `status` | Status booking. |
| `notes` | Catatan opsional. |
| `expires_at` | Batas waktu booking/pembayaran. |

Status booking aktif berdasarkan model:

- `waiting_payment`
- `confirmed`
- `cancelled`
- `completed`
- `expired`

Catatan: model masih memiliki konstanta legacy `pending` dan `waiting_verification`, tetapi daftar status final aktif di `Booking::STATUSES` adalah lima status di atas.

### `payments`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `booking_id` | Foreign key unik ke `bookings`. |
| `order_id` | Order ID unik untuk Midtrans. |
| `snap_token` | Token Snap Midtrans. |
| `snap_redirect_url` | URL redirect Snap jika tersedia. |
| `payment_type` | Tipe pembayaran dari Midtrans. |
| `transaction_status` | Status transaksi Midtrans. |
| `fraud_status` | Fraud status Midtrans. |
| `gross_amount` | Nominal pembayaran. |
| `status` | Status payment internal. |
| `paid_at` | Waktu pembayaran berhasil. |
| `expired_at` | Waktu pembayaran expired. |
| `raw_response` | Payload webhook Midtrans. |
| `file_path`, `uploaded_at`, `verified_at`, `verified_by` | Kolom legacy upload bukti pembayaran manual. |

Status payment aktif:

- `unpaid`
- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`

### `tickets`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `booking_id` | Foreign key unik ke `bookings`. |
| `ticket_code` | Kode ticket unik. |
| `qr_code_path` | Path QR code opsional. |
| `status` | `active`, `used`, `cancelled`, atau `expired`. |
| `checked_in_at` | Waktu ticket ditandai digunakan. |

### `reviews`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `booking_id` | Foreign key unik ke `bookings`. |
| `user_id` | Foreign key ke `users`. |
| `rating` | Rating 1 sampai 5. |
| `comment` | Komentar opsional. |
| `is_visible` | Menentukan review tampil public atau tidak. |

### `galleries`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `title` | Judul galeri. |
| `image_path` | Path gambar. |
| `description` | Deskripsi opsional. |
| `is_active` | Status tampil. |
| `sort_order` | Urutan tampil jika tersedia. |

### `site_settings`

| Kolom | Keterangan |
| --- | --- |
| `id` | Primary key. |
| `key` | Key unik setting. |
| `value` | Value setting. |

## Primary Key

Semua tabel utama menggunakan kolom `id` sebagai primary key, kecuali tabel bawaan Laravel tertentu seperti `cache` yang menggunakan key berbasis string.

## Foreign Key

| Tabel | Kolom | Referensi |
| --- | --- | --- |
| `bookings` | `user_id` | `users.id` |
| `bookings` | `service_id` | `services.id` |
| `payments` | `booking_id` | `bookings.id` |
| `payments` | `verified_by` | `users.id` |
| `tickets` | `booking_id` | `bookings.id` |
| `reviews` | `booking_id` | `bookings.id` |
| `reviews` | `user_id` | `users.id` |

## Relasi Antar Tabel

| Tabel Asal | Relasi | Tabel Tujuan | Keterangan |
| ---------- | ------ | ------------ | ---------- |
| `users` | hasMany | `bookings` | User dapat memiliki banyak booking. |
| `users` | hasMany | `reviews` | User dapat membuat banyak review. |
| `users` | hasMany | `payments` | Admin dapat menjadi verifier pada kolom legacy `verified_by`. |
| `services` | hasMany | `bookings` | Satu layanan dapat dibooking banyak kali. |
| `bookings` | belongsTo | `users` | Booking dimiliki user. |
| `bookings` | belongsTo | `services` | Booking memilih satu layanan. |
| `bookings` | hasOne | `payments` | Satu booking memiliki satu payment. |
| `bookings` | hasOne | `tickets` | Satu booking memiliki satu ticket. |
| `bookings` | hasOne | `reviews` | Satu booking memiliki satu review. |
| `payments` | belongsTo | `bookings` | Payment terkait booking. |
| `payments` | belongsTo | `users` | Verifier admin legacy. |
| `tickets` | belongsTo | `bookings` | Ticket terkait booking. |
| `reviews` | belongsTo | `bookings` | Review terkait booking. |
| `reviews` | belongsTo | `users` | Review dibuat user. |

## Relasi Berdasarkan Model Laravel

- `User`: `bookings()`, `reviews()`, `verifiedPayments()`.
- `Service`: `bookings()`, route key `slug`, scope `active()`.
- `Booking`: `user()`, `service()`, `payment()`, `ticket()`, `review()`.
- `Payment`: `booking()`, `verifier()`.
- `Ticket`: `booking()`.
- `Review`: `booking()`, `user()`, scope `visible()`.
- `Gallery`: scope `active()`.
- `SiteSetting`: `asKeyValue()`.

## Catatan Migration

- Migration awal membuat tabel Laravel bawaan, users, services, bookings, payments, reviews, galleries, dan site_settings.
- Migration `2026_06_04_000001_upgrade_services_for_checkout_flow` menambahkan slug, pricing type, image path, featured flag, dan sort order pada services.
- Migration `2026_06_04_000002_upgrade_bookings_payments_and_create_tickets` mengubah flow booking/payment ke checkout Midtrans dan membuat tabel tickets.
- Migration `2026_06_04_000003_make_review_comment_nullable` membuat komentar review menjadi nullable.

## Deskripsi ERD

| Entity | Relasi Utama |
| --- | --- |
| User | 1 user memiliki banyak booking dan review. |
| Service | 1 service memiliki banyak booking. |
| Booking | 1 booking dimiliki user, memilih service, memiliki 1 payment, 1 ticket, dan 1 review. |
| Payment | 1 payment dimiliki 1 booking. |
| Ticket | 1 ticket dimiliki 1 booking. |
| Review | 1 review dimiliki 1 booking dan 1 user. |

TODO: Lengkapi bagian ini berdasarkan informasi manual dari developer jika diperlukan diagram ERD visual.
