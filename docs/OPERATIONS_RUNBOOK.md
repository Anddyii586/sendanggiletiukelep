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

## E. Database Backup and Restore

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

## F. Emergency Action

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
