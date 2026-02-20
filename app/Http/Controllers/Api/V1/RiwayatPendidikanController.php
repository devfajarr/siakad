<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRiwayatPendidikanRequest;
use App\Http\Requests\Api\V1\UpdateRiwayatPendidikanRequest;
use App\Http\Resources\Api\V1\RiwayatPendidikanResource;
use App\Models\RiwayatPendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatPendidikanController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPendidikan::query()->with('mahasiswa');

        // Filter by Mahasiswa ID
        if ($request->has('id_mahasiswa')) {
            $query->where('id_mahasiswa', $request->id_mahasiswa);
        }

        // Filter by NIM
        if ($request->has('nim')) {
            $query->where('nim', 'like', '%' . $request->nim . '%');
        }

        $data = $query->paginate(10);

        return RiwayatPendidikanResource::collection($data);
    }

    public function store(StoreRiwayatPendidikanRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Auto-fill sync status defaults
            $validated['is_synced'] = false;

            $riwayat = RiwayatPendidikan::create($validated);

            DB::commit();

            return new RiwayatPendidikanResource($riwayat);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $riwayat = RiwayatPendidikan::with('mahasiswa')->findOrFail($id);
        return new RiwayatPendidikanResource($riwayat);
    }

    public function update(UpdateRiwayatPendidikanRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $riwayat = RiwayatPendidikan::findOrFail($id);
            $validated = $request->validated();

            // If critical data changes, reset sync status
            $validated['is_synced'] = false;

            $riwayat->update($validated);

            DB::commit();

            return new RiwayatPendidikanResource($riwayat);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $riwayat = RiwayatPendidikan::findOrFail($id);
            $riwayat->delete();

            DB::commit();

            return response()->json(['message' => 'Data deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
