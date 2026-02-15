<?php

namespace App\Services\ReferensiServices;

use App\Services\NeoFeederService;

class AdministratifRefService extends NeoFeederService
{
    /**
     * Mengambil data Jenis Tinggal.
     * 
     * @return array
     * @throws \Exception
     */
    public function getJenisTinggal(): array
    {
        return $this->sendRequest('GetJenisTinggal');
    }

    /**
     * Mengambil data Alat Transportasi.
     * 
     * @return array
     * @throws \Exception
     */
    public function getAlatTransportasi(): array
    {
        return $this->sendRequest('GetAlatTransportasi');
    }
}
