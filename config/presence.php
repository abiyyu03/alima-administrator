<?php

return [

    /*
     * Kelas Regular: jumlah minimal siswa hadir agar insentif dihitung normal (rate * jumlah hadir).
     * Jika hadir >= 1 tapi < minimal, insentif diganti flat sesuai regular_min_incentive.
     * Jika hadir = 0, insentif tetap 0.
     */
    'regular_min_pupils'    => (int) env('REGULAR_MIN_PUPILS', 3),

    /*
     * Insentif flat (dalam rupiah) yang dibayarkan saat jumlah siswa hadir
     * di bawah regular_min_pupils tapi lebih dari 0.
     */
    'regular_min_incentive' => (int) env('REGULAR_MIN_INCENTIVE', 50000),

    /*
     * Biaya per sesi Development Class per siswa (dalam rupiah).
     */
    'dev_class_rate' => (int) env('DEV_CLASS_RATE', 6000),

];
