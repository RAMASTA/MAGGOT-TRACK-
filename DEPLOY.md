# MaggotTrack — Instalasi, Pengujian, dan Deploy

Paket ini adalah **overlay Laravel 11/12**: kode MaggotTrack (migration, model, controller, service, view, route, tes)
yang ditempelkan ke instalasi Laravel resmi oleh `install.sh`.

## 1. Kebutuhan
- PHP 8.2+ dengan ekstensi: mbstring, xml, zip, gd, fileinfo, pdo_mysql (pdo_sqlite untuk tes)
- Composer 2, MySQL 8 / MariaDB 10.6+
- Browser klien butuh internet (Tailwind, Lucide, Chart.js, dan font dimuat dari CDN)

## 2. Instalasi lokal
```bash
unzip maggottrack.zip && cd maggottrack
./install.sh maggottrack-app        # unduh Laravel + pasang paket + salin kode
cd maggottrack-app
mysql -u root -p -e "CREATE DATABASE maggottrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# edit .env → DB_USERNAME, DB_PASSWORD
php artisan migrate
php artisan serve
```
Buka http://localhost:8000, klik **Daftar**, lalu mulai dari menu **Batch**. Tidak ada data dummy: semua angka 0 dan
halaman menampilkan *empty state* sampai Anda mengisi data.

## 3. Pengujian
```bash
php artisan test
```
Tes mencakup: proteksi halaman, register/login, hash password, kepemilikan data antar-user, CRUD pakan,
pertumbuhan, lingkungan, panen (transaksi status batch), estimasi panen, pencarian, laporan, dan export PDF/Excel.
Checklist manual setelah deploy: lihat bagian 6.

## 4. Deploy produksi (VPS Ubuntu + Nginx)
```bash
# di server, setelah kode ada di /var/www/maggottrack
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
```
Isi `.env` produksi:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
DB_DATABASE=maggottrack
DB_USERNAME=user_khusus     # jangan pakai root
DB_PASSWORD=kata-sandi-kuat
SESSION_SECURE_COOKIE=true  # jika memakai HTTPS
```
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```
Nginx:
```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/maggottrack/public;
    index index.php;
    client_max_body_size 6M;           # unggah foto maks 4 MB

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # sesuaikan versi PHP
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```
Pasang HTTPS: `sudo certbot --nginx -d domain-anda.com`.

## 5. Deploy di shared hosting / cPanel
1. Jalankan `composer install --no-dev` di komputer lokal, unggah seluruh folder proyek (termasuk `vendor/`).
2. Arahkan *document root* domain ke folder `public/`. Jika tidak bisa, taruh proyek di luar `public_html`
   lalu isi `public_html` dengan isi folder `public/` dan sesuaikan path di `index.php`.
3. Buat database dan user MySQL di cPanel, isi `.env`, lalu jalankan migrasi (Terminal cPanel: `php artisan migrate --force`).
4. Tanpa akses SSH untuk `storage:link`: buat symlink lewat cron sekali jalan:
   `ln -s /home/USER/maggottrack/storage/app/public /home/USER/public_html/storage`.

## 6. Checklist uji manual
- [ ] Daftar → masuk → keluar; halaman dalam tidak bisa dibuka tanpa login
- [ ] Buat batch tanpa target panen → target terisi otomatis dari metode
- [ ] Catat pakan, pertumbuhan (dengan foto), lingkungan; grafik di Dashboard dan detail batch terisi
- [ ] Catat panen dengan "tandai batch selesai" → status batch jadi Selesai
- [ ] Menu Jadwal Panen: ubah target, terapkan estimasi, notifikasi di ikon lonceng
- [ ] Laporan: filter periode, export PDF dan Excel terunduh dan terbuka
- [ ] Akun kedua tidak dapat melihat data akun pertama
- [ ] Tampilan HP (lebar ±375 px) dan mode gelap

## 7. Pengaturan penting
- **Estimasi panen**: ubah lama budidaya per metode di `config/maggottrack.php`. Setelah ≥ 2 batch selesai pada
  metode yang sama, rata-rata historis otomatis dipakai.
- **Batas notifikasi**: `alert_days` (default H-3).
- **Cadangan**: `mysqldump maggottrack > backup.sql` dan salin `storage/app/public` (foto).
- **Hapus batch** memakai *soft delete*: data tidak hilang permanen dari database.

## 8. Kesiapan IoT
Tabel `environment_logs` sudah punya kolom `source` (`manual`/`sensor`) dan `device_id`. Untuk sensor, tambahkan
endpoint API ber-token yang menyimpan ke tabel yang sama dengan `source = 'sensor'`; halaman dan grafik tidak perlu diubah.

## 9. Struktur kode
```
app/Http/Controllers   LogController (basis CRUD), Batch, Feeding, GrowthLog, EnvironmentLog, Harvest,
                       HarvestSchedule, Report, Profile, Search, Auth, Dashboard, BatchPhoto
app/Models             User, Batch (+accessor umur/sisa hari/kesiapan), Feeding, GrowthLog, EnvironmentLog, Harvest, BatchPhoto
app/Services           HarvestEstimator, DashboardStatsService, ReportService, NotificationService
database/migrations    7 tabel + indeks + foreign key
resources/views        layouts, crud (index/form generik), dashboard, batches, schedule, reports, profile, search, auth
tests/Feature          MaggotTrackTest
```
