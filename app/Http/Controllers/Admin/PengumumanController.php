<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::with('creator')->latest()->paginate(15);
        return view('admin.pengumuman.index', compact('pengumumans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|in:krs,kuisioner,ujian,jadwal,umum',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $pengumuman = Pengumuman::create($validated);

        Log::info("CRUD_CREATE: Pengumuman berhasil dibuat", [
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|in:krs,kuisioner,ujian,jadwal,umum',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $pengumuman->update($validated);

        Log::info("CRUD_UPDATE: Pengumuman diubah", [
            'id' => $pengumuman->id,
            'changes' => $pengumuman->getChanges(),
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        Log::warning("CRUD_DELETE: Pengumuman dihapus", [
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
        ]);

        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
