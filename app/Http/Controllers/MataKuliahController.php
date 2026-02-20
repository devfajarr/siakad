<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MataKuliah;

class MataKuliahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mataKuliah = MataKuliah::with('prodi')->orderBy('kode_mk')->get();
        return view('mata-kuliah.index', compact('mataKuliah'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodi = \App\Models\ProgramStudi::all();
        return view('mata-kuliah.create', compact('prodi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreMataKuliahRequest $request)
    {
        $validated = $request->validated();

        // Add default values for local data
        $validated['sumber_data'] = 'lokal';
        $validated['status_sinkronisasi'] = MataKuliah::STATUS_CREATED_LOCAL;
        $validated['status_aktif'] = true;

        MataKuliah::create($validated);

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan secara lokal.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mataKuliah = MataKuliah::with('prodi')->findOrFail($id);
        return view('mata-kuliah.show', compact('mataKuliah'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Protect server data
        if ($mataKuliah->sumber_data != 'lokal') {
            return redirect()->route('admin.mata-kuliah.index')
                ->with('error', 'Data dari server tidak dapat diedit secara lokal.');
        }

        $prodi = \App\Models\ProgramStudi::all();
        return view('mata-kuliah.edit', compact('mataKuliah', 'prodi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateMataKuliahRequest $request, string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Protect server data
        if ($mataKuliah->sumber_data != 'lokal') {
            return redirect()->route('admin.mata-kuliah.index')
                ->with('error', 'Data dari server tidak dapat diupdate secara lokal.');
        }

        $validated = $request->validated();

        // Update status for push possibility
        $validated['status_sinkronisasi'] = MataKuliah::STATUS_UPDATED_LOCAL;

        $mataKuliah->update($validated);

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Protect server data
        if ($mataKuliah->sumber_data != 'lokal') {
            return redirect()->route('admin.mata-kuliah.index')
                ->with('error', 'Data dari server tidak dapat dihapus secara lokal.');
        }

        $mataKuliah->delete();

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}
