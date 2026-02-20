<?php

namespace App\Services\ReferensiServices;

use App\Services\NeoFeederService;

class PribadiRefService extends NeoFeederService
{
    /**
     * Mengambil data Agama.
     * 
     * @return array
     * @throws \Exception
     */
    public function getAgama(): array
    {
        return $this->sendRequest('GetAgama');
    }

    /**
     * Mengambil data Kebutuhan Khusus.
     * 
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getKebutuhanKhusus(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetKebutuhanKhusus', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil data Pekerjaan.
     * 
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getPekerjaan(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetPekerjaan', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil data Penghasilan.
     * 
     * @return array
     * @throws \Exception
     */
    public function getPenghasilan(): array
    {
        return $this->sendRequest('GetPenghasilan');
    }

    /**
     * Mengambil data Pembiayaan.
     * 
     * @return array
     * @throws \Exception
     */
    public function getPembiayaan(): array
    {
        return $this->sendRequest('GetPembiayaan');
    }
}
