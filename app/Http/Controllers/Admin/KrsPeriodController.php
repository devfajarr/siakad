<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KrsPeriod;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KrsPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periods = KrsPeriod::with('semester')->orderByDesc('tgl_mulai')->get();
        $semesters = Semester::orderByDesc('id_semester')->get();

        return view('admin.krs-period.index', compact('periods', 'semesters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_semester' => 'required|exists:semesters,id_semester',
            'nama_periode' => 'required|string|max:100',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        $period = KrsPeriod::create($validated + [
            'created_by' => Auth::id(),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        Log::info("CRUD_CREATE: KrsPeriod berhasil dibuat", ['id' => $period->id, 'data' => $validated]);

        return redirect()->route('admin.krs-period.index')
            ->with('success', 'Periode KRS berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KrsPeriod $krsPeriod)
    {
        $validated = $request->validate([
            'id_semester' => 'required|exists:semesters,id_semester',
            'nama_periode' => 'required|string|max:100',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        $krsPeriod->update($validated + [
            'updated_by' => Auth::id(),
            'is_active' => $request->boolean('is_active'),
        ]);

        Log::info("CRUD_UPDATE: KrsPeriod diubah", ['id' => $krsPeriod->id, 'changes' => $validated]);

        return redirect()->route('admin.krs-period.index')
            ->with('success', 'Periode KRS berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KrsPeriod $krsPeriod)
    {
        Log::warning("CRUD_DELETE: KrsPeriod dihapus", ['id' => $krsPeriod->id]);
        $krsPeriod->delete();

        return redirect()->route('admin.krs-period.index')
            ->with('success', 'Periode KRS berhasil dihapus.');
    }
}
