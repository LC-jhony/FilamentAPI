<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class ApiService
{
    protected string $token;
    protected string $sunatBaseUrl;
    protected string $reniecBaseUrl;

    public function __construct()
    {
        $this->token = config('services.sunat.token') ?:
            '5GQEkpGu69GJdUOn9eRvqqZbci68LAvQHBzLApNNLcsd9YofjWu6zI1A5zuW';
        $base = config('services.sunat.base_url') ?:
            'https://api.codart.cgrt.net/api/v1/consultas/sunat';
        $this->sunatBaseUrl = rtrim($base, '/');
        $this->reniecBaseUrl = preg_replace('#/sunat$#', '/reniec', $this->sunatBaseUrl);
    }

    /**
     * Consulta información de un RUC en SUNAT
     */
    public function consultarRUC(string $ruc): array
    {
        $this->validateRUC($ruc);

        try {
            $response = $this->makeRequest($this->sunatBaseUrl . "/ruc/{$ruc}");

            if (!$response->successful()) {
                return $this->errorResponse('Error al consultar SUNAT', $response->status());
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                return $this->errorResponse('RUC no encontrado');
            }

            return $this->successResponse($data['result'] ?? []);
        } catch (\Exception $e) {
            Log::error('SunatService::consultarRUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error de conexión con SUNAT');
        }
    }

    /**
     * Consulta información de un DNI en RENIEC
     */
    public function consultarDNI(string $dni): array
    {
        $this->validateDNI($dni);

        try {
            $response = $this->makeRequest($this->reniecBaseUrl . "/dni/{$dni}");

            if (!$response->successful()) {
                return $this->errorResponse('Error al consultar RENIEC', $response->status());
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                return $this->errorResponse('DNI no encontrado');
            }

            return $this->successResponse($data['result'] ?? $data);
        } catch (\Exception $e) {
            Log::error('SunatService::consultarDNI', [
                'dni' => $dni,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error de conexión con RENIEC');
        }
    }

    protected function makeRequest(string $url): Response
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->timeout(10)->get($url);
    }

    protected function validateRUC(string $ruc): void
    {
        if (strlen($ruc) !== 11 || !ctype_digit($ruc)) {
            throw new \InvalidArgumentException('El RUC debe tener exactamente 11 dígitos');
        }
    }

    protected function validateDNI(string $dni): void
    {
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            throw new \InvalidArgumentException('El DNI debe tener exactamente 8 dígitos');
        }
    }

    protected function successResponse(array $data): array
    {
        return [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];
    }

    protected function errorResponse(string $message, int $code = 0): array
    {
        return [
            'success' => false,
            'data' => [],
            'error' => $message,
            'code' => $code,
        ];
    }
}
