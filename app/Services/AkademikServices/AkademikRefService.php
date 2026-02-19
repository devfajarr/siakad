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
    /**
     * Mengambil daftar Jenis Pendaftaran.
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getJenisPendaftaran(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetJenisPendaftaran', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Mengambil daftar Jalur Masuk.
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getJalurMasuk(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetJalurMasuk', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
    /**
     * Mengambil daftar Jenjang Pendidikan.
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getJenjangPendidikan(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetJenjangPendidikan', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountKurikulum(string $filter = ''): int
    {
        $response = $this->sendRequest('GetCountKurikulum', [
            'filter' => $filter,
        ]);

        return (int) ($response['total'] ?? $response);
    }

    /**
     * Mengambil jumlah data Mata Kuliah.
     *
     * @param string $filter
     * @return int
     * @throws \Exception
     */
    public function getCountMataKuliah(string $filter = ''): int
    {
        $response = $this->sendRequest('GetCountMataKuliah', [
            'filter' => $filter,
        ]);

        return (int) $response;
    }

    /**
     * Mengambil detail Mata Kuliah berdasarkan ID Matkul.
     *
     * @param string $idMatkul
     * @return array
     * @throws \Exception
     */
    public function getDetailMataKuliah(string $idMatkul = '', string $filter = '', int $limit = 0, int $offset = 0): array
    {
        // Jika ada ID Matkul, tambahkan ke filter
        if (!empty($idMatkul)) {
            $idFilter = "id_matkul='{$idMatkul}'";
            $filter = empty($filter) ? $idFilter : "$filter AND $idFilter";
        }

        $params = [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ];

        return $this->sendRequest('GetDetailMataKuliah', $params);
    }

    /**
     * Mengambil dictionary data.
     *
     * @param string $fungsi
     * @return array
     * @throws \Exception
     */
    public function getDictionary(string $fungsi): array
    {
        return $this->sendRequest('GetDictionary', [
            'fungsi' => $fungsi,
        ]);
    }

    // ─── Kelas Kuliah ───────────────────────────────────────

    /**
     * Mengambil daftar Kelas Kuliah.
     *
     * @param string $filter
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getListKelasKuliah(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetListKelasKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
            'order' => '',
        ]);
    }

    /**
     * Mengambil detail Kelas Kuliah berdasarkan id_kelas_kuliah.
     *
     * @param string $idKelasKuliah
     * @return array
     * @throws \Exception
     */
    public function getDetailKelasKuliah(string $idKelasKuliah): array
    {
        return $this->sendRequest('GetDetailKelasKuliah', [
            'filter' => "id_kelas_kuliah='{$idKelasKuliah}'",
        ]);
    }

    /**
     * Mengambil jumlah data Kelas Kuliah.
     *
     * @param string $filter
     * @return int
     * @throws \Exception
     */
    public function getCountKelasKuliah(string $filter = ''): int
    {
        $response = $this->sendRequest('GetCountKelasKuliah', [
            'filter' => $filter,
        ]);

        return (int) ($response['total'] ?? $response);
    }

    // ─── Peserta Kelas Kuliah ──────────────────────────────

    /**
     * Mengambil daftar peserta kelas kuliah.
     *
     * @param string $filter Filter, biasanya "id_kelas_kuliah='...'"
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getPesertaKelasKuliah(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetPesertaKelasKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
            'order' => '',
        ]);
    }

    // ─── Dosen Pengajar Kelas Kuliah ──────────────────────

    /**
     * Mengambil daftar dosen pengajar kelas kuliah.
     *
     * @param string $filter Filter, biasanya "id_kelas_kuliah='...'"
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getDosenPengajarKelasKuliah(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetDosenPengajarKelasKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
            'order' => '',
        ]);
    }

    // ─── Nilai Perkuliahan ────────────────────────────────

    /**
     * Mengambil daftar nilai perkuliahan per kelas.
     *
     * @param string $filter Filter, biasanya "id_kelas_kuliah='...'"
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getListNilaiPerkuliahanKelas(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        return $this->sendRequest('GetListNilaiPerkuliahanKelas', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
            'order' => '',
        ]);
    }

    /**
     * Mengambil detail nilai perkuliahan per kelas.
     *
     * @param string $filter
     * @return array
     * @throws \Exception
     */
    public function getDetailNilaiPerkuliahanKelas(string $filter = ''): array
    {
        return $this->sendRequest('GetDetailNilaiPerkuliahanKelas', [
            'filter' => $filter,
        ]);
    }
}

