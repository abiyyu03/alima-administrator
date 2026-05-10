<?php

return [

    // Default gaji per sesi jika tutor_classes.amount = 0
    'tutor_rate_regular' => (int) env('TUTOR_RATE_REGULAR', 20000),
    'tutor_rate_private' => (int) env('TUTOR_RATE_PRIVATE', 35000),

    // Kelas Regular: minimal siswa hadir agar insentif normal (rate * hadir)
    'regular_min_pupils'    => (int) env('REGULAR_MIN_PUPILS', 3),

    // Insentif flat saat hadir >= 1 tapi < regular_min_pupils
    'regular_min_incentive' => (int) env('REGULAR_MIN_INCENTIVE', 50000),

    // Biaya per sesi Development Class per siswa
    'dev_class_rate' => (int) env('DEV_CLASS_RATE', 6000),

];
