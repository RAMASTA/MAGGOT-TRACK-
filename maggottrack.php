<?php

return [
    // Zona waktu aplikasi
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    // Estimasi lama budidaya (hari, dihitung dari tanggal telur) per metode.
    // Ubah angka ini sesuai praktik budidaya Anda. Jika sudah ada data historis
    // batch selesai pada metode yang sama, rata-rata historis dipakai lebih dulu.
    'harvest_methods' => [
        'standar'   => ['label' => 'Standar (limbah organik campuran)', 'days' => 20],
        'intensif'  => ['label' => 'Intensif (pakan terkontrol)',       'days' => 16],
        'ekstensif' => ['label' => 'Ekstensif (skala rumah tangga)',     'days' => 28],
    ],
    'default_harvest_days' => 20,
    'min_history_samples'  => 2,   // minimal batch selesai agar data historis dipakai

    'alert_days' => 3,             // notifikasi muncul H-3 sebelum target panen
    'per_page'   => 10,
];
