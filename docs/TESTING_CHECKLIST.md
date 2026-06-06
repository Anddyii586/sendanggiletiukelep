# TESTING CHECKLIST

Status test saat audit:

- `php artisan test` passed.
- 2 tests, 2 assertions.
- Test yang ada masih default smoke tests.
- Test booking/payment/webhook/security BELUM ADA.

Gunakan checklist ini sebagai daftar minimal sebelum real use/production.

## 1. Public Test Cases

- [ ] Guest dapat membuka landing page `/`.
- [ ] Guest dapat membuka `/destination`.
- [ ] Guest dapat membuka `/packages`.
- [ ] Guest dapat membuka detail paket `/packages/{slug}`.
- [ ] Detail paket nonaktif menghasilkan 404.
- [ ] Guest dapat membuka `/gallery`.
- [ ] Guest dapat membuka `/reviews`.
- [ ] Guest dapat membuka `/contact`.
- [ ] Navbar menampilkan link Login untuk guest.
- [ ] Tombol Booking Sekarang untuk guest mengarah ke login ketika route booking membutuhkan auth.
- [ ] Empty state muncul jika tidak ada paket aktif.
- [ ] Empty state muncul jika tidak ada review public.

## 2. Auth Test Cases

- [ ] Register sukses dengan data valid.
- [ ] Register gagal jika email sudah dipakai.
- [ ] Register gagal jika password tidak confirmed.
- [ ] Register user baru selalu role `user`.
- [ ] Login user sukses mengarah ke Pesanan Saya.
- [ ] Login admin sukses mengarah ke Admin Dashboard.
- [ ] Login gagal dengan password salah.
- [ ] Login rate limit bekerja setelah percobaan berlebih.
- [ ] Logout menghapus session dan redirect ke home.
- [ ] Guest tidak bisa membuka `/my-bookings`.
- [ ] Guest tidak bisa membuka `/bookings/create`.
- [ ] Admin tidak bisa membuka user booking flow jika route memakai `role:user`.
- [ ] User biasa tidak bisa membuka `/admin/dashboard`.

## 3. User Booking Test Cases

- [ ] User dapat membuka form booking.
- [ ] Form booking menampilkan hanya service aktif.
- [ ] Selected package dari query `?package=` otomatis terpilih.
- [ ] Booking gagal jika `package_id` kosong.
- [ ] Booking gagal jika package tidak ada.
- [ ] Booking gagal jika package nonaktif.
- [ ] Booking gagal jika `visit_date` sebelum hari ini.
- [ ] Booking gagal jika `participant_count < 1`.
- [ ] Booking gagal jika `participant_count > 100`.
- [ ] Booking gagal jika contact name kosong.
- [ ] Booking gagal jika contact phone kosong.
- [ ] Booking gagal jika contact email invalid.
- [ ] Booking sukses membuat record booking.
- [ ] Booking sukses membuat payment awal.
- [ ] Booking code unique.
- [ ] Payment order_id unique.
- [ ] Harga per_person dihitung `service.price * participant_count`.
- [ ] Harga per_trip dihitung `service.price * 1`.
- [ ] Amount tidak bisa dimanipulasi dari frontend.
- [ ] Booking sukses redirect ke checkout.
- [ ] User A tidak bisa melihat booking User B.
- [ ] User A tidak bisa checkout booking User B.
- [ ] User A tidak bisa membayar booking User B.

## 4. Checkout Test Cases

- [ ] Checkout menampilkan booking code.
- [ ] Checkout menampilkan service, tanggal, peserta, kontak, total.
- [ ] Checkout menampilkan payment status.
- [ ] Tombol bayar tampil jika booking `waiting_payment` dan payment belum paid.
- [ ] Tombol bayar tidak tampil jika payment paid.
- [ ] Tombol bayar tidak tampil jika booking expired.
- [ ] Tombol bayar tidak tampil jika booking cancelled.
- [ ] Tombol bayar tidak tampil jika booking completed.
- [ ] Jika Midtrans key kosong, UI menampilkan gateway belum aktif.
- [ ] `POST /bookings/{booking}/pay` membutuhkan auth.
- [ ] `POST /bookings/{booking}/pay` membutuhkan role user.
- [ ] `POST /bookings/{booking}/pay` hanya untuk owner booking.
- [ ] `POST /bookings/{booking}/pay` membuat Snap token backend.
- [ ] Snap token disimpan ke payment.
- [ ] Snap token existing dipakai ulang untuk unpaid/pending jika masih valid.
- [ ] Error Midtrans dikembalikan sebagai JSON 422.

## 5. Payment Gateway Test Cases

