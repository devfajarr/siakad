<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Exception;

class NeoFeederService
{
    protected FeederAuthService $feederAuthService;
    protected string $baseUrl;

    public function __construct(FeederAuthService $feederAuthService)
    {
        $this->feederAuthService = $feederAuthService;
        $this->baseUrl = config('services.feeder.url', env('FEEDER_URL'));
    }

    /**
     * Send request to Neo Feeder API.
     *
     * @param string $act
     * @param array $params
     * @return array
     * @throws Exception
     */
    protected function sendRequest(string $act, array $params = [])
    {
        try {
            // Payload dasar
            $payload = ['act' => $act];

            // Inject Token jika bukan request GetToken
            if ($act !== 'GetToken') {
                $token = $this->feederAuthService->getToken();
                $payload['token'] = $token;
            }

            // Merge dengan params tambahan
            $payload = array_merge($payload, $params);

            $response = Http::timeout(30)->post($this->baseUrl, $payload);

        } catch (ConnectionException $e) {
            throw new Exception("Gagal koneksi ke Neo Feeder: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Terjadi kesalahan saat menghubungi Neo Feeder: " . $e->getMessage());
        }

        if ($response->failed()) {
            throw new Exception("HTTP Error saat request ke Feeder ({$act}). Status: " . $response->status());
        }

        $json = $response->json();

        // Validasi response
        if (!isset($json['error_code'])) {
            throw new Exception("Format response Feeder tidak valid. Body: " . $response->body());
        }

        if ($json['error_code'] !== 0) {
            $desc = $json['error_desc'] ?? 'Unknown error';
            throw new Exception("Feeder Error ({$act}): {$desc} (Code: {$json['error_code']})");
        }

        return $json['data'] ?? [];
    }

    /**
     * Public wrapper for sendRequest.
     */
    public function execute(string $act, array $params = [])
    {
        return $this->sendRequest($act, $params);
    }
}
