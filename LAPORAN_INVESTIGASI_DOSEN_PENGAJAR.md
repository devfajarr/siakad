# 🔍 LAPORAN INVESTIGASI BUG DOSEN PENGAJAR KELAS KULIAH

**Tanggal**: 20 Februari 2026  
**Status**: ✅ **BUG KRITIS DITEMUKAN**

---

## 📋 EXECUTIVE SUMMARY

**Masalah**: Data dosen pengajar di kelas kuliah pada sistem lokal kebanyakan sama (id_dosen berulang/tidak sesuai), sedangkan di server pusat berbeda-beda dan sesuai kelasnya.

**Root Cause**: 
1. **Mapping id_registrasi_dosen → id_dosen salah** karena multiple external_id ter-mapping ke satu id_dosen yang sama
2. **Sync logic tidak menggunakan id_aktivitas_mengajar** sebagai unique identifier
3. **Overwrite terjadi** karena sync menggunakan composite key (id_kelas_kuliah, id_dosen) yang tidak unik ketika mapping salah

---

## 🚨 TEMUAN KRITIS

### 1. Multiple External ID untuk Satu ID Dosen

**Ditemukan 2 id_dosen dengan multiple external_id:**

- **id_dosen 171**: Memiliki **58 external_id berbeda**
- **id_dosen 170**: Memiliki **28 external_id berbeda**

**Dampak:**
- Ketika sync menggunakan `DosenPenugasan::pluck('id_dosen', 'external_id')`, semua external_id tersebut akan ter-mapping ke id_dosen yang sama
- Ketika API mengembalikan multiple record dengan id_registrasi_dosen berbeda untuk kelas yang sama, semua akan ter-resolve ke id_dosen yang sama
- Sync mencari existing record menggunakan `(id_kelas_kuliah, id_dosen)`, sehingga terjadi overwrite

### 2. Sync Logic Tidak Menggunakan id_aktivitas_mengajar

**Lokasi Bug**: `app/Console/Commands/SyncDosenPengajarKelasKuliahFromServer.php` baris 217-219

```php
// Upsert per record (karena perlu resolve id_dosen per item)
$existing = DosenPengajarKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)
    ->where('id_dosen', $idDosenLokal)
    ->first();
```

**Masalah:**
- Sync menggunakan `(id_kelas_kuliah, id_dosen)` sebagai key untuk mencari existing record
- `id_aktivitas_mengajar` (UUID unik dari server) **TIDAK digunakan** sebagai unique identifier
- Ketika mapping salah, multiple dosen berbeda ter-resolve ke id_dosen yang sama, menyebabkan overwrite

### 3. Mapping Registrasi Dosen Map

**Lokasi**: `app/Console/Commands/SyncDosenPengajarKelasKuliahFromServer.php` baris 52-54

```php
$this->registrasiDosenMap = DosenPenugasan::whereNotNull('external_id')
    ->pluck('id_dosen', 'external_id')
    ->toArray();
```

**Masalah:**
- `pluck()` hanya menyimpan mapping terakhir jika ada duplikasi key
- Tidak ada validasi untuk memastikan mapping konsisten
- Tidak ada logging untuk mendeteksi mapping yang ambigu

---

## 🔎 ANALISIS DETAIL

### Struktur Database

**Tabel: `dosen_pengajar_kelas_kuliah`**

```sql
- id (PK)
- id_aktivitas_mengajar (UUID, unique, nullable) ← SEHARUSNYA DIGUNAKAN SEBAGAI KEY
- id_kelas_kuliah (UUID, FK)
- id_dosen (BIGINT, FK) ← DIGUNAKAN SEBAGAI KEY (BERMASALAH)
- id_registrasi_dosen (UUID, nullable)
- ...
- UNIQUE(id_kelas_kuliah, id_dosen) ← CONSTRAINT INI BENAR, TAPI TIDAK CUKUP
```

**Constraint yang ada:**
- ✅ Unique constraint pada `(id_kelas_kuliah, id_dosen)` - BENAR
- ✅ Unique constraint pada `id_aktivitas_mengajar` - BENAR, tapi TIDAK DIGUNAKAN

