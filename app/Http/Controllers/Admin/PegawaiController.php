<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pegawais = Pegawai::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.pegawai.index', compact('pegawais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais,nip',
            'nama_lengkap' => 'required|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'required|string|unique:pegawais,no_hp',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $pegawai = Pegawai::create([
                'nip' => $validated['nip'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'unit_kerja' => $validated['unit_kerja'],
                'jabatan' => $validated['jabatan'],
                'no_hp' => $validated['no_hp'],
                'email' => $validated['email'],
                'is_active' => true,
            ]);

            Log::info("CRUD_CREATE: Data Pegawai berhasil dibuat", ['id' => $pegawai->id, 'data' => $validated]);
            return redirect()->route('admin.pegawai.index')->with('success', 'Data Pegawai berhasil ditambahkan. Akun login telah otomatis dibuat.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menyimpan data Pegawai", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais,nip,' . $pegawai->id,
            'nama_lengkap' => 'required|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'required|string|unique:pegawais,no_hp,' . $pegawai->id,
            'email' => 'nullable|email|max:255',
            'is_active' => 'required|boolean'
        ]);

        try {
            $pegawai->update([
                'nip' => $validated['nip'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'unit_kerja' => $validated['unit_kerja'],
                'jabatan' => $validated['jabatan'],
                'no_hp' => $validated['no_hp'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ]);

            Log::info("CRUD_UPDATE: Data Pegawai diubah", ['id' => $pegawai->id, 'changes' => $pegawai->getChanges()]);
            return redirect()->route('admin.pegawai.index')->with('success', 'Data Pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal memperbarui data Pegawai", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan sistem saat memperbarui data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $pegawai = Pegawai::findOrFail($id);
            $pegawai->delete();

            Log::warning("CRUD_DELETE: Data Pegawai dihapus", ['id' => $id]);
            return response()->json(['success' => true, 'message' => 'Data pegawai berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus data Pegawai", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data.']);
        }
    }
}
