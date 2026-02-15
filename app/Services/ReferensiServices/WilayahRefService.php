<?php

namespace App\Services\ReferensiServices;

use App\Services\NeoFeederService;

class WilayahRefService extends NeoFeederService
{
    /**
     * Mengambil data Wilayah.
     * 
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getWilayah(string $filter = '', int $limit = 500, int $offset = 0): array
    {
        return $this->sendRequest('GetWilayah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil data Negara.
     * 
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getNegara(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetNegara', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil data Level Wilayah.
     * 
     * @return array
     * @throws \Exception
     */
    public function getLevelWilayah(): array
    {
        return $this->sendRequest('GetLevelWilayah');
    }
}
