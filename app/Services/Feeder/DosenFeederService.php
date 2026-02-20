<?php

namespace App\Services\Feeder;

use App\Services\NeoFeederService;

class DosenFeederService extends NeoFeederService
{
    public function getListDosen(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetListDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getDetailBiodataDosen(string $idDosen): array
    {
        // Menggunakan format filter seperti API lain untuk konsistensi
        // Format: filter="id_dosen='{uuid}'" seperti GetListPenugasanDosen, dll
        return $this->sendRequest('DetailBiodataDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getListPenugasanDosen(string $idDosen): array
    {
        return $this->sendRequest('GetListPenugasanDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getListPenugasanSemuaDosen(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetListPenugasanSemuaDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getRiwayatFungsionalDosen(string $idDosen): array
    {
        return $this->sendRequest('GetRiwayatFungsionalDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getRiwayatPangkatDosen(string $idDosen): array
    {
        return $this->sendRequest('GetRiwayatPangkatDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getRiwayatPendidikanDosen(string $idDosen): array
    {
        return $this->sendRequest('GetRiwayatPendidikanDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getRiwayatSertifikasiDosen(string $idDosen): array
    {
        return $this->sendRequest('GetRiwayatSertifikasiDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getRiwayatPenelitianDosen(string $idDosen): array
    {
        return $this->sendRequest('GetRiwayatPenelitianDosen', [
            'filter' => "id_dosen='{$idDosen}'",
        ]);
    }

    public function getDictionary(string $table): array
    {
        return $this->sendRequest('GetDictionary', [
            'table' => $table,
        ]);
    }
}
