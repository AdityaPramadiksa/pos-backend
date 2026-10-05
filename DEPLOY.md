# Deploy backend ke shared hosting

Panduan memasang backend POS Men Gede (API untuk aplikasi kasir + panel admin) di shared hosting seperti Hostinger, Niagahoster, DomaiNesia, atau Rumahweb. Langkahnya sama untuk panel hosting cPanel maupun hPanel; yang beda hanya letak menunya.

## 1. Syarat paket hosting

| Syarat | Keterangan |
|---|---|
| PHP 8.1 atau lebih baru | Disarankan 8.2. Pilih versinya di menu *PHP Configuration* / *Select PHP Version*. |
| MySQL / MariaDB | 1 database cukup. |
| **Akses SSH** | Wajib untuk `composer` dan `php artisan`. Paket termurah kadang tidak punya SSH. |
| Subdomain dengan *document root* bebas | Supaya folder `public/` bisa jadi root, misalnya `pos.namawarung.com`. |
| SSL gratis (Let's Encrypt) | **Wajib.** PIN kasir dan token login lewat jaringan. |

Ekstensi PHP yang perlu aktif (biasanya sudah aktif): `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `gd`, `zip`, `curl`.

## 2. Siapkan subdomain, SSL, dan database

1. Buat subdomain, misalnya `pos.namawarung.com`. Biarkan *document root* default dulu; nanti diarahkan di langkah 4.
2. Aktifkan SSL untuk subdomain itu, lalu tunggu sampai `https://pos.namawarung.com` bisa dibuka.
3. Buat database MySQL baru beserta user-nya. Catat **nama database, user, dan password**.

## 3. Upload kode

Lewat SSH, taruh kode di **luar** `public_html` supaya file `.env` tidak bisa dibuka dari internet:

```bash
cd ~
git clone https://github.com/AdityaPramadiksa/pos-backend.git pos-backend
cd pos-backend
```

Repo private akan meminta login. Pakai *Personal Access Token* GitHub sebagai password, atau unggah ZIP proyek lewat File Manager lalu ekstrak ke `~/pos-backend`. Folder `vendor/` dan file `.env` tidak perlu ikut diunggah.

## 4. Arahkan subdomain ke folder `public`

Di menu subdomain, ubah *document root* `pos.namawarung.com` menjadi:

```
pos-backend/public
```

**Kalau hosting tidak mengizinkan mengubah document root:**

1. Salin **isi** `~/pos-backend/public/` ke folder document root subdomain, misalnya `~/public_html/pos/`.
2. Edit `index.php` di folder itu. Ubah dua baris `__DIR__.'/../...` menjadi path ke `pos-backend`:
   ```php
   require __DIR__.'/../../pos-backend/vendor/autoload.php';
   $app = require_once __DIR__.'/../../pos-backend/bootstrap/app.php';
   ```
   Sesuaikan jumlah `../` dengan letak foldernya.
3. Ulangi penyalinan setiap kali ada perubahan di folder `public/`.

## 5. Install dependensi

```bash
cd ~/pos-backend
composer install --no-dev --optimize-autoloader
```

Kalau perintah `composer` tidak ada:

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

## 6. Isi file `.env`

```bash
cp .env.example .env
nano .env
```

Ganti bagian ini (sisanya biarkan):

```dotenv
APP_NAME="POS Men Gede"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pos.namawarung.com

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_dari_hosting
DB_USERNAME=user_database_dari_hosting
DB_PASSWORD=password_database
```

`APP_URL` harus diawali `https://`. Dengan begitu foto menu dan form admin selalu memakai https.

## 7. Siapkan aplikasi

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan pos:admin
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

- `pos:admin` membuat akun admin pertama: email, password minimal 8 karakter, dan PIN 4 angka untuk izin void. **Jangan** jalankan `db:seed` di server, karena seeder memakai password bawaan yang umum.
- `storage:link` sering ditolak di shared hosting (fungsi `symlink` dimatikan). Itu tidak masalah: foto menu dan nota tetap tampil lewat rute cadangan `/storage/...`.

Buka `https://pos.namawarung.com`, lalu masuk dengan email dan password admin tadi.

## 8. Pindahkan data dari laptop (opsional)

Kalau menu, kategori, dan staf di laptop mau dipakai:

1. Di laptop (Laragon), buka phpMyAdmin, pilih database, lalu **Export** ke format SQL.
2. Di hosting, buka phpMyAdmin untuk database baru, lalu **Import** file tadi. Lakukan sebelum `php artisan migrate`, atau ke database yang masih kosong.
3. Jalankan `php artisan migrate --force` untuk menambah kolom yang belum ada.
4. Salin folder `storage/app/public/menus` dari laptop ke `~/pos-backend/storage/app/public/menus` di hosting, supaya foto menu ikut pindah.

## 9. Atur toko dan staf

Di panel admin:

1. **Pengaturan**: periksa nama warung, alamat, telepon, catatan kaki struk, modal awal, pajak, dan batas stok.
2. **Staff**: tambahkan kasir dan beri PIN masing-masing.
3. **Menu & stok**: periksa harga dan stok.

## 10. Sambungkan aplikasi kasir

Di HP/tablet kasir, buka halaman login, ketuk **Server … · Ubah**, isi `pos.namawarung.com`, lalu tekan **Tes koneksi** dan **Simpan**.

Setiap kasir harus **masuk sekali saat online** di perangkat itu. Setelah itu PIN-nya bisa dipakai masuk walau sinyal hilang.

## Mode offline: yang perlu diketahui

- Saat sinyal hilang, kasir tetap bisa mencatat pesanan, menyimpan dan melunasi bill, menambah pesanan ke bill, mencatat kas keluar, dan mengubah stok. Struk dan tiket dapur tetap tercetak lewat Bluetooth.
- Transaksi tersimpan di perangkat dan dikirim otomatis berurutan saat sinyal kembali. Server menandai setiap transaksi dengan kode unik, jadi kiriman ulang tidak membuat data dobel.
- Selama offline, panel admin dan dashboard belum menampilkan transaksi itu. Bill dari HP lain juga tidak terlihat.
- **Tutup shift** dan **void** butuh koneksi. Tutup shift ditahan sampai semua transaksi kasir itu terkirim.
- Jangan hapus aplikasi atau datanya saat masih ada transaksi menunggu. Tanda di atas layar kasir menunjukkan jumlahnya.

## Memperbarui backend

Setelah ada perubahan kode:

```bash
cd ~/pos-backend
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## Backup

- **Database**: export lewat phpMyAdmin minimal seminggu sekali, atau aktifkan backup otomatis dari hosting.
- **Foto**: folder `~/pos-backend/storage/app/public`.
- **File `.env`**: simpan salinannya di tempat aman. Isinya password database dan `APP_KEY`.

## Kalau ada masalah

| Gejala | Penyebab & solusi |
|---|---|
| Halaman putih / *500 Server Error* | Lihat `storage/logs/laravel.log`. Biasanya izin folder (`chmod -R 775 storage bootstrap/cache`) atau `.env` salah. |
| *No application encryption key* | Jalankan `php artisan key:generate` lalu `php artisan config:cache`. |
| Semua halaman *404* kecuali beranda | Document root belum ke folder `public`, atau file `public/.htaccess` tidak ikut terunggah. |
| *419 Page Expired* saat login admin | `APP_URL` tidak sama dengan alamat yang dibuka (http vs https). Perbaiki lalu `php artisan config:cache`. |
| Perubahan `.env` tidak terasa | Jalankan `php artisan config:cache` lagi setiap kali `.env` diubah. |
| Aplikasi kasir "Tidak tersambung ke server" | Coba buka `https://pos.namawarung.com/api/ping` di browser HP. Harus muncul `"status":"success"`. |
