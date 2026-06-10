# Operations Runbook

Panduan ini untuk maintenance harian dan troubleshooting production.

## A. Daily and Maintenance Commands

Expire booking yang sudah lewat waktu bayar:

```bash
php artisan bookings:expire
```

Cek scheduler:

```bash
php artisan schedule:list
```

Cek failed queue jobs:

```bash
php artisan queue:failed
```

Retry semua failed queue jobs:

```bash
php artisan queue:retry all
```

Bersihkan cache aplikasi:

```bash
php artisan cache:clear
php artisan optimize:clear
```

Setelah deploy, rebuild cache:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## B. Payment Issue Checks

Saat ada issue payment:

- Buka `/admin/payments`.
- Cek `payment_logs` untuk event webhook.
- Cek `order_id`.
- Cek `gross_amount`.
- Cek status transaksi di dashboard Midtrans.
- Pastikan notification URL Midtrans benar.
- Resend notification dari dashboard Midtrans jika webhook belum masuk.

Indikasi umum:

- Signature invalid: key atau payload tidak sesuai.
- Amount mismatch: `gross_amount` Midtrans tidak sama dengan payment/booking.
- Payment not found: `order_id` tidak cocok dengan data lokal.

## C. Booking Stuck `waiting_payment`

Kemungkinan penyebab:

- Payment masih pending dan user belum menyelesaikan pembayaran.
- Webhook Midtrans belum masuk.
- Notification URL salah atau tidak bisa diakses.
- Amount mismatch sehingga webhook ditolak.
- Booking sudah expired, tetapi scheduler belum berjalan.
- Queue worker atau scheduler tidak aktif.

Tindakan:

```bash
php artisan bookings:expire
php artisan schedule:list
```

Lalu cek `/admin/payments` dan `payment_logs`.

## D. Error Log Checks

Log Laravel:

```text
storage/logs/laravel.log
```

Catatan:

- Jangan expose folder `storage/logs` ke public.
- Jangan upload log ke tiket publik jika berisi payload sensitif.
- Masking `MIDTRANS_SERVER_KEY`, password, dan token sebelum membagikan log.

## E. Gallery Cloudinary Checks

Saat upload atau gambar galeri bermasalah:

- Cek `.env` production berisi `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, atau `CLOUDINARY_URL`.
- Cek `php artisan config:clear` lalu `php artisan config:cache` setelah perubahan credential.
- Test upload ulang dari `/admin/galleries`.
- Cek tabel `galleries`: record Cloudinary baru harus punya `cloudinary_secure_url`, `cloudinary_public_id`, dan `storage_disk=cloudinary`.
- Jika gambar lama masih memakai `image_path` lokal, pastikan `php artisan storage:link` masih ada untuk fallback local storage.
- Jika delete/update gagal menghapus asset lama, cek log Laravel untuk `Cloudinary image delete failed` dan hapus asset manual dari Cloudinary Console bila perlu.

## F. Database Backup and Restore

Backup database:

```bash
mysqldump -u <db_user> -p <db_name> > backup-$(date +%Y%m%d-%H%M%S).sql
```

Restore database:

```bash
mysql -u <db_user> -p <db_name> < backup-file.sql
```

Praktik aman:

- Backup sebelum `php artisan migrate --force`.
- Simpan backup di lokasi non-public.
- Uji restore secara berkala di staging.

## G. Emergency Action

Aktifkan maintenance mode:

```bash
php artisan down
```

Buka kembali aplikasi:

```bash
php artisan up
```

Jika emergency terkait payment:

- Jangan hapus record booking/payment.
- Ambil backup database sebelum koreksi manual.
- Cocokkan `order_id`, `gross_amount`, dan status dari dashboard Midtrans.
- Catat tindakan manual di audit internal.
