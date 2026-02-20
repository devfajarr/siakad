<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kurikulums = \App\Models\Kurikulum::with(['prodi', 'semester'])->orderBy('nama_kurikulum')->get();
        return view('kurikulum.index', compact('kurikulums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodis = \App\Models\ProgramStudi::orderBy('nama_program_studi')->get();
        $semesters = \App\Models\Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->take(20)->get();
        return view('kurikulum.create', compact('prodis', 'semesters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreKurikulumRequest $request)
    {
        try {
            $data = $request->validated();

            // Set default values for local data
            $data['id_kurikulum'] = \Illuminate\Support\Str::uuid();
            $data['sumber_data'] = 'lokal';
            $data['status_sinkronisasi'] = 'created_local';
            $data['is_deleted_server'] = false;

            \App\Models\Kurikulum::create($data);

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Data Kurikulum berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kurikulum = \App\Models\Kurikulum::with(['prodi', 'semester', 'matakuliah.prodi'])->findOrFail($id);
        // Fetch all active mata kuliah for the dropdown, ordered by name
        $mataKuliahs = \App\Models\MataKuliah::orderBy('nama_mk')->get();
        return view('kurikulum.show', compact('kurikulum', 'mataKuliahs'));
    }

    /**
     * Store Mata Kuliah to Kurikulum (Pivot)
     */
    public function storeMatkul(Request $request, $id)
    {
        try {
            $kurikulum = \App\Models\Kurikulum::findOrFail($id);

            if ($kurikulum->sumber_data != 'lokal') {
                return redirect()->back()->with('error', 'Tidak dapat mengubah kurikulum dari server.');
            }

            $request->validate([
                'id_matkul' => 'required|exists:mata_kuliahs,id_matkul',
                'semester' => 'required|integer|min:1|max:14',
                'apakah_wajib' => 'nullable',
            ]);

            // Find Mata Kuliah by id_matkul (UUID) to get/derive attributes
            $matkul = \App\Models\MataKuliah::where('id_matkul', $request->id_matkul)->firstOrFail();

            $kurikulum->matakuliah()->attach($matkul->id_matkul, [
                'semester' => $request->semester,
                'apakah_wajib' => $request->has('apakah_wajib') ? 1 : 0,
                'sks_mata_kuliah' => $matkul->sks,
                'sks_tatap_muka' => $matkul->sks_tatap_muka,
                'sks_praktek' => $matkul->sks_praktek,
                'sks_praktek_lapangan' => $matkul->sks_praktek_lapangan,
                'sks_simulasi' => $matkul->sks_simulasi,
                'sumber_data' => 'lokal',
                'status_sinkronisasi' => 'created_local',
                'is_deleted_server' => false,
            ]);

            return redirect()->back()->with('success', 'Mata Kuliah berhasil ditambahkan ke Kurikulum.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan mata kuliah: ' . $e->getMessage());
        }
    }

    /**
     * Remove Mata Kuliah from Kurikulum (Pivot)
     */
    public function destroyMatkul($id, $id_matkul)
    {
        try {
            $kurikulum = \App\Models\Kurikulum::findOrFail($id);

            if ($kurikulum->sumber_data != 'lokal') {
                return redirect()->back()->with('error', 'Tidak dapat menghapus data dari kurikulum server.');
            }

            $kurikulum->matakuliah()->detach($id_matkul);

            return redirect()->back()->with('success', 'Mata Kuliah berhasil dihapus dari Kurikulum.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus mata kuliah: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kurikulum = \App\Models\Kurikulum::findOrFail($id);

        if ($kurikulum->sumber_data != 'lokal') {
            return redirect()->route('admin.kurikulum.index')
                ->with('error', 'Data dari server tidak dapat diubah.');
        }

        $prodis = \App\Models\ProgramStudi::orderBy('nama_program_studi')->get();
        $semesters = \App\Models\Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->take(20)->get();

        return view('kurikulum.edit', compact('kurikulum', 'prodis', 'semesters'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\StoreKurikulumRequest $request, $id)
    {
        try {
            $kurikulum = \App\Models\Kurikulum::findOrFail($id);

            if ($kurikulum->sumber_data != 'lokal') {
                return redirect()->route('admin.kurikulum.index')
                    ->with('error', 'Data dari server tidak dapat diubah.');
            }

            $data = $request->validated();
            $data['status_sinkronisasi'] = 'updated_local'; // Mark as updated

            $kurikulum->update($data);

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Data Kurikulum berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $kurikulum = \App\Models\Kurikulum::findOrFail($id);

            if ($kurikulum->sumber_data != 'lokal') {
                return redirect()->route('admin.kurikulum.index')
                    ->with('error', 'Data dari server tidak dapat dihapus.');
            }

            $kurikulum->delete();

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Data Kurikulum berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Syc data from server.
     */
    public function sync()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('sync:kurikulum-from-server');
            return redirect()->back()->with('success', 'Sinkronisasi Kurikulum berhasil!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