- [ ] Server key tidak pernah muncul di HTML/JS.
- [ ] Client key muncul hanya sebagai Snap client key.
- [ ] Amount transaksi berasal dari `booking.total_price`.
- [ ] Item details sesuai service.
- [ ] Order ID sesuai payment order_id.
- [ ] Callback finish mengarah ke detail booking user.
- [ ] Jika order_id konflik, retry order_id berjalan.
- [ ] Payment pending membuat payment status `pending`.
- [ ] Payment paid hanya terjadi dari webhook valid.
- [ ] Frontend Snap `onSuccess` tidak mengubah status paid.
- [ ] Frontend Snap `onPending` tidak mengubah status paid.
- [ ] Frontend Snap `onError` menampilkan pesan error.
- [ ] Frontend Snap `onClose` menampilkan pesan belum selesai.

## 6. Webhook Test Cases

- [ ] Webhook dapat diakses tanpa auth user.
- [ ] Webhook tidak memerlukan CSRF token.
- [ ] Webhook menolak payload tanpa `order_id`.
- [ ] Webhook menolak payload tanpa `status_code`.
- [ ] Webhook menolak payload tanpa `gross_amount`.
- [ ] Webhook menolak payload tanpa `signature_key`.
- [ ] Webhook menolak fake signature.
- [ ] Webhook menolak order_id tidak dikenal.
- [ ] Webhook menolak amount mismatch.
- [ ] Webhook `settlement` mengubah payment menjadi `paid`.
- [ ] Webhook `settlement` mengisi `paid_at`.
- [ ] Webhook `settlement` mengubah booking menjadi `confirmed`.
- [ ] Webhook `settlement` membuat ticket.
- [ ] Webhook `capture` dengan fraud `accept` mengubah payment menjadi `paid`.
- [ ] Webhook `capture` dengan fraud selain accept tidak langsung paid.
- [ ] Webhook `pending` mengubah payment menjadi `pending`.
- [ ] Webhook `pending` mengubah booking menjadi `waiting_payment`.
- [ ] Webhook `expire` mengubah payment menjadi `expired`.
- [ ] Webhook `expire` mengubah booking menjadi `expired`.
- [ ] Webhook `expire` mengubah ticket menjadi `expired` jika sudah ada.
- [ ] Webhook `cancel` mengubah payment menjadi `cancelled`.
- [ ] Webhook `cancel` mengubah booking menjadi `cancelled`.
- [ ] Webhook `deny` mengubah payment menjadi `failed`.
- [ ] Webhook `failure` mengubah payment menjadi `failed`.
- [ ] Duplicate webhook tidak membuat duplicate ticket.
- [ ] Raw response tersimpan.
- [ ] Payment log tersimpan jika tabel payment_logs sudah dibuat.

## 7. E-ticket Test Cases

- [ ] E-ticket tidak bisa dilihat sebelum payment paid.
- [ ] E-ticket tidak bisa dilihat jika booking waiting payment.
- [ ] E-ticket tidak bisa dilihat jika booking cancelled.
- [ ] E-ticket tidak bisa dilihat jika payment unpaid/pending/failed.
- [ ] E-ticket bisa dilihat setelah booking confirmed dan payment paid.
- [ ] E-ticket menampilkan booking code.
- [ ] E-ticket menampilkan ticket code.
- [ ] E-ticket menampilkan nama pemesan.
- [ ] E-ticket menampilkan paket.
- [ ] E-ticket menampilkan tanggal kunjungan.
- [ ] E-ticket menampilkan jumlah peserta.
- [ ] E-ticket menampilkan total.
- [ ] E-ticket print button berfungsi secara UI.
- [ ] Admin bisa melihat e-ticket booking paid.
- [ ] User A tidak bisa melihat e-ticket User B.
- [ ] QR code tampil jika fitur QR sudah ditambahkan.

## 8. Pesanan Saya Test Cases

- [ ] Pesanan Saya hanya menampilkan booking milik user login.
- [ ] List menampilkan booking code.
- [ ] List menampilkan paket.
- [ ] List menampilkan tanggal.
- [ ] List menampilkan total.
- [ ] List menampilkan payment status.
- [ ] List menampilkan booking status.
- [ ] Tombol Bayar muncul untuk booking yang bisa dibayar.
- [ ] Tombol E-ticket muncul setelah paid/confirmed.
- [ ] Tombol Review muncul setelah completed dan belum reviewed.
- [ ] Empty state muncul jika user belum punya booking.
- [ ] Pagination bekerja.
- [ ] Detail pesanan menampilkan timeline.
- [ ] Detail pesanan menampilkan invoice/payment data.
- [ ] Detail pesanan menampilkan review jika sudah ada.

