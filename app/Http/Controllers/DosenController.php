<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Agama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dosen = Dosen::orderBy('nama')->get();
        $agamaList = Agama::all();

        return view('dosen.index', compact('dosen', 'agamaList'));
    }

    /**
     * Sync Dosen dari API Pusat (Feeder).
     */
    public function sync()
    {
        try {
            Artisan::call('sync:dosen-from-pusat');
            return redirect()->route('admin.dosen.index')
                ->with('success', 'Sinkronisasi dosen berhasil dijalankan.');
        } catch (\Exception $e) {
            return redirect()->route('admin.dosen.index')
                ->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreDosenRequest $request)
    {
        Dosen::create($request->validated() + [
            'status_sinkronisasi' => 'lokal',
            'external_id' => null,
            'is_struktural' => false,
            'is_pengajar' => true,
        ]);

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen lokal berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not used, view is handled via modal
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used, edit is handled via modal
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateDosenRequest $request, Dosen $dosen)
    {
        // Guard is already handled in FormRequest authorize()

        $dosen->update($request->validated());

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dosen $dosen)
    {
        if ($dosen->status_sinkronisasi !== 'lokal') {
            abort(403, 'Data dosen dari sistem pusat tidak dapat dihapus.');
        }

        $dosen->delete();

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }
}