**Masalah:**
- Constraint `(id_kelas_kuliah, id_dosen)` tidak cukup ketika mapping salah
- `id_aktivitas_mengajar` adalah UUID unik dari server yang lebih reliable sebagai identifier

### Alur Sync yang Bermasalah

1. **Build Mapping** (baris 52-54):
   ```php
   registrasiDosenMap = {
       'external_id_1' => 171,  // id_dosen
       'external_id_2' => 171,  // SAMA!
       'external_id_3' => 171,  // SAMA!
       ...
   }
   ```

2. **API Mengembalikan Data** (baris 174):
   ```php
   $data = [
       ['id_registrasi_dosen' => 'external_id_1', 'id_kelas_kuliah' => 'kelas_A'],
       ['id_registrasi_dosen' => 'external_id_2', 'id_kelas_kuliah' => 'kelas_A'],
       ['id_registrasi_dosen' => 'external_id_3', 'id_kelas_kuliah' => 'kelas_A'],
   ]
   ```

3. **Resolve id_dosen** (baris 192):
   ```php
   // Semua ter-resolve ke id_dosen yang sama!
   $idDosenLokal = 171; // untuk semua record
   ```

4. **Cari Existing Record** (baris 217-219):
   ```php
   // Semua mencari record yang sama!
   $existing = DosenPengajarKelasKuliah::where('id_kelas_kuliah', 'kelas_A')
       ->where('id_dosen', 171)
       ->first();
   ```

5. **Overwrite Terjadi** (baris 221-223):
   ```php
   // Record pertama di-update
   // Record kedua OVERWRITE record pertama
   // Record ketiga OVERWRITE record kedua
   // Hasil: Hanya record terakhir yang tersimpan!
   ```

---

## 💡 REKOMENDASI PERBAIKAN

### 1. Gunakan id_aktivitas_mengajar sebagai Primary Key untuk Upsert

**Perubahan di `SyncDosenPengajarKelasKuliahFromServer.php`:**

```php
// SEBELUM (SALAH):
$existing = DosenPengajarKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)
    ->where('id_dosen', $idDosenLokal)
    ->first();

// SESUDAH (BENAR):
$existing = DosenPengajarKelasKuliah::where('id_aktivitas_mengajar', $idAktivitasMengajar)
    ->first();

// Atau lebih baik menggunakan updateOrCreate:
DosenPengajarKelasKuliah::updateOrCreate(
    ['id_aktivitas_mengajar' => $idAktivitasMengajar],
    $values
);
```

**Alasan:**
- `id_aktivitas_mengajar` adalah UUID unik dari server yang tidak bisa duplikat
- Lebih reliable daripada composite key yang bergantung pada mapping lokal
- Konsisten dengan struktur database yang sudah ada

### 2. Perbaiki Mapping Registrasi Dosen

**Validasi Mapping:**

```php
// Build map dengan validasi
$registrasiDosenMap = [];
$duplicates = [];

DosenPenugasan::whereNotNull('external_id')
    ->get()
    ->each(function ($penugasan) use (&$registrasiDosenMap, &$duplicates) {
        $externalId = $penugasan->external_id;
        $idDosen = $penugasan->id_dosen;
        
        if (isset($registrasiDosenMap[$externalId])) {
            // Duplikasi external_id untuk id_dosen berbeda
            if ($registrasiDosenMap[$externalId] !== $idDosen) {
                $duplicates[] = [
                    'external_id' => $externalId,
                    'existing_id_dosen' => $registrasiDosenMap[$externalId],
                    'new_id_dosen' => $idDosen,
                ];
            }
        } else {
            $registrasiDosenMap[$externalId] = $idDosen;
        }
    });

if (!empty($duplicates)) {
    Log::warning('Duplikasi external_id ditemukan:', $duplicates);
    // Handle sesuai kebutuhan: skip, gunakan yang pertama, atau error
}
```

**Atau gunakan mapping yang lebih spesifik:**

```php
// Gunakan id_kelas_kuliah juga untuk mapping yang lebih spesifik
// Jika diperlukan, bisa menggunakan relasi langsung ke DosenPenugasan
```

### 3. Tambahkan Logging dan Monitoring

