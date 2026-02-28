<?php

return [
    /**
     * Target jumlah pertemuan dalam satu semester untuk satu kelas kuliah.
     * Standar nasional biasanya 14-16, sistem ini menggunakan 14.
     */
    'target_pertemuan' => 14,

    /**
     * Target jumlah pertemuan dalam satu blok ujian (Setengah semester).
     * Biasanya 7 pertemuan sebelum UTS, dan 7 pertemuan sebelum UAS.
     */
    'target_pertemuan_per_blok' => 7,

    /**
     * Persentase kehadiran minimum per blok agar mahasiswa layak mengikuti ujian.
     * Default: 75%.
     */
    'min_persentase_ujian' => 75,
];
