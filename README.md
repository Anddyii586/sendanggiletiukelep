# Sistem Informasi Pariwisata Sendang Gile & Tiu Kelep

Sistem Informasi Pariwisata Sendang Gile & Tiu Kelep adalah aplikasi web Laravel untuk menampilkan informasi destinasi wisata, paket layanan, galeri, review publik, booking online, pembayaran melalui Midtrans Snap, e-ticket, dan dashboard admin.

Dokumentasi ini dibuat berdasarkan audit kode aktual di repository, termasuk route, controller, model, migration, request validation, policy, middleware, view, config, dependency, dan seeder.

## Tujuan Project

Project ini bertujuan membantu pengelola wisata Sendang Gile & Tiu Kelep dalam menyediakan kanal informasi dan pemesanan online yang lebih rapi, sekaligus membantu wisatawan melihat paket, membuat booking, membayar melalui payment gateway, dan mendapatkan e-ticket.

## Tech Stack

| Layer | Teknologi |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Blade, Tailwind CSS 4, Vite |
| Database | MySQL |
| Authentication | Laravel session authentication |
| Payment Gateway | Midtrans Snap (`midtrans/midtrans-php`) |
| File Storage | Laravel Storage dan public asset lokal |
| Testing | Pest, Laravel test runner |

## Fitur Utama

- Public website: landing page, destinasi, paket wisata, galeri, review, dan kontak.
- Authentication: register, login, logout, role `user` dan `admin`.
- Booking wisata: pilih paket, tanggal kunjungan, jumlah peserta, data pemesan, dan checkout.
- Payment gateway: generate Midtrans Snap token, popup pembayaran, dan webhook notification.
- E-ticket: dibuat otomatis setelah pembayaran berhasil dan booking terkonfirmasi.
- Riwayat booking user: daftar booking, status pembayaran, status booking, detail, checkout ulang, dan e-ticket.
- Review user: user dapat membuat review setelah booking berstatus `completed`.
- Admin dashboard: statistik booking, payment, user, review, dan revenue paid.
- Admin management: kelola layanan/paket, galeri, booking, review visibility, dan site settings.

## Role Pengguna

| Role | Akses |
| --- | --- |
| `user` | Membuat booking, checkout, membayar, melihat riwayat, melihat e-ticket, dan membuat review setelah booking selesai. |
| `admin` | Mengakses dashboard admin, mengelola layanan, galeri, booking, review, dan site settings. |

## Ringkasan Alur Sistem

1. Wisatawan membuka halaman public dan memilih paket wisata.
2. Jika belum login, wisatawan melakukan register atau login.
3. Wisatawan membuat booking dengan memilih paket, tanggal, jumlah peserta, dan mengisi data pemesan.
4. Sistem menghitung harga dari data paket di database, membuat `booking_code`, membuat payment record, dan mengarahkan user ke checkout.
5. User menekan tombol bayar, sistem membuat Midtrans Snap token, lalu user menyelesaikan pembayaran melalui popup Midtrans.
6. Midtrans mengirim webhook ke endpoint notification.
7. Sistem memvalidasi signature webhook, memperbarui payment, mengonfirmasi booking, dan membuat e-ticket jika pembayaran berhasil.
8. Admin dapat memonitor booking dan menandai booking `completed` setelah kunjungan selesai.
9. User dapat membuat review untuk booking yang sudah `completed`.

## Cara Install Singkat

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Untuk mode development frontend:

```bash
npm run dev
```

## Dokumentasi Lengkap

- [Product Requirements Document](docs/PRD.md)
- [Software Requirements Specification](docs/SRS.md)
- [Database Documentation](docs/DATABASE.md)
- [Routes Documentation](docs/ROUTES.md)
- [Installation Guide](docs/INSTALLATION.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [User Manual](docs/USER_MANUAL.md)
- [Admin Manual](docs/ADMIN_MANUAL.md)
- [Testing Documentation](docs/TESTING.md)
- [Security Documentation](docs/SECURITY.md)
- [Changelog](docs/CHANGELOG.md)

## Catatan Keamanan `.env`

File `.env` berisi konfigurasi sensitif seperti database credential, `APP_KEY`, dan Midtrans key. Jangan commit file `.env` ke repository. Gunakan `.env.example` sebagai template konfigurasi tanpa secret asli.

Seeder membuat akun contoh untuk kebutuhan lokal. Ubah semua password akun contoh sebelum aplikasi digunakan di production.
