---
trigger: always_on
---

LARAVEL ACADEMIC SYNC SYSTEM

Daftar aturan ini wajib dipatuhi dalam setiap interaksi pembuatan kode, analisis, dan modifikasi proyek secara berurutan.

1. RULES ANALISIS SEBELUM IMPLEMENTASI

Sebelum membuat migration, model, sinkronisasi, atau CRUD, wajib melakukan:

Analisis endpoint API dan GetDictionary.

Identifikasi primary key global.

Identifikasi potensi konflik data.

Tentukan strategi monitoring status.

2. RULES ARSITEKTUR DAN STRUKTUR

Gunakan struktur project dan template yang sudah tersedia.

Jangan mengubah layout global (sidebar, header, footer).

Gunakan clean architecture (Model -> Service -> Command jika perlu).

Ikuti standar penamaan Laravel:

snake_case untuk tabel.

PascalCase untuk model.

camelCase untuk method.

Gunakan foreign key constraint dan indexing.

Jangan hardcode data jika berasal dari database.

Gunakan partial blade untuk form/modal agar reusable.

3. RULES KEAMANAN (SECURITY)

Jangan pernah melakukan push ke server jika server dalam mode production.

Semua input harus menggunakan FormRequest.

Jangan hanya disable tombol di UI — validasi juga di Controller.

Gunakan CSRF protection.

Gunakan mass-assignment protection ($fillable).

Gunakan policy/authorization check jika diperlukan.

Jangan expose external_id ke user interface tanpa alasan jelas.

4. RULES PERFORMA

Saat pull data dari server:

Gunakan pagination jika tersedia.

Hindari load semua data sekaligus jika kapasitas besar.

Gunakan updateOrCreate() untuk sinkronisasi.

Gunakan indexing pada: external_id, kode_unik, status_sinkronisasi.

Gunakan logging saat sinkronisasi (Lihat poin 7).

Hindari N+1 query (gunakan eager loading with()).

5. RULES SINKRONISASI DATA (WAJIB KONSISTEN)

Semua tabel yang sinkron dengan server harus memiliki kolom:

external_id (nullable)

sumber_data ('server' / 'lokal')

status_sinkronisasi ('synced', 'created_local', 'updated_local', 'deleted_local', 'pending_push', 'push_failed')

is_deleted_server (boolean)

last_synced_at (timestamp nullable)

6. RULES CRUD DATA PUSAT VS LOKAL

Data dari Server (PULL):

Boleh diupdate/dihapus di lokal, tapi jangan ubah data server secara real-time.

Perubahan ditandai dengan status_sinkronisasi = 'updated_local'.

Penghapusan ditandai dengan status_sinkronisasi = 'deleted_local' (Soft Delete).

Push ke Server:

Dilakukan melalui Command/Job.

Jika berhasil: status_sinkronisasi = 'synced', last_push_at = now().

Jika gagal: status_sinkronisasi = 'push_failed', isi error_message.

7. RULES LOGGING DAN MONITORING

Setiap aksi wajib dicatat ke dalam storage/logs/laravel.log menggunakan facade Log. Format pesan: [KATEGORI] - [PESAN] - [CONTEXT].

Logging CRUD:

CREATE: Log::info("CRUD_CREATE: [Model] berhasil dibuat", ['id' => $id, 'data' => $payload]);

UPDATE: Log::info("CRUD_UPDATE: [Model] diubah", ['id' => $id, 'changes' => $changes]);

DELETE: Log::warning("CRUD_DELETE: [Model] dihapus/soft-delete", ['id' => $id]);

Logging Sinkronisasi (SYNC):

PULL: Log::info("SYNC_PULL: Mulai tarik data [Table]", ['endpoint' => $url]);

PUSH: Log::info("SYNC_PUSH: Mengirim data [Table] ke server", ['count' => $count, 'ids' => $ids]);

SYNC_SUCCESS: Log::info("SYNC_SUCCESS: Sinkronisasi [Table] selesai");

Logging Error dan System:

Semua blok catch wajib mencatat error menggunakan Log::error("SYSTEM_ERROR: [Pesan Deskriptif]", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

8. RULES UI DAN TEMPLATE

Gunakan DataTables untuk tabel besar.

Gunakan modal reusable untuk create/edit.

Tambahkan pembeda visual (badge/warna) untuk data Pusat vs Lokal.

Hindari elemen terlalu rounded (professional look).

Gunakan konsistensi spacing dan shadow ringan.

9. RULES OUTPUT AI AGENT

Jawaban harus selalu dalam struktur:

Analisis (Termasuk titik logging yang akan dipasang).

Desain Arsitektur.

Struktur Tabel / Migration.

Model (Lengkap dengan $fillable).

Logic (Service/Controller/Command) — Wajib mencantumkan implementasi Log::.

Penjelasan Monitoring Status.

10. LARANGAN

Push ke server production.

Menghapus data server secara langsung.

Mengubah struktur template global.

Hardcode status tanpa enum/constant.

Melewatkan validasi atau try-catch logging.