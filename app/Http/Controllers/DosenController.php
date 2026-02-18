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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