```php
// Log setiap mapping yang dilakukan
Log::info("Mapping dosen pengajar", [
    'id_aktivitas_mengajar' => $idAktivitasMengajar,
    'id_registrasi_dosen' => $idRegistrasiDosen,
    'id_dosen_lokal' => $idDosenLokal,
    'id_kelas_kuliah' => $idKelasKuliah,
    'action' => $existing ? 'updated' : 'created',
]);

// Log overwrite yang terjadi
if ($existing && $existing->id_registrasi_dosen !== $idRegistrasiDosen) {
    Log::warning("Overwrite detected", [
        'id_aktivitas_mengajar' => $idAktivitasMengajar,
        'old_id_registrasi_dosen' => $existing->id_registrasi_dosen,
        'new_id_registrasi_dosen' => $idRegistrasiDosen,
    ]);
}
```

### 4. Validasi Data Sebelum Sync

```php
// Validasi id_registrasi_dosen ada di mapping
if (!isset($this->registrasiDosenMap[$idRegistrasiDosen])) {
    Log::warning("id_registrasi_dosen tidak ditemukan di mapping", [
        'id_registrasi_dosen' => $idRegistrasiDosen,
        'id_kelas_kuliah' => $idKelasKuliah,
    ]);
    $unmatched++;
    continue;
}

// Validasi id_aktivitas_mengajar tidak null
if (empty($idAktivitasMengajar)) {
    Log::warning("id_aktivitas_mengajar kosong", [
        'id_registrasi_dosen' => $idRegistrasiDosen,
        'id_kelas_kuliah' => $idKelasKuliah,
    ]);
    $failed++;
    continue;
}
```

### 5. Perbaiki Data yang Sudah Terlanjur Rusak

**Script untuk memperbaiki data yang sudah terlanjur rusak:**

```php
// 1. Identifikasi record yang memiliki id_dosen sama untuk kelas yang sama
// 2. Ambil data dari API untuk kelas tersebut
// 3. Update menggunakan id_aktivitas_mengajar sebagai key
// 4. Hapus duplikasi jika ada
```

---

## 🛠 IMPLEMENTASI PERBAIKAN

### Prioritas 1: Fix Sync Logic (KRITIS)

1. Ubah sync logic untuk menggunakan `id_aktivitas_mengajar` sebagai key
2. Tambahkan validasi mapping
3. Tambahkan logging

### Prioritas 2: Investigasi Root Cause Mapping

1. Cek mengapa ada multiple external_id untuk satu id_dosen
2. Apakah ini masalah di sync DosenPenugasan?
3. Apakah perlu normalisasi data DosenPenugasan?

### Prioritas 3: Perbaiki Data yang Rusak

1. Buat script untuk re-sync data yang sudah terlanjur rusak
2. Validasi data setelah perbaikan
3. Monitor sync berikutnya

---

## 📊 METRIK YANG PERLU DIMONITOR

1. **Jumlah mapping duplikat** (external_id → id_dosen)
2. **Jumlah overwrite** yang terjadi saat sync
3. **Jumlah record tanpa id_aktivitas_mengajar**
4. **Jumlah kelas dengan id_dosen berulang**
5. **Jumlah sync yang gagal karena mapping tidak ditemukan**

---

## ✅ CHECKLIST PERBAIKAN

- [ ] Ubah sync logic untuk menggunakan `id_aktivitas_mengajar` sebagai key
- [ ] Tambahkan validasi mapping registrasi dosen
- [ ] Tambahkan logging untuk monitoring
- [ ] Investigasi root cause multiple external_id untuk satu id_dosen
- [ ] Buat script untuk memperbaiki data yang sudah rusak
- [ ] Test sync dengan data sample
- [ ] Monitor sync berikutnya untuk memastikan tidak ada overwrite
- [ ] Dokumentasikan perubahan

---

## 📝 CATATAN TAMBAHAN

1. **GetDictionary API**: Tidak bisa diakses saat investigasi (network blocked), tapi struktur sudah diketahui dari migration dan kode
2. **Database**: PostgreSQL memerlukan syntax berbeda untuk HAVING clause
3. **Constraint**: Unique constraint pada `(id_kelas_kuliah, id_dosen)` sudah benar, tapi tidak cukup ketika mapping salah

---

**Disusun oleh**: AI Assistant  
**Reviewed by**: [Tunggu Review]  
**Status**: ✅ Siap untuk Implementasi
