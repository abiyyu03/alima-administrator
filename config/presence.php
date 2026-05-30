<?php

return [

    // Tarif dasar tutor jika tutor_classes.amount = 0.
    // PENTING: untuk kelas Regular nilai ini dihitung PER ANAK yang hadir (rate × jumlah hadir),
    // bukan per sesi. Untuk Private (dan jenis lain) nilainya flat per sesi.
    'tutor_rate_regular' => (int) env('TUTOR_RATE_REGULAR', 20000), // Regular: per anak hadir
    'tutor_rate_private' => (int) env('TUTOR_RATE_PRIVATE', 35000), // Private: flat per sesi

    // Kelas Regular: minimal siswa hadir agar dihitung per anak (rate × hadir).
    // Jika hadir < nilai ini, gaji memakai insentif flat (regular_min_incentive).
    'regular_min_pupils'    => (int) env('REGULAR_MIN_PUPILS', 3),

    // Insentif flat saat hadir < regular_min_pupils (mis. 1-2 anak)
    'regular_min_incentive' => (int) env('REGULAR_MIN_INCENTIVE', 50000),

    // Biaya per sesi Development Class per siswa
    'dev_class_rate' => (int) env('DEV_CLASS_RATE', 6000),

];
