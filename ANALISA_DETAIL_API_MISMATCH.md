# 🔍 ANALISA ERROR: Detail API Mismatch untuk DetailBiodataDosen

**Tanggal**: 20 Februari 2026  
**Status**: ⚠️ **MASALAH TERIDENTIFIKASI**

---

## 📋 EXECUTIVE SUMMARY

**Masalah**: API `DetailBiodataDosen` mengembalikan data dosen yang salah ketika dipanggil dengan ID dosen tertentu. API mengembalikan ID dosen yang sama (`b185521e-61a4-4b64-bddb-6632a0198075`) untuk beberapa dosen berbeda.

**Dampak**: 
- Data `tempat_lahir` tidak bisa diambil dari detail API untuk beberapa dosen
- Sync tetap berjalan karena kode sudah memiliki validasi untuk skip data yang tidak sesuai
- Tidak ada data yang salah yang tersimpan ke database

---

## 🚨 TEMUAN ERROR

### Pola Error yang Terjadi

```
[2026-02-20 08:24:07] AMIR KHASAN: 
  Expected: 4f44131a-06b5-47e1-a144-adc7378b0443
  Got:      b185521e-61a4-4b64-bddb-6632a0198075

[2026-02-20 08:24:09] DESY NUR PRATIWI: 
  Expected: b4b9f578-d71b-4251-95f8-e0693ac66993
  Got:      b185521e-61a4-4b64-bddb-6632a0198075
```

**Observasi**:
- ID `b185521e-61a4-4b64-bddb-6632a0198075` muncul berulang untuk dosen yang berbeda
- Error terjadi konsisten untuk dosen yang sama (terjadi beberapa kali)
- API mengabaikan parameter `id_dosen` yang dikirim

---

## 🔍 ROOT CAUSE ANALYSIS

### 1. Format Parameter API Berbeda

**Lokasi**: `app/Services/Feeder/DosenFeederService.php` baris 18-23

```php
public function getDetailBiodataDosen(string $idDosen): array
{
    return $this->sendRequest('DetailBiodataDosen', [
        'id_dosen' => $idDosen,  // ❌ Menggunakan parameter langsung
    ]);
}
```

**Perbandingan dengan API lain**:
- `GetListPenugasanDosen` menggunakan `filter: "id_dosen='{$idDosen}'"`
- `GetRiwayatFungsionalDosen` menggunakan `filter: "id_dosen='{$idDosen}'"`
- `DetailBiodataDosen` menggunakan `id_dosen: $idDosen` (parameter langsung)

**Kemungkinan**: API `DetailBiodataDosen` mungkin memerlukan format `filter` seperti API lain, bukan parameter langsung.

### 2. Masalah di Sisi API Server

Kemungkinan masalah di sisi server Neo Feeder:
- API memiliki bug dan mengabaikan parameter `id_dosen`
- API menggunakan cache yang salah
- API mengembalikan data default/terakhir yang di-query
- Rate limiting atau throttling menyebabkan response yang salah

### 3. Validasi Sudah Ada (Good!)

**Lokasi**: `app/Console/Commands/SyncDosenFromPusat.php` baris 67-73

```php
if (!empty($detailData) && ($detailData['id_dosen'] ?? '') === $dosenData['id_dosen']) {
    // Safe to merge — detail matches the correct dosen
    $dosenData['tempat_lahir'] = $detailData['tempat_lahir'] ?? null;
} else {
    // Detail API returned wrong dosen — skip detail data
    Log::info("Detail API mismatch...");
}
```

✅ **Kode sudah aman** - Data yang tidak sesuai akan di-skip dan tidak tersimpan.

---

## 💡 SARAN PERBAIKAN

### ✅ **SOLUSI 1: Coba Format Filter (Recommended)**

Ubah format parameter dari `id_dosen` langsung menjadi `filter` seperti API lain:

```php
public function getDetailBiodataDosen(string $idDosen): array
{
    return $this->sendRequest('DetailBiodataDosen', [
        'filter' => "id_dosen='{$idDosen}'",  // ✅ Gunakan format filter
    ]);
}
```

**Alasan**: Konsisten dengan API lain yang bekerja dengan baik.

---

### ✅ **SOLUSI 2: Tambahkan Retry Logic dengan Delay**

Tambahkan retry logic untuk mengatasi masalah cache/timing:

