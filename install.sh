#!/usr/bin/env bash
# MaggotTrack — pemasang otomatis.
# Pemakaian: ./install.sh [nama-folder] [--migrate]
set -euo pipefail

APP_DIR="${1:-maggottrack}"
MIGRATE="${2:-}"
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

need() { command -v "$1" >/dev/null 2>&1 || { echo "✗ '$1' belum terpasang."; exit 1; }; }
need php
need composer
php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' || { echo "✗ Dibutuhkan PHP 8.2 atau lebih baru."; exit 1; }
for ext in mbstring xml zip gd fileinfo pdo_mysql; do
  php -m | grep -qi "^${ext}$" || echo "⚠ Ekstensi PHP '${ext}' tidak terdeteksi (dibutuhkan untuk Laravel/Excel/PDF)."
done
[ -e "$APP_DIR" ] && { echo "✗ Folder '$APP_DIR' sudah ada."; exit 1; }

echo "→ Mengunduh Laravel…"
composer create-project laravel/laravel "$APP_DIR" --prefer-dist --no-interaction
cd "$APP_DIR"

echo "→ Memasang paket PDF dan Excel…"
composer require barryvdh/laravel-dompdf maatwebsite/excel --no-interaction

echo "→ Menerapkan kode MaggotTrack…"
rm -f database/migrations/0001_01_01_*.php database/database.sqlite tests/Feature/ExampleTest.php resources/views/welcome.blade.php
cp -R "$HERE/overlay/." .
cp .env.example .env
php artisan key:generate --no-interaction
php artisan storage:link || true

echo
echo "✓ Terpasang di: $(pwd)"
echo "  1. Buat database MySQL 'maggottrack' (utf8mb4)."
echo "  2. Sesuaikan DB_USERNAME / DB_PASSWORD di file .env."
echo "  3. php artisan migrate"
echo "  4. php artisan serve   → buka http://localhost:8000"
echo "  5. php artisan test    → jalankan pengujian (butuh ekstensi pdo_sqlite)"

if [ "$MIGRATE" = "--migrate" ]; then
  php artisan migrate --force
fi
