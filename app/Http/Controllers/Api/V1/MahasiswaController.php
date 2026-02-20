<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Http\Requests\Api\V1\StoreMahasiswaRequest;
use App\Http\Requests\Api\V1\UpdateMahasiswaRequest;
use App\Http\Resources\Api\V1\MahasiswaResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Mahasiswa::query();

            // Optional Search
            if ($request->has('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama_mahasiswa', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Pagination with meta
            $mahasiswas = $query->paginate(10); // Default per_page 10

            // Custom Response format
            return response()->json([
                'success' => true,
                'message' => 'List Data Mahasiswa',
                'data' => MahasiswaResource::collection($mahasiswas)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $mahasiswas->currentPage(),
                    'per_page' => $mahasiswas->perPage(),
                    'total' => $mahasiswas->total(),
                    'last_page' => $mahasiswas->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMahasiswaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $mahasiswa = Mahasiswa::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa created successfully',
                'data' => new MahasiswaResource($mahasiswa),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Mahasiswa',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::find($id);

            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail Data Mahasiswa',
                'data' => new MahasiswaResource($mahasiswa),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMahasiswaRequest $request, $id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::find($id);

            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa not found',
                ], 404);
            }

            $validated = $request->validated();
            $mahasiswa->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa updated successfully',
                'data' => new MahasiswaResource($mahasiswa),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Mahasiswa',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::find($id);

            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa not found',
                ], 404);
            }

            $mahasiswa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Mahasiswa',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
