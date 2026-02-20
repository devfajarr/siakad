<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Http\Client\ConnectionException;

class FeederAuthService
{
    /**
     * Mendapatkan token aktif dari cache atau request baru ke Neo Feeder.
     *
     * @return string
     * @throws Exception
     */
    public function getToken(): string
    {
        // Cache durasi 55 menit (3300 detik) sebagai buffer aman sebelum token expired (60 menit)
        return Cache::remember('feeder_token', 3300, function () {
            return $this->requestNewToken();
        });
    }

    /**
     * Melakukan request token baru ke Neo Feeder.
     *
     * @return string
     * @throws Exception
     */
    protected function requestNewToken(): string
    {
        $url = config('services.feeder.url', 'http://localhost:3003/ws/live2.php');
        $username = config('services.feeder.username');
        $password = config('services.feeder.password');

        if (empty($url) || empty($username) || empty($password)) {
            throw new Exception('Konfigurasi Feeder (URL, Username, Password) belum lengkap. Cek file .env dan config/services.php.');
        }

        try {
            $response = Http::timeout(30)->post($url, [
                'act' => 'GetToken',
                'username' => $username,
                'password' => $password,
            ]);
        } catch (ConnectionException $e) {
            throw new Exception("Gagal koneksi ke Neo Feeder: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Terjadi kesalahan saat menghubungi Neo Feeder: " . $e->getMessage());
        }

        if ($response->failed()) {
            throw new Exception("HTTP Error saat request token ke Feeder. Status: " . $response->status());
        }

        $json = $response->json();

        // Validasi format response dasar
        if (!isset($json['error_code'])) {
            throw new Exception("Format response dari Feeder tidak valid (missing error_code). Body: " . $response->body());
        }

        // Cek error code dari Feeder
        if ($json['error_code'] !== 0) {
            $desc = $json['error_desc'] ?? 'Unknown error';
            throw new Exception("Feeder Error: {$desc} (Code: {$json['error_code']})");
        }

        // Ambil token
        $token = $json['data']['token'] ?? null;

        if (empty($token)) {
            throw new Exception("Token tidak ditemukan dalam response sukses Feeder.");
        }

        return $token;
    }
}
