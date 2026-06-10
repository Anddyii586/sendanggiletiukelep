# Security Checklist

Gunakan checklist ini sebelum membuka akses production.

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_KEY` valid dan tidak kosong.
- [ ] HTTPS aktif dengan sertifikat valid.
- [ ] `SESSION_SECURE_COOKIE=true`.
- [ ] Database credential kuat dan tidak dibagikan.
- [ ] Midtrans key tidak dipublish di Git, log, issue tracker, atau frontend.
- [ ] Midtrans sandbox/production key sesuai dengan `MIDTRANS_IS_PRODUCTION`.
- [ ] Cloudinary credential tidak dipublish di Git, log, issue tracker, atau frontend.
- [ ] `CLOUDINARY_URL` jika dipakai memakai format `cloudinary://<api_key>:<api_secret>@<cloud_name>` dan hanya ada di `.env` server.
- [ ] Upload galeri admin hanya menerima image `jpg`, `jpeg`, `png`, atau `webp` dengan batas ukuran yang ditentukan.
- [ ] Webhook signature validation aktif.
- [ ] Webhook amount validation aktif.
- [ ] Admin route diproteksi `auth` dan `role:admin`.
- [ ] Default admin credential diganti atau tidak pernah dibuat di production.
- [ ] Seeder demo tidak dijalankan untuk production.
- [ ] Permission `storage` dan `bootstrap/cache` hanya writable sesuai kebutuhan web server.
- [ ] Backup database aktif dan restore pernah diuji.
- [ ] Log tidak expose secret, token, password, atau full credential.
- [ ] `.env` tidak masuk Git.
- [ ] `vendor` dan `node_modules` tidak masuk Git.
- [ ] CORS/CSRF dipahami sesuai kebutuhan integrasi.
- [ ] Route webhook Midtrans tetap dikecualikan dari CSRF dan divalidasi signature.
- [ ] Rate limiting login aktif.
- [ ] Scheduler aktif.
- [ ] Queue worker aktif jika `QUEUE_CONNECTION` bukan `sync`.
- [ ] Web server document root mengarah ke `public`, bukan root project.
- [ ] Directory listing web server nonaktif.
- [ ] File backup SQL tidak disimpan di public web root.
