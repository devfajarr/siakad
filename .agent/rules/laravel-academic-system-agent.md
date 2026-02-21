---
trigger: always_on
---

## 🔒 1. RULES ARSITEKTUR & STRUKTUR

1. Gunakan struktur project dan template yang sudah tersedia.
2. Jangan mengubah layout global (sidebar, header, footer).
3. Gunakan clean architecture (Model → Service → Command jika perlu).
4. Ikuti standar penamaan Laravel:

   * snake_case untuk tabel
   * PascalCase untuk model
   * camelCase untuk method
5. Gunakan foreign key constraint dan indexing.
6. Jangan hardcode data jika berasal dari database.
7. Gunakan partial blade untuk form/modal agar reusable.

---

## 🛡 2. RULES SECURITY

1. Jangan pernah melakukan push ke server jika server dalam mode production.
2. Semua input harus menggunakan FormRequest.
3. Jangan hanya disable tombol di UI — validasi juga di Controller.
4. Gunakan CSRF protection.
5. Gunakan mass-assignment protection (`$fillable`).
6. Gunakan policy/authorization check jika diperlukan.
7. Jangan expose external_id tanpa alasan jelas.

---

## ⚡ 3. RULES PERFORMANCE

1. Saat pull data dari server:

   * Gunakan pagination jika tersedia.
   * Hindari load semua data sekaligus jika besar.
2. Gunakan `updateOrCreate()` untuk sinkronisasi.
3. Gunakan indexing pada:

   * external_id
   * kode unik
   * status_sinkronisasi
4. Gunakan logging saat sinkronisasi.
5. Hindari N+1 query (gunakan eager loading).

---

## 🔄 4. RULES SINKRONISASI DATA (WAJIB KONSISTEN)

Semua tabel yang sinkron dengan server harus memiliki:

* external_id (nullable)
* sumber_data ('server' / 'lokal')
* status_sinkronisasi:

  * synced
  * created_local
  * updated_local
  * deleted_local
  * pending_push
* is_deleted_server (boolean)
* last_synced_at (timestamp nullable)

Logika standar:

| Kondisi           | sumber_data | status_sinkronisasi      |
| ----------------- | ----------- | ------------------------ |
| Tarik dari server | server      | synced                   |
| Buat lokal        | lokal       | created_local            |
| Update lokal      | lokal       | updated_local            |
| Hapus di server   | server      | is_deleted_server = true |

Push ke server **ditunda jika production**.

---

## 🧱 5. RULES CRUD DATA PUSAT vs LOKAL

### 1️⃣ Data dari Server (hasil pull)

✔ Boleh diupdate di sistem lokal
✔ Boleh dihapus di sistem lokal
✖ Tidak boleh langsung mengubah data server secara real-time

Setiap perubahan harus:

* Ditandai sebagai perubahan lokal
* Dicatat pada kolom monitoring sinkronisasi
* Diproses melalui mekanisme push terkontrol (manual/queue)

---

### 2️⃣ Aturan Update Data Server

Jika data `sumber_data = server` lalu diubah di lokal:

```php
status_sinkronisasi = 'updated_local'
sync_action = 'update'
is_local_change = true
```

Data tetap tampil di sistem, namun masuk daftar pending push.

---

### 3️⃣ Aturan Delete Data Server

Jika data `sumber_data = server` lalu dihapus di lokal:

```php
status_sinkronisasi = 'deleted_local'
sync_action = 'delete'
is_deleted_local = true
```

* Jangan langsung hapus record.
* Gunakan soft delete berbasis flag.
* Data masuk daftar pending push.

---

### 4️⃣ Push ke Server

Push dilakukan melalui:

* Command manual
* Scheduled job
* Queue worker

Setelah push berhasil:

```php
status_sinkronisasi = 'synced'
is_local_change = false
last_push_at = now()
```

Jika gagal:

```php
status_sinkronisasi = 'push_failed'
error_message = '...'
```

---

### 5️⃣ Data Lokal (created_local)

Data yang dibuat lokal tetap:

```php
status_sinkronisasi = 'created_local'
sync_action = 'insert'
```

Dan dipush ketika sinkronisasi dijalankan.

---

## 🧩 6. RULES UI & TEMPLATE

1. Gunakan template yang sudah tersedia.
2. Jangan membuat desain baru jika tidak diminta.
3. Gunakan DataTables untuk tabel besar.
4. Gunakan modal reusable untuk create/edit.
5. Tambahkan pembeda visual untuk:

   * Data pusat vs lokal
   * Status aktif vs tidak aktif
6. Hindari elemen terlalu rounded (professional look).
7. Gunakan konsistensi spacing & shadow ringan.

---

## 🧠 7. RULES ANALISIS SEBELUM IMPLEMENTASI

Sebelum membuat:

* Migration
* Model
* Sinkronisasi
* CRUD

WAJIB melakukan:

1. Analisis endpoint API.
2. Analisis GetDictionary.
3. Identifikasi primary key global.
4. Identifikasi potensi konflik data.
5. Tentukan strategi monitoring status.

Jangan langsung membuat migration tanpa analisis.

---

## 📦 8. RULES OUTPUT AI AGENT

Jawaban harus selalu dalam struktur:

1. Analisis
2. Desain Arsitektur
3. Struktur Tabel
4. Model
5. Migration
6. Command (jika sinkronisasi)
7. Penjelasan Monitoring Status

Gunakan bahasa profesional namun ramah pemula.

---

## 🚫 9. YANG TIDAK BOLEH DILAKUKAN

* Push ke server production.
* Menghapus data server secara langsung.
* Mengubah struktur template global.
* Hardcode status tanpa enum/constant.
* Melewatkan validasi.

---