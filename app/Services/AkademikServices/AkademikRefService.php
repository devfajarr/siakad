<?php

namespace App\Services\AkademikServices;

use App\Services\NeoFeederService;

class AkademikRefService extends NeoFeederService
{
    /**
     * Mengambil data Tahun Ajaran.
     *
     * @return array
     * @throws \Exception
     */
    public function getTahunAjaran(): array
    {
        return $this->sendRequest('GetTahunAjaran');
    }

    /**
     * Mengambil data Semester.
     *
     * @return array
     * @throws \Exception
     */
    public function getSemester(): array
    {
        return $this->sendRequest('GetSemester');
    }

    /**
     * Mengambil data Program Studi.
     *
     * @return array
     * @throws \Exception
     */
    public function getProdi(): array
    {
        return $this->sendRequest('GetProdi');
    }

    /**
     * Mengambil daftar Kurikulum.
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getKurikulum(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        // Parameter di-pass langsung ke sendRequest
        // Token akan di-inject otomatis oleh NeoFeederService
        return $this->sendRequest('GetKurikulum', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil daftar Mata Kuliah (Master).
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getMataKuliah(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetMataKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil mata kuliah dalam kurikulum tertentu.
     *
     * @param string $idKurikulum
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getMatkulKurikulum(string $idKurikulum, int $limit = 0, int $offset = 0): array
    {
        // Filter wajib id_kurikulum
        $filter = "id_kurikulum='{$idKurikulum}'";

        return $this->sendRequest('GetMatkulKurikulum', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
