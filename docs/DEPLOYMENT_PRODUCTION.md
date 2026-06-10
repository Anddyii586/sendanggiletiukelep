# Production Deployment Guide

Panduan ini hanya untuk deployment production. Jangan commit file `.env`, secret, backup database, atau credential admin.

## A. Server Requirements

- PHP `^8.3` sesuai `composer.json`.
- Composer versi stabil terbaru.
- Node.js LTS dan npm.
- MySQL atau MariaDB.
- Web server Nginx atau Apache dengan document root ke folder `public`.
- SSL/HTTPS aktif untuk domain production.
- Cron tersedia untuk menjalankan Laravel scheduler.
- Akses shell untuk menjalankan Artisan command dan queue worker.

## B. Initial Deployment Steps

Jalankan dari server production:

```bash
git clone <repository-url> /path/to/project
cd /path/to/project
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
```

Edit `.env` di server dan isi nilai production:

- `APP_NAME`
- `APP_KEY`
- `APP_URL`
- database credential
- session/cache/queue driver
- Midtrans key
- mail config

Lanjutkan:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## C. File Permission Notes

Pastikan user web server dapat menulis ke:

- `storage`
- `bootstrap/cache`

Contoh umum:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

Sesuaikan user/group dengan environment hosting.

## D. Scheduler Setup

Tambahkan cron Laravel scheduler:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Verifikasi:

```bash
php artisan schedule:list
```

Pastikan `bookings:expire` terdaftar.

## E. Queue Worker Setup

Jika memakai `QUEUE_CONNECTION=database`, jalankan worker dengan process manager seperti Supervisor/systemd:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Pastikan tabel queue sudah ada dari migration Laravel dan worker restart setelah deploy.

## F. Midtrans Setup

- Sandbox: `MIDTRANS_IS_PRODUCTION=false` dan gunakan sandbox client/server key.
- Production: `MIDTRANS_IS_PRODUCTION=true` dan gunakan production client/server key.
- Notification URL di dashboard Midtrans:

```text
https://your-domain.com/payments/midtrans/notification
```

Checklist Midtrans:

- HTTPS aktif dan valid.
- `MIDTRANS_CLIENT_KEY` sesuai environment.
- `MIDTRANS_SERVER_KEY` sesuai environment.
- Jangan memakai ngrok untuk production.
- Jangan mencampur key sandbox dengan mode production.

## G. Admin Setup

Seeder demo hanya ditujukan untuk `local` dan `testing`. Untuk production, buat admin manual:

```bash
php artisan admin:create --name="Admin Name" --email="admin@example.com"
```

Command akan meminta password secara tersembunyi jika `--password` tidak diberikan. Jangan menggunakan credential default atau credential demo di production.

Setelah deploy:

- Gunakan email admin valid.
- Gunakan password kuat dan unik.
- Ubah password admin jika pernah dibuat untuk testing.
- Hapus atau disable data demo jika tidak diperlukan.

## H. Smoke Test After Deploy

Jalankan checklist manual:

- Landing page terbuka.
- Login dan register berjalan.
- User bisa membuat booking.
- Checkout terbuka.
- Midtrans Snap muncul.
- Webhook callback Midtrans berhasil.
- E-ticket muncul setelah payment paid.
- Admin payments terbuka.
- Transaction report terbuka.
- Export CSV rapi.
- User management terbuka.
- Audit logs tercatat setelah aksi admin.

Detail checklist tersedia di `docs/PRODUCTION_SMOKE_TEST.md`.

## I. Rollback Guidance

Sebelum deploy:

```bash
git tag release-YYYYMMDD-HHMM
mysqldump -u <db_user> -p <db_name> > backup-before-deploy.sql
```

Jika perlu rollback:

```bash
git fetch --tags
git checkout <previous-tag-or-release-commit>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika migration sudah mengubah data/schema dan rollback code saja tidak cukup, restore database dari backup:

```bash
mysql -u <db_user> -p <db_name> < backup-before-deploy.sql
```

Uji ulang smoke test setelah rollback.
