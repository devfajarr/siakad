<?php

namespace App\Http\Controllers;

use App\Http\Requests\KelasKuliah\StoreKelasKuliahRequest;
use App\Http\Requests\KelasKuliah\UpdateKelasKuliahRequest;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KelasKuliahController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = KelasKuliah::with([
                'mataKuliah',
                'semester',
                'dosenPengajar.dosenPenugasan.dosen',
            ])
                ->select('kelas_kuliah.*')
                ->withCount('pesertaKelasKuliah');

            // 1. Filter by specific columns
            if ($request->has('id_semester') && $request->id_semester != '') {
                $query->where('id_semester', $request->id_semester);
            }

            if ($request->has('status_sinkronisasi') && $request->status_sinkronisasi != '') {
                $query->where('status_sinkronisasi', $request->status_sinkronisasi);
            }

            // 2. Search
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('nama_kelas_kuliah', 'like', "%{$searchValue}%")
                        ->orWhereHas('mataKuliah', function ($mk) use ($searchValue) {
                            $mk->where('nama_mk', 'like', "%{$searchValue}%")
                                ->orWhere('kode_mk', 'like', "%{$searchValue}%");
                        });
                });
            }

            // 3. Sorting
            if ($request->has('order')) {
                $order = $request->input('order')[0];
                $columnIndex = $order['column'];
                $columnDir = $order['dir'];

                // Column mapping based on index.blade.php
                // 0: action, 1: status, 2: no, 3: semester, 4: kode_mk, 5: nama_mk, 6: nama_kelas, 7: bobot
                $columns = [
                    3 => 'id_semester',
                    6 => 'nama_kelas_kuliah',
                    7 => 'sks_mk',
                ];

                if (isset($columns[$columnIndex])) {
                    $query->orderBy($columns[$columnIndex], $columnDir);
                } elseif ($columnIndex == 4 || $columnIndex == 5) {
                    // Complex sorting for related columns (optional, skips for simplicity or implements join)
                    // For now, default to created_at or id to avoid errors
                    $query->orderBy('nama_kelas_kuliah', $columnDir);
                } else {
                    $query->orderBy('updated_at', 'desc');
                }
            } else {
                $query->orderBy('updated_at', 'desc');
            }

            // 4. Pagination
            $totalRecords = KelasKuliah::count();
            $filteredRecords = $query->count();

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);

            $data = $query->skip($start)->take($length)->get();

            // 5. Format Data
            $formattedData = $data->map(function ($row, $index) use ($start) {
                // Action Button
                $btn = '<div class="d-flex gap-1">';
                $btn .= '<a href="' . route('admin.kelas-kuliah.show', $row->id) . '" class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail"><i class="ri-eye-line"></i></a>';
                // Allowed to edit and delete for both Server and Lokal based on Offline-First CRUD rules
                $btn .= '<a href="' . route('admin.kelas-kuliah.edit', $row->id) . '" class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit"><i class="ri-pencil-line"></i></a>';
                $btn .= '<form action="' . route('admin.kelas-kuliah.destroy', $row->id) . '" method="POST" class="d-inline delete-form">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="button" class="btn btn-icon btn-sm btn-danger rounded-pill btn-delete" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                         </form>';
                $btn .= '</div>';

                // Status Badge
                $statusClass = 'bg-label-secondary';
                $statusText = 'Unknown';
                if ($row->is_deleted_server) {
                    $statusClass = 'bg-label-danger';
                    $statusText = 'Dihapus Server';
                } else {
                    if ($row->sumber_data === 'server' && $row->status_sinkronisasi === 'synced') {
                        $statusClass = 'bg-label-success';
                        $statusText = 'Server (Synced)';
                    } elseif ($row->sumber_data === 'lokal' && $row->status_sinkronisasi === 'created_local') {
                        $statusClass = 'bg-label-warning';
                        $statusText = 'Lokal (Belum Push)';
                    } elseif ($row->sumber_data === 'server' && $row->status_sinkronisasi === 'updated_local') {
                        $statusClass = 'bg-label-info';
                        $statusText = 'Server (Update Lokal)';
                    } elseif ($row->status_sinkronisasi === 'push_failed') {
                        $statusClass = 'bg-label-danger';
                        $statusText = 'Gagal Push';
                    } else {
                        switch ($row->status_sinkronisasi) {
                            case 'synced':
                                $statusClass = 'bg-label-success';
                                $statusText = 'Sudah Sync';
                                break;
                            case 'created_local':
                                $statusClass = 'bg-label-info';
                                $statusText = 'Belum Sync (Lokal)';
                                break;
                            case 'updated_local':
                                $statusClass = 'bg-label-warning';
                                $statusText = 'Update Lokal';
                                break;
                            case 'pending_push':
                                $statusClass = 'bg-label-secondary';
                                $statusText = 'Pending Push';
                                break;
                        }
                    }
                }
                $statusBadge = '<span class="badge ' . $statusClass . ' rounded-pill">' . $statusText . '</span>';

                // Dosen
                $dosenNames = '-';
                if ($row->dosenPengajar && $row->dosenPengajar->isNotEmpty()) {
                    $dosenNames = $row->dosenPengajar->map(function ($dp) {
                        // Gunakan nullsafe operator (?->) untuk keamanan maksimal
                        // Artinya: Ambil dosenPenugasan, jika ada ambil dosen-nya, jika ada ambil nama-nya.
                        return $dp->dosenPenugasan?->dosen?->nama ?? '-';
                    })->implode(', <br>');
                }

                return [
                    'action' => $btn,
                    'status' => $statusBadge,
                    'DT_RowIndex' => $start + $index + 1,
                    'semester_nama' => $row->semester ? $row->semester->nama_semester : '-',
                    'kode_mk' => $row->mataKuliah ? '<span class="fw-semibold text-primary">' . $row->mataKuliah->kode_mk . '</span>' : '-',
                    'nama_mk' => $row->mataKuliah ? $row->mataKuliah->nama_mk : '-',
                    'nama_kelas_kuliah' => $row->nama_kelas_kuliah,
                    'bobot_sks' => $row->sks_mk,
                    'dosen_pengajar' => $dosenNames,
                    'peserta_kelas' => $row->peserta_kelas_kuliah_count,
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords, // Total records without filter
                'recordsFiltered' => $filteredRecords, // Total records with filter
                'data' => $formattedData,
            ]);
        }

        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        // Default active semester: a_periode_aktif = 1, order by id_semester desc
        $activeSemester = Semester::where('a_periode_aktif', 1)
            ->orderBy('id_semester', 'desc')
            ->first();

        // Fallback if no active semester found
        if (!$activeSemester) {
            $activeSemester = Semester::orderBy('id_semester', 'desc')->first();
        }

        return view('admin.kelas-kuliah.index', compact('semesters', 'activeSemester'));
    }

    public function create()
    {
        $prodis = ProgramStudi::orderBy('nama_program_studi')->get();
        $semesters = Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->get();
        $mataKuliahs = MataKuliah::orderBy('nama_mk')->get();

        return view('admin.kelas-kuliah.create', compact('prodis', 'semesters', 'mataKuliahs'));
    }

    public function store(StoreKelasKuliahRequest $request)
    {
        try {
            $data = $request->validated();

            // Set default values for local data (monitoring & flags)
            $data['id_kelas_kuliah'] = Str::uuid();
            $data['sumber_data'] = 'lokal';
            $data['status_sinkronisasi'] = KelasKuliah::STATUS_CREATED_LOCAL;
            $data['sync_action'] = 'insert';
            $data['is_local_change'] = true;
            $data['is_deleted_server'] = false;
            $data['is_deleted_local'] = false;

            // Default PDITT flag if not provided (Feeder expects 0/1)
            if (!array_key_exists('apa_untuk_pditt', $data) || $data['apa_untuk_pditt'] === null) {
                $data['apa_untuk_pditt'] = 0;
            }

            // Get SKS from Mata Kuliah master (wajib sesuai skema lokal)
            $mataKuliah = MataKuliah::where('id_matkul', $data['id_matkul'])->first();
            if ($mataKuliah) {
                $data['sks_mk'] = $mataKuliah->sks;
                $data['sks_tm'] = $mataKuliah->sks_tatap_muka;
                $data['sks_prak'] = $mataKuliah->sks_praktek;
                $data['sks_prak_lap'] = $mataKuliah->sks_praktek_lapangan;
                $data['sks_sim'] = $mataKuliah->sks_simulasi;
            }

            KelasKuliah::create($data);

            return redirect()->route('admin.kelas-kuliah.index')
                ->with('success', 'Data Kelas Kuliah berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail Kelas Kuliah (mode view / readonly).
     */
    public function show(KelasKuliah $kelasKuliah)
    {
        $kelasKuliah->load([
            'programStudi',
            'semester',
            'mataKuliah',
            'dosenPengajar.dosen',
            'dosenPengajar.dosenAliasLokal',
            'pesertaKelasKuliah.riwayatPendidikan.mahasiswa',
            'pesertaKelasKuliah.riwayatPendidikan.programStudi',
            'jadwalKuliahs.ruang',
        ]);

        $tahunAjaranId = $kelasKuliah->semester?->id_tahun_ajaran;

        $daftarDosenQuery = Dosen::query();
        if (!empty($tahunAjaranId)) {
            $daftarDosenQuery->whereHas('penugasans', function ($query) use ($tahunAjaranId) {
                $query->where('id_tahun_ajaran', $tahunAjaranId);
            });
        }

        $daftarDosen = $daftarDosenQuery
            ->orderBy('nama')
            ->get();

        if ($daftarDosen->isEmpty()) {
            $daftarDosen = Dosen::query()
                ->orderBy('nama')
                ->get();
        }

        $daftarDosenLokal = Dosen::lokal()
            ->orderBy('nama')
            ->get();

        // ─── AMBIL MAHASISWA AKTIF ───────────────────────────────────────────
        // Untuk input kolektif/individu, kita ambil mahasiswa yang belum terdaftar di kelas ini
        // dan ideally difilter per prodi (untuk kemudahan, ambil semua yang statusnya Aktif).
        $pesertaIds = $kelasKuliah->pesertaKelasKuliah->pluck('riwayat_pendidikan_id')->toArray();
        $daftarMahasiswa = \App\Models\RiwayatPendidikan::with(['mahasiswa', 'programStudi'])
            ->whereNotIn('id', $pesertaIds)
            // Filter by prodi kelas if needed: ->where('id_prodi', $kelasKuliah->id_prodi)
            ->get()
            ->sortBy('mahasiswa.nama');

        $jenisEvaluasiOptions = [
            '1' => 'Evaluasi Akademik',
            '2' => 'Aktivitas Partisipatif',
            '3' => 'Hasil Proyek',
            '4' => 'Kognitif / Pengetahuan',
        ];

        // ─── AMBIL DATA RUANG UNTUK MODAL PENJADWALAN ───────────────────────
        $daftarRuang = \App\Models\Ruang::orderBy('nama_ruang')->get();

        $isEditMode = false;

        return view('admin.kelas-kuliah.show', compact(
            'kelasKuliah',
            'isEditMode',
            'daftarDosen',
            'daftarDosenLokal',
            'jenisEvaluasiOptions',
            'daftarMahasiswa',
            'daftarRuang'
        ));
    }

    /**
     * Tampilkan form edit Kelas Kuliah (mode edit).
     *
     * Mengikuti Global Rules: data dari server tidak boleh diubah.
     */
    public function edit(KelasKuliah $kelasKuliah)
    {
        // Allowed to edit both local and server data locally
        // Server data will be marked as updated_local

        $kelasKuliah->load([
            'programStudi',
            'semester',
            'mataKuliah',
            'dosenPengajar.dosen',
            'dosenPengajar.dosenAliasLokal',
            'pesertaKelasKuliah.riwayatPendidikan.mahasiswa',
            'pesertaKelasKuliah.riwayatPendidikan.programStudi',
        ]);

        $prodis = ProgramStudi::orderBy('nama_program_studi')->get();
        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $mataKuliahs = MataKuliah::orderBy('nama_mk')->get();

        $tahunAjaranId = $kelasKuliah->semester?->id_tahun_ajaran;

        $daftarDosenQuery = Dosen::query();
        if (!empty($tahunAjaranId)) {
            $daftarDosenQuery->whereHas('penugasans', function ($query) use ($tahunAjaranId) {
                $query->where('id_tahun_ajaran', $tahunAjaranId);
            });
        }

        $daftarDosen = $daftarDosenQuery
            ->orderBy('nama')
            ->get();

        if ($daftarDosen->isEmpty()) {
            $daftarDosen = Dosen::query()
                ->orderBy('nama')
                ->get();
        }

        $daftarDosenLokal = Dosen::lokal()
            ->orderBy('nama')
            ->get();

        // ─── AMBIL MAHASISWA AKTIF ───────────────────────────────────────────
        $pesertaIds = $kelasKuliah->pesertaKelasKuliah->pluck('riwayat_pendidikan_id')->toArray();
        $daftarMahasiswa = \App\Models\RiwayatPendidikan::with(['mahasiswa', 'programStudi'])
            ->whereNotIn('id', $pesertaIds)
            ->get()
            ->sortBy('mahasiswa.nama');

        $jenisEvaluasiOptions = [
            '1' => 'Evaluasi Akademik',
            '2' => 'Aktivitas Partisipatif',
            '3' => 'Hasil Proyek',
            '4' => 'Kognitif / Pengetahuan',
        ];

        $isEditMode = true;

        return view('admin.kelas-kuliah.edit', compact(
            'kelasKuliah',
            'isEditMode',
            'daftarDosen',
            'daftarDosenLokal',
            'jenisEvaluasiOptions',
            'prodis',
            'semesters',
            'mataKuliahs',
            'daftarMahasiswa'
        ));
    }

    /**
     * Update data Kelas Kuliah lokal.
     *
     * Mengikuti dictionary UpdateKelasKuliah:
     * hanya field yang diizinkan yang boleh diubah dan setiap perubahan
     * akan menandai record sebagai update lokal (dirty) untuk kebutuhan sync.
     */
    public function update(UpdateKelasKuliahRequest $request, KelasKuliah $kelasKuliah)
    {
        // Allowed to update locally

        $data = $request->validated();

        // Jika mata kuliah diganti, pastikan bobot SKS mengikuti master Mata Kuliah
        if (array_key_exists('id_matkul', $data) && $data['id_matkul']) {
            $mataKuliah = MataKuliah::where('id_matkul', $data['id_matkul'])->first();

            if ($mataKuliah) {
                $data['sks_mk'] = $mataKuliah->sks;
                $data['sks_tm'] = $mataKuliah->sks_tatap_muka;
                $data['sks_prak'] = $mataKuliah->sks_praktek;
                $data['sks_prak_lap'] = $mataKuliah->sks_praktek_lapangan;
                $data['sks_sim'] = $mataKuliah->sks_simulasi;
            }
        }

        // Monitoring sync: tandai sebagai update lokal (dirty) jika sebelumnya sudah pernah tersinkronisasi atau dari server
        $isSyncedBefore = in_array($kelasKuliah->status_sinkronisasi, [
            KelasKuliah::STATUS_SYNCED,
            KelasKuliah::STATUS_PUSH_SUCCESS,
        ], true);

        if ($kelasKuliah->sumber_data === 'server') {
            $data['status_sinkronisasi'] = KelasKuliah::STATUS_UPDATED_LOCAL;
            $data['sync_action'] = 'update';
            $data['is_local_change'] = true;
        } else {
            // Jika lokal, update_local action jika sudah pernah synced, kalau belum biarkan
            if ($isSyncedBefore) {
                $data['status_sinkronisasi'] = KelasKuliah::STATUS_UPDATED_LOCAL;
                $data['sync_action'] = 'update';
            }
            $data['is_local_change'] = true;
        }

        $kelasKuliah->update($data);

        return redirect()
            ->route('admin.kelas-kuliah.show', $kelasKuliah->id)
            ->with('success', 'Data Kelas Kuliah berhasil diperbarui.');
    }

    /**
     * Hapus Kelas Kuliah.
     *
     * Jika belum pernah sync ke server → hard delete.
     * Jika sudah pernah sync → soft delete via flag is_deleted_server + status_sinkronisasi deleted_local.
     */
    public function destroy(KelasKuliah $kelasKuliah)
    {
        if ($kelasKuliah->sumber_data === 'server') {
            // Soft delete for server data
            $kelasKuliah->update([
                'is_deleted_local' => true,
                'status_sinkronisasi' => KelasKuliah::STATUS_DELETED_LOCAL,
                'sync_action' => 'delete',
                'is_local_change' => true,
                'sync_error_message' => null,
            ]);
        } else {
            // Data Lokal
            // Anggap sudah pernah sync jika pernah dipush atau status bukan created_local
            $hasEverSynced = $kelasKuliah->last_push_at !== null
                || in_array($kelasKuliah->status_sinkronisasi, [
                    KelasKuliah::STATUS_SYNCED,
                    KelasKuliah::STATUS_UPDATED_LOCAL,
                    KelasKuliah::STATUS_DELETED_LOCAL,
                    KelasKuliah::STATUS_PUSH_SUCCESS,
                ], true);

            if (!$hasEverSynced) {
                // Hard delete untuk data lokal yang belum pernah tersinkronisasi
                $kelasKuliah->delete();
            } else {
                // Soft delete via flag dan status sinkronisasi
                $kelasKuliah->update([
                    'is_deleted_local' => true,
                    'status_sinkronisasi' => KelasKuliah::STATUS_DELETED_LOCAL,
                    'sync_action' => 'delete',
                    'is_local_change' => true,
                    'sync_error_message' => null,
                ]);
            }
        }

        return redirect()
            ->route('admin.kelas-kuliah.index')
            ->with('success', 'Data Kelas Kuliah berhasil dihapus.');
    }
}