```php
public function getDetailBiodataDosen(string $idDosen, int $maxRetries = 3): array
{
    $attempt = 0;
    $lastException = null;
    
    while ($attempt < $maxRetries) {
        try {
            $result = $this->sendRequest('DetailBiodataDosen', [
                'id_dosen' => $idDosen,
            ]);
            
            // Validasi response
            $detailData = isset($result[0]) ? $result[0] : (!empty($result) ? $result : []);
            if (!empty($detailData) && ($detailData['id_dosen'] ?? '') === $idDosen) {
                return $result; // ✅ Data sesuai
            }
            
            // Data tidak sesuai, retry dengan delay
            if ($attempt < $maxRetries - 1) {
                sleep(1); // Delay 1 detik sebelum retry
            }
            
        } catch (\Exception $e) {
            $lastException = $e;
        }
        
        $attempt++;
    }
    
    // Jika semua retry gagal, throw exception atau return empty
    if ($lastException) {
        throw $lastException;
    }
    
    return [];
}
```

---

### ✅ **SOLUSI 3: Tambahkan Rate Limiting**

Tambahkan delay antar request untuk menghindari masalah cache/throttling:

```php
protected function syncDosen(array $data)
{
    // ... existing code ...
    
    try {
        // Tambahkan delay sebelum call detail API
        usleep(500000); // 0.5 detik delay
        
        $detail = $this->dosenFeederService->getDetailBiodataDosen($dosenData['id_dosen']);
        // ... rest of code ...
    }
}
```

---

### ✅ **SOLUSI 4: Skip Detail API Jika Tidak Critical**

Karena detail API hanya digunakan untuk mengambil `tempat_lahir` (field opsional), bisa di-skip jika sering error:

```php
// Option: Skip detail API untuk menghindari error
$skipDetailApi = true; // Set ke false jika ingin tetap mencoba

if (!$skipDetailApi) {
    try {
        $detail = $this->dosenFeederService->getDetailBiodataDosen($dosenData['id_dosen']);
        // ... existing validation code ...
    } catch (\Exception $e) {
        // Skip jika error
    }
}
```

---

### ✅ **SOLUSI 5: Tambahkan Logging Lebih Detail**

Tambahkan logging untuk debugging lebih lanjut:

```php
try {
    Log::debug("Calling DetailBiodataDosen", [
        'requested_id_dosen' => $dosenData['id_dosen'],
        'nama_dosen' => $dosenData['nama_dosen'],
    ]);
    
    $detail = $this->dosenFeederService->getDetailBiodataDosen($dosenData['id_dosen']);
    
    Log::debug("DetailBiodataDosen response", [
        'response_count' => count($detail),
        'first_item_id' => $detail[0]['id_dosen'] ?? 'null',
    ]);
    
    // ... rest of validation code ...
} catch (\Exception $e) {
    Log::error("DetailBiodataDosen error", [
        'id_dosen' => $dosenData['id_dosen'],
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

---

## 🎯 REKOMENDASI IMPLEMENTASI

### Prioritas Tinggi (Lakukan Sekarang)

1. **Coba Solusi 1**: Ubah format parameter menjadi `filter` seperti API lain
   - Risiko rendah
   - Mudah diimplementasikan
   - Konsisten dengan pattern yang sudah bekerja

2. **Tambahkan Solusi 5**: Logging lebih detail
   - Membantu debugging lebih lanjut
   - Tidak mengubah behavior

### Prioritas Sedang (Jika Solusi 1 Tidak Berhasil)

3. **Implementasikan Solusi 2**: Retry logic dengan delay
   - Mengatasi masalah cache/timing
   - Meningkatkan reliability

4. **Implementasikan Solusi 3**: Rate limiting
   - Mengurangi beban server
   - Menghindari throttling

### Prioritas Rendah (Jika Semua Gagal)

5. **Implementasikan Solusi 4**: Skip detail API
   - Hanya jika detail API tidak critical
   - `tempat_lahir` adalah field opsional

---

## 📊 MONITORING

Setelah implementasi perbaikan, monitor:

1. **Error Rate**: Apakah error mismatch berkurang?
2. **Success Rate**: Berapa persen detail API yang berhasil?
3. **Response Time**: Apakah ada peningkatan latency?
4. **Data Quality**: Apakah data `tempat_lahir` terisi dengan benar?

---

## 🔗 FILE YANG TERKAIT

- `app/Services/Feeder/DosenFeederService.php` - Service untuk call API
- `app/Console/Commands/SyncDosenFromPusat.php` - Command yang menggunakan detail API
- `app/Services/NeoFeederService.php` - Base service untuk API calls

---

## 📝 CATATAN TAMBAHAN

- **Status Saat Ini**: Kode sudah aman karena memiliki validasi
- **Impact**: Minimal - hanya field `tempat_lahir` yang tidak terisi untuk beberapa dosen
- **Urgency**: Medium - Tidak critical, tapi sebaiknya diperbaiki untuk data completeness
