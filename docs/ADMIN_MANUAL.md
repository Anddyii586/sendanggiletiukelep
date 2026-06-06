# Admin Manual

Panduan ini ditujukan untuk admin/pengelola.

## Login Admin

1. Buka `/login`.
2. Masukkan email dan password admin.
3. Jika role user adalah `admin`, sistem mengarahkan ke `/admin/dashboard`.

Seeder menyediakan akun contoh untuk lokal:

| Email | Password |
| --- | --- |
| `admin@example.com` | `@&min586` |

Ubah password sebelum production.

## Mengakses Dashboard Admin

Buka:

```text
/admin/dashboard
```

Dashboard menampilkan ringkasan data seperti total booking, waiting payment, confirmed, completed, revenue paid, recent bookings, dan recent payments berdasarkan controller admin.

## Mengelola Layanan/Paket

Buka:

```text
/admin/services
```

Admin dapat:

- Melihat daftar layanan/paket.
- Menambah layanan.
- Mengedit layanan.
- Menghapus layanan.

Field layanan mencakup nama, deskripsi, harga, pricing type, gambar, status aktif, featured, dan sort order.

## Mengelola Galeri

Buka:

```text
/admin/galleries
```

Admin dapat:

- Melihat daftar galeri.
- Menambah galeri.
- Mengedit galeri.
- Menghapus galeri.

Upload gambar galeri divalidasi dengan format `jpg`, `jpeg`, `png`, atau `webp`, maksimal 2 MB.

## Mengelola Booking

Buka:

```text
/admin/bookings
```

Admin dapat:

- Melihat semua booking.
- Memfilter booking berdasarkan status.
- Memfilter booking berdasarkan tanggal kunjungan.
- Membuka detail booking.
- Melihat data pemesan.
- Melihat data payment.
- Melihat ticket jika sudah tersedia.
- Menandai booking `completed`.
- Membatalkan booking.

Status booking aktif:

- `waiting_payment`
- `confirmed`
- `cancelled`
- `completed`
- `expired`

## Mengelola Pembayaran

Payment diproses melalui Midtrans. Admin tidak perlu approve pembayaran manual pada flow utama.

Pada detail booking, admin dapat melihat:

- Order ID.
- Status payment internal.
- Transaction status Midtrans.
- Payment type.
- Fraud status.
- Gross amount.
- Paid at.
- Raw response Midtrans.

Jika payment sudah paid dan booking confirmed, admin dapat menandai booking sebagai completed setelah kunjungan selesai.

## Mengelola Review

Buka:

```text
/admin/reviews
```

Admin dapat:

- Melihat daftar review.
- Mengubah visibility review.

Review yang `is_visible=true` dapat tampil pada halaman public.

## Mengelola Site Settings

Buka:

```text
/admin/site-settings
```

Admin dapat mengelola pengaturan situs berbasis key-value, seperti nama destinasi, deskripsi, alamat, jam operasional, fasilitas, kontak, dan URL maps.

## Laporan dan Export Data

Tidak ditemukan fitur export data berdasarkan audit route dan controller.

TODO: Lengkapi bagian ini berdasarkan informasi manual dari developer jika export/laporan akan ditambahkan.

## Mengelola User

Tidak ditemukan fitur CRUD user oleh admin berdasarkan audit route dan controller.

TODO: Lengkapi bagian ini berdasarkan informasi manual dari developer jika manajemen user akan ditambahkan.

## Logout

Klik tombol logout pada layout admin. Logout menggunakan route `POST /logout`.
