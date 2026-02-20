<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Agama;
use App\Models\AlatTransportasi;
use App\Models\JenisTinggal;
use App\Models\JenjangPendidikan;
use App\Models\Pekerjaan;
use App\Models\Penghasilan;

use App\Models\Negara;
use App\Models\ReferenceWilayah;
use App\Models\Mahasiswa;
use App\Http\Requests\Api\V1\StoreMahasiswaRequest;
use Illuminate\Support\Facades\DB;

use App\Services\Feeder\Reference\ReferenceJenisPendaftaranService;
use App\Services\Feeder\Reference\ReferenceJalurPendaftaranService;
use App\Services\Feeder\Reference\ReferenceSemesterService;
use App\Services\Feeder\Reference\ReferencePembiayaanService;
use App\Services\Feeder\Reference\ReferenceProgramStudiService;
use App\Services\Feeder\Reference\ReferenceProfilPTService;

use App\Services\Feeder\Reference\ReferenceDataSyncService;

class MahasiswaController extends Controller
{
    public function __construct(
        protected ReferenceJenisPendaftaranService $jenisPendaftaranService,
        protected ReferenceJalurPendaftaranService $jalurPendaftaranService,
        protected ReferenceSemesterService $semesterService,
        protected ReferencePembiayaanService $pembiayaanService,
        protected ReferenceProgramStudiService $programStudiService,
        protected ReferenceProfilPTService $profilPTService,
        protected ReferenceDataSyncService $refSyncService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswa = Mahasiswa::with(['riwayatAktif.prodi', 'agama'])
            ->orderBy('nama_mahasiswa')
            ->paginate(10);

        return view('mahasiswa.index', compact('mahasiswa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agamas = Agama::orderBy('id_agama', 'asc')->pluck('nama_agama', 'id_agama');
        $jenisTinggals = JenisTinggal::orderBy('id_jenis_tinggal', 'asc')->pluck('nama_jenis_tinggal', 'id_jenis_tinggal');
        $alatTransportasis = AlatTransportasi::orderBy('id_alat_transportasi', 'asc')->pluck('nama_alat_transportasi', 'id_alat_transportasi');
        $jenjangPendidikans = JenjangPendidikan::orderBy('id_jenjang_didik', 'asc')->pluck('nama_jenjang_didik', 'id_jenjang_didik');
        $pekerjaans = Pekerjaan::orderBy('id_pekerjaan', 'asc')->pluck('nama_pekerjaan', 'id_pekerjaan');
        $penghasilans = Penghasilan::orderBy('id_penghasilan', 'asc')->pluck('nama_penghasilan', 'id_penghasilan');
        $negaras = Negara::orderBy('nama_negara', 'asc')->pluck('nama_negara', 'id_negara');

        $provinsis = ReferenceWilayah::provinsi()->orderBy('nama_wilayah')->get()->mapWithKeys(function ($item) {
            return [trim($item->id_wilayah) => $item->nama_wilayah];
        });

        return view('mahasiswa.create', compact(
            'agamas',
            'jenisTinggals',
            'alatTransportasis',
            'jenjangPendidikans',
            'pekerjaans',
            'penghasilans',
            'negaras',
            'provinsis'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMahasiswaRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Mahasiswa::create([
                    ...$request->validated(),
                    'id_wilayah' => $request->id_wilayah, // Ensure this maps to kecamatan_id from form
                ]);
            });

            return redirect()
                ->route('admin.mahasiswa.index')
                ->with('success', 'Data mahasiswa berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Redirect to detail view which serves as the main entry point
        return redirect()->route('admin.mahasiswa.detail', $id);
    }

    /**
     * Show the detail/edit form for the specified resource.
     */
    public function detail(string $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        // Fetch reference data for the edit form
        $agamas = Agama::orderBy('id_agama', 'asc')->pluck('nama_agama', 'id_agama');
        $jenisTinggals = JenisTinggal::orderBy('id_jenis_tinggal', 'asc')->pluck('nama_jenis_tinggal', 'id_jenis_tinggal');
        $alatTransportasis = AlatTransportasi::orderBy('id_alat_transportasi', 'asc')->pluck('nama_alat_transportasi', 'id_alat_transportasi');
        $jenjangPendidikans = JenjangPendidikan::orderBy('id_jenjang_didik', 'asc')->pluck('nama_jenjang_didik', 'id_jenjang_didik');
        $pekerjaans = Pekerjaan::orderBy('id_pekerjaan', 'asc')->pluck('nama_pekerjaan', 'id_pekerjaan');
        $penghasilans = Penghasilan::orderBy('id_penghasilan', 'asc')->pluck('nama_penghasilan', 'id_penghasilan');
        $negaras = Negara::orderBy('nama_negara', 'asc')->pluck('nama_negara', 'id_negara');

        // Fetch all Provinsi options for the dropdown
        $provinsis = ReferenceWilayah::provinsi()->orderBy('nama_wilayah')->get()->mapWithKeys(function ($item) {
            return [trim($item->id_wilayah) => $item->nama_wilayah];
        });

        // Resolve Wilayah Hierarchy (Kecamatan -> Kabupaten -> Provinsi) with trimming
        $wilayahData = ReferenceWilayah::resolveHierarchy($mahasiswa->id_wilayah);

        // Pre-build Kabupaten & Kecamatan option lists for the selected province/kabupaten
        $kabupatenOptions = [];
        $kecamatanOptions = [];

        if ($wilayahData['provinsi']) {
            $kabupatenOptions = ReferenceWilayah::getKabupatenByProvinsi($wilayahData['provinsi']->id_wilayah)
                ->mapWithKeys(fn($item) => [trim($item->id_wilayah) => $item->nama_wilayah])
                ->toArray();
        }

        if ($wilayahData['kabupaten']) {
            $kecamatanOptions = ReferenceWilayah::getKecamatanByKabupaten($wilayahData['kabupaten']->id_wilayah)
                ->mapWithKeys(fn($item) => [trim($item->id_wilayah) => $item->nama_wilayah])
                ->toArray();
        }

        return view('mahasiswa.show', compact(
            'mahasiswa',
            'agamas',
            'jenisTinggals',
            'alatTransportasis',
            'jenjangPendidikans',
            'pekerjaans',
            'penghasilans',
            'negaras',
            'provinsis',
            'wilayahData',
            'kabupatenOptions',
            'kecamatanOptions'
        ));
    }

    public function histori(string $id)
    {
        $mahasiswa = Mahasiswa::with('riwayatPendidikans')->findOrFail($id);

        // Reference data for modal select options
        $jenisPendaftaran = $this->jenisPendaftaranService->get();
        $jalurPendaftaran = $this->jalurPendaftaranService->get();
        $semesters = $this->semesterService->getAktif();
        $pembiayaans = $this->pembiayaanService->get();
        $programStudis = $this->programStudiService->get();
        $profilPT = $this->profilPTService->getOwn();
        $perguruanTinggiList = $this->refSyncService->getAllPtExcludeLocal();

        return view('mahasiswa.show', compact(
            'mahasiswa',
            'jenisPendaftaran',
            'jalurPendaftaran',
            'semesters',
            'pembiayaans',
            'programStudis',
            'profilPT',
            'perguruanTinggiList'
        ));
    }

    public function krs(string $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return redirect()->route('admin.mahasiswa.detail', $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Logic to update data would go here
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Logic to delete data would go here
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Generate random data for testing.
     */
    public function random()
    {
        $faker = \Faker\Factory::create('id_ID');

        // Get random valid references
        $agama = Agama::inRandomOrder()->first();
        $jenisTinggal = JenisTinggal::inRandomOrder()->first();
        $alatTransportasi = AlatTransportasi::inRandomOrder()->first();
        $jenjangPendidikan = JenjangPendidikan::inRandomOrder()->first();
        $pekerjaan = Pekerjaan::inRandomOrder()->first();
        $penghasilan = Penghasilan::inRandomOrder()->first();

        // Wilayah (Kecamatan -> Kabupaten -> Provinsi)
        // Ensure we get a valid Kecamatan (Level 3)
        $kecamatan = ReferenceWilayah::where('id_level_wilayah', 3)->inRandomOrder()->first();

        $kabupaten = null;
        $provinsi = null;

        if ($kecamatan) {
            // Try relation first, fallback to manual fetch with trim
            $kabupaten = $kecamatan->parent;
            if (!$kabupaten && $kecamatan->id_induk_wilayah) {
                $kabupaten = ReferenceWilayah::find(trim($kecamatan->id_induk_wilayah));
            }

            if ($kabupaten) {
                // Try relation first, fallback to manual fetch with trim
                $provinsi = $kabupaten->parent;
                if (!$provinsi && $kabupaten->id_induk_wilayah) {
                    $provinsi = ReferenceWilayah::find(trim($kabupaten->id_induk_wilayah));
                }
            }
        }

        $data = [
            // Data Utama
            'nama_mahasiswa' => $faker->name,
            'jenis_kelamin' => $faker->randomElement(['L', 'P']),
            'tempat_lahir' => $faker->city,
            'tanggal_lahir' => $faker->date('Y-m-d', '-18 years'),
            'id_agama' => $agama ? $agama->id_agama : null,
            'nik' => $faker->numerify('################'), // 16 digits
            'nisn' => $faker->numerify('##########'), // 10 digits
            'kewarganegaraan' => 'ID', // Simplification, mostly ID
            'kelurahan' => $faker->streetName,
            'penerima_kps' => $faker->boolean,
            'no_hp' => $faker->numerify('08##########'), // Map to 'handphone' field
            'email' => $faker->unique()->safeEmail,

            // Wilayah Cascade
            'provinsi_id' => $provinsi ? $provinsi->id_wilayah : null,
            'kabupaten_id' => $kabupaten ? $kabupaten->id_wilayah : null,
            'id_wilayah' => $kecamatan ? $kecamatan->id_wilayah : null,

            // Alamat
            'jalan' => $faker->streetAddress,
            'dusun' => $faker->streetName,
            'rt' => $faker->numberBetween(1, 20),
            'rw' => $faker->numberBetween(1, 10),
            'kode_pos' => $faker->postcode,
            'id_jenis_tinggal' => $jenisTinggal ? $jenisTinggal->id_jenis_tinggal : null,
            'id_alat_transportasi' => $alatTransportasi ? $alatTransportasi->id_alat_transportasi : null,

            // Orang Tua (Ayah)
            'nik_ayah' => $faker->numerify('################'),
            'nama_ayah' => $faker->name('male'),
            'tgl_lahir_ayah' => $faker->date('Y-m-d', '-50 years'),
            'id_pendidikan_ayah' => $jenjangPendidikan ? $jenjangPendidikan->id_jenjang_didik : null,
            'id_pekerjaan_ayah' => $pekerjaan ? $pekerjaan->id_pekerjaan : null,
            'id_penghasilan_ayah' => $penghasilan ? $penghasilan->id_penghasilan : null,

            // Orang Tua (Ibu)
            'nik_ibu' => $faker->numerify('################'),
            'nama_ibu_kandung' => $faker->name('female'),
            'tgl_lahir_ibu' => $faker->date('Y-m-d', '-45 years'),
            'id_pendidikan_ibu' => $jenjangPendidikan ? $jenjangPendidikan->id_jenjang_didik : null,
            'id_pekerjaan_ibu' => $pekerjaan ? $pekerjaan->id_pekerjaan : null,
            'id_penghasilan_ibu' => $penghasilan ? $penghasilan->id_penghasilan : null,

            // Wali (Optional, 50% chance)
            'nama_wali' => $faker->boolean ? $faker->name : null,
            // 'hubungan_wali' => not in request
        ];

        // Adjust field names to match form inputs exactly
        $data['handphone'] = $data['no_hp'];
        unset($data['no_hp']);

        return response()->json($data);
    }
}
