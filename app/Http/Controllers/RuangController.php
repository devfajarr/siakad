<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ruang;
use Illuminate\Database\QueryException;

class RuangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ruangs = Ruang::orderBy('nama_ruang')->get();
        return view('admin.ruangan.index', compact('ruangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_ruang' => 'required|string|max:255|unique:ruangs,kode_ruang',
            'nama_ruang' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:0',
        ]);

        try {
            Ruang::create($request->all());
            return back()->with('success', 'Data ruangan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan ruangan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ruang = Ruang::findOrFail($id);

        $request->validate([
            'kode_ruang' => 'required|string|max:255|unique:ruangs,kode_ruang,' . $ruang->id,
            'nama_ruang' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:0',
        ]);

        try {
            $ruang->update($request->all());
            return back()->with('success', 'Data ruangan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui ruangan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ruang = Ruang::findOrFail($id);

        try {
            $ruang->delete();
            return back()->with('success', 'Data ruangan berhasil dihapus.');
        } catch (QueryException $e) {
            // Catching Foreign Key Constraint Violations
            return back()->with('error', 'Gagal menghapus! Ruangan ini tidak bisa dihapus karena sedang digunakan pada Jadwal Perkuliahan aktif.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
