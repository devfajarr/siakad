<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negara;
use App\Models\ReferenceWilayah;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /**
     * Get Kabupaten by Provinsi ID.
     */
    public function getKabupaten($provinsiId)
    {
        $kabupatens = ReferenceWilayah::getKabupatenByProvinsi($provinsiId);

        // Transform for Select2 if needed, or return as is
        // Return structured data for easier consumption
        return response()->json($kabupatens->map(function ($item) {
            return [
                'id' => trim($item->id_wilayah),
                'text' => $item->nama_wilayah
            ];
        }));
    }

    /**
     * Get Kecamatan by Kabupaten ID.
     */
    public function getKecamatan($kabupatenId)
    {
        $kecamatans = ReferenceWilayah::getKecamatanByKabupaten($kabupatenId);

        return response()->json($kecamatans->map(function ($item) {
            return [
                'id' => trim($item->id_wilayah),
                'text' => $item->nama_wilayah
            ];
        }));
    }

    /**
     * Search Negara for Select2.
     */
    public function searchNegara(Request $request)
    {
        $search = $request->term; // Select2 sends 'term'

        $query = Negara::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_negara', 'like', "%{$search}%")
                    ->orWhere('id_negara', 'like', "%{$search}%");
            });
        } else {
            // Default popular countries if no search
            $query->whereIn('id_negara', ['ID', 'MY', 'SG', 'SA', 'US']);
        }

        $negaras = $query->orderBy('nama_negara')->limit(20)->get();

        return response()->json($negaras->map(function ($item) {
            return [
                'id' => $item->id_negara,
                'text' => $item->nama_negara . ' (' . $item->id_negara . ')'
            ];
        }));
    }
}
