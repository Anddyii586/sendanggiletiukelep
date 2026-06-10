# Production Smoke Test Checklist

Jalankan setelah deployment, cache rebuild, scheduler aktif, queue worker aktif, dan Midtrans notification URL sudah diset.

## Public

- [ ] Landing page terbuka.
- [ ] Package list terbuka.
- [ ] Package detail terbuka.
- [ ] Gallery terbuka.
- [ ] Review terbuka.
- [ ] Info/contact/destination terbuka.

## Auth

- [ ] Register berhasil.
- [ ] Login berhasil.
- [ ] Logout berhasil.
- [ ] User biasa tidak bisa akses admin.

## Booking

- [ ] User bisa membuat booking.
- [ ] Checkout muncul.
- [ ] Expired booking tidak bisa dibayar.
- [ ] Booking user lain tidak bisa diakses user biasa.

## Payment

- [ ] Snap Midtrans muncul.
- [ ] Webhook notification URL mengembalikan HTTP 200 untuk payload valid.
- [ ] Status payment berubah menjadi paid setelah settlement.
- [ ] Status booking berubah menjadi confirmed setelah settlement.
- [ ] E-ticket muncul.
- [ ] QR e-ticket tampil.
- [ ] Amount mismatch webhook ditolak.
- [ ] Invalid signature webhook ditolak.

## Admin

- [ ] Dashboard terbuka.
- [ ] Payment monitoring terbuka.
- [ ] Transaction report terbuka.
- [ ] CSV export rapi.
- [ ] Users terbuka.
- [ ] Detail user terbuka.
- [ ] Audit log tercatat setelah aksi admin.
- [ ] Cancel booking wajib reason.

## Operations

- [ ] `php artisan schedule:list` menampilkan `bookings:expire`.
- [ ] Cron `schedule:run` aktif.
- [ ] Queue worker aktif jika memakai `QUEUE_CONNECTION=database`.
- [ ] `storage/logs/laravel.log` tidak berisi secret.
- [ ] Backup database terakhir tersedia.
