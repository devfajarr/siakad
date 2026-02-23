<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Menampilkan daftar seluruh Master Semester.
     */
    public function index()
    {
        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $activeSemester = getActiveSemester();

        return view('admin.semester.index', compact('semesters', 'activeSemester'));
    }

    /**
     * Memperbarui Status Semester Menjadi Aktif Global Secara Eksklusif
     */
    public function setActive(Request $request, $id)
    {
        try {
            // Panggil Helper Statis dari Model Semester
            Semester::setActivePeriod($id);

            $semesterName = Semester::find($id)->nama_semester ?? 'Terpilih';

            return back()->with('success', "Periode {$semesterName} berhasil ditetapkan sebagai referensi aktif global.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengaktifkan semester: ' . $e->getMessage());
        }
    }
}