## 9. Review Test Cases

- [ ] User tidak bisa review booking yang belum completed.
- [ ] User bisa review booking completed.
- [ ] User tidak bisa review booking user lain.
- [ ] User tidak bisa review dua kali pada booking yang sama.
- [ ] Rating wajib 1 sampai 5.
- [ ] Comment boleh kosong.
- [ ] Comment maksimal 1000 karakter.
- [ ] Review sukses muncul di detail pesanan.
- [ ] Review visible muncul di halaman public reviews.
- [ ] Admin bisa hide review.
- [ ] Admin bisa show review.

## 10. Admin Test Cases

### Admin Auth

- [ ] Guest tidak bisa membuka `/admin/dashboard`.
- [ ] User role user tidak bisa membuka `/admin/dashboard`.
- [ ] Admin bisa membuka `/admin/dashboard`.
- [ ] Admin sidebar menampilkan menu dashboard, layanan, booking, galeri, review, site settings.

### Dashboard

- [ ] Dashboard menampilkan total booking.
- [ ] Dashboard menampilkan waiting payment.
- [ ] Dashboard menampilkan confirmed.
- [ ] Dashboard menampilkan completed.
- [ ] Dashboard menampilkan cancelled.
- [ ] Dashboard menampilkan revenue paid.
- [ ] Dashboard menampilkan recent bookings.
- [ ] Dashboard menampilkan recent payments.

### Services/Paket

- [ ] Admin dapat melihat daftar paket.
- [ ] Admin dapat membuat paket.
- [ ] Admin dapat edit paket.
- [ ] Admin dapat menonaktifkan paket.
- [ ] Admin tidak bisa hapus paket yang sudah punya booking, hanya dinonaktifkan.
- [ ] Validasi name wajib.
- [ ] Validasi price numeric min 0.
- [ ] Validasi pricing_type hanya `per_person` atau `per_trip`.
- [ ] Slug dibuat unique.
- [ ] Upload image package berhasil jika fitur sudah ditambahkan.

### Booking

- [ ] Admin dapat melihat semua booking.
- [ ] Admin dapat filter booking berdasarkan status.
- [ ] Admin dapat filter booking berdasarkan tanggal.
- [ ] Admin dapat melihat detail booking.
- [ ] Admin dapat melihat payment status.
- [ ] Admin dapat melihat order_id.
- [ ] Admin dapat melihat transaction_status.
- [ ] Admin dapat melihat payment_type.
- [ ] Admin dapat melihat fraud_status.
- [ ] Admin dapat melihat gross_amount.
- [ ] Admin dapat melihat paid_at.
- [ ] Admin dapat melihat raw_response.
- [ ] Admin dapat mark completed hanya untuk booking confirmed.
- [ ] Mark completed mengubah booking status menjadi completed.
- [ ] Mark completed mengubah ticket status menjadi used.
- [ ] Mark completed mengisi checked_in_at.
- [ ] Admin dapat cancel booking non-completed.
- [ ] Cancel booking mengubah payment non-paid menjadi cancelled.
- [ ] Cancel booking mengubah ticket menjadi cancelled.
- [ ] Admin tidak bisa cancel booking completed.
- [ ] Admin cancel paid booking mengikuti flow refund jika fitur refund sudah dibuat.

### Gallery

- [ ] Admin dapat melihat gallery.
- [ ] Admin dapat upload image valid.
- [ ] Upload gagal jika bukan image.
- [ ] Upload gagal jika ukuran lebih dari 2MB.
- [ ] Admin dapat edit gallery.
- [ ] Admin dapat delete gallery.
- [ ] Delete gallery menghapus file local jika bukan URL external.

### Site Settings

- [ ] Admin dapat membuka site settings.
- [ ] Admin dapat update setting text.
- [ ] URL maps divalidasi jika validasi spesifik sudah ditambahkan.
- [ ] Email contact divalidasi jika validasi spesifik sudah ditambahkan.

### Reports/Export

- [ ] BELUM ADA: admin dapat export transaksi CSV/XLSX.
- [ ] BELUM ADA: admin dapat filter laporan tanggal.
- [ ] BELUM ADA: admin dapat melihat summary revenue per periode.

### User Management

- [ ] BELUM ADA: admin dapat melihat daftar user.
- [ ] BELUM ADA: admin dapat melihat detail user.
- [ ] BELUM ADA: admin dapat disable user jika dibutuhkan.

## 11. Security Test Cases

- [ ] Guest tidak bisa checkout.
- [ ] Guest tidak bisa pay.
- [ ] Guest tidak bisa melihat Pesanan Saya.
- [ ] Guest tidak bisa melihat e-ticket.
- [ ] Guest tidak bisa akses admin.
- [ ] User tidak bisa akses admin.
- [ ] Admin tidak bisa memakai route `role:user` jika memang tidak diizinkan.
- [ ] User A tidak bisa akses booking User B.
- [ ] User A tidak bisa akses checkout User B.
- [ ] User A tidak bisa pay booking User B.
- [ ] User A tidak bisa akses ticket User B.
- [ ] User A tidak bisa review booking User B.
- [ ] Webhook fake signature ditolak.
- [ ] Webhook missing required fields ditolak.
- [ ] User tidak bisa bayar booking yang sudah paid.
- [ ] User tidak bisa bayar booking cancelled.
- [ ] User tidak bisa bayar booking expired.
- [ ] User tidak bisa bayar booking completed.
- [ ] Payment amount tidak bisa dimanipulasi dari request frontend.
- [ ] Upload gallery menolak file PHP/script.
- [ ] Blade output review/comment tetap escaped.
- [ ] Admin route memakai CSRF untuk POST/PATCH/PUT/DELETE.
- [ ] `.env` tidak accessible dari public web.
- [ ] `APP_DEBUG=false` di production.
- [ ] Default admin seeder tidak tersedia di production.

## 12. Deployment Test Cases

- [ ] Production `.env` memakai `APP_ENV=production`.
- [ ] Production `.env` memakai `APP_DEBUG=false`.
- [ ] Production `.env` memakai `APP_URL=https://domain`.
- [ ] Production memakai HTTPS.
- [ ] `MIDTRANS_IS_PRODUCTION=true` untuk real payment.
- [ ] `MIDTRANS_SERVER_KEY` production terisi.
- [ ] `MIDTRANS_CLIENT_KEY` production terisi.
- [ ] Webhook URL production didaftarkan di Midtrans dashboard.
- [ ] `php artisan migrate --force` berjalan di staging.
- [ ] Seeder default tidak dijalankan di production.
- [ ] `php artisan storage:link` sudah dibuat jika memakai public disk.
- [ ] Permission `storage/` dan `bootstrap/cache/` benar.
- [ ] `npm run build` sukses.
- [ ] `php artisan config:cache` sukses.
- [ ] `php artisan route:cache` sukses.
- [ ] `php artisan view:cache` sukses.
- [ ] Scheduler aktif untuk expiry booking/payment.
- [ ] Queue worker aktif jika ada job.
- [ ] Log channel production memakai daily/centralized logging.
- [ ] Backup database berjalan.
- [ ] Error monitoring aktif.
- [ ] Smoke test public page setelah deploy.
- [ ] Smoke test login user/admin setelah deploy.
- [ ] Smoke test booking sandbox/staging setelah deploy.

## 13. Manual UAT Scenario

### User Happy Path

- [ ] Guest buka landing page.
- [ ] Guest buka detail paket.
- [ ] Guest klik Pesan Sekarang.
- [ ] Guest login/register.
- [ ] User isi booking form.
- [ ] User masuk checkout.
- [ ] User bayar via Midtrans sandbox.
- [ ] Midtrans webhook settlement terkirim.
- [ ] User melihat payment paid.
- [ ] User melihat e-ticket.
- [ ] Admin melihat booking confirmed.
- [ ] Admin mark completed.
- [ ] User membuat review.
- [ ] Review tampil public.

### Payment Pending Path

- [ ] User checkout.
- [ ] User memilih metode payment pending.
- [ ] Payment status menjadi pending.
- [ ] Booking tetap waiting payment.
- [ ] User bisa kembali ke checkout.
- [ ] Settlement webhook kemudian mengubah paid/confirmed/ticket.

### Payment Expired Path

- [ ] User checkout.
- [ ] Payment tidak diselesaikan sampai expired.
- [ ] Webhook expire atau scheduler mengubah payment expired.
- [ ] Booking menjadi expired.
- [ ] Tombol bayar tidak muncul lagi.

### Admin Cancel Path

- [ ] User membuat booking waiting payment.
- [ ] Admin cancel booking.
- [ ] Booking menjadi cancelled.
- [ ] Payment menjadi cancelled jika belum paid.
- [ ] User tidak bisa bayar.

### Security Negative Path

- [ ] Login sebagai User A.
- [ ] Buat booking A.
- [ ] Login sebagai User B.
- [ ] Coba buka URL booking A.
- [ ] Sistem menolak akses.
- [ ] Coba fake webhook signature.
- [ ] Sistem menolak webhook.
