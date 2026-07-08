<?php

namespace Modules\Gateway\Infrastructure\Repositories;

use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class KubAppApiRepository implements GatewayRepositoryInterface
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('KUBAPP_API_URL');
        $this->apiKey = env('KUBAPP_API_KEY');

        if (!$this->baseUrl || !$this->apiKey) {
            throw new Exception("Las credenciales del Gateway (KubApp) no están configuradas correctamente en el archivo .env");
        }
    }

    public function consultarDetalleIngreso($idIngreso): array
    {
        // Petición HTTP al Gateway (KubApp)
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->get("{$this->baseUrl}/ingresos/{$idIngreso}/detalles");

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() === 404) {
            throw new Exception("Ingreso no encontrado en el sistema externo.");
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new Exception("Error de autenticación con el Gateway externo.");
        }

        throw new Exception("Error al consultar el Gateway externo: " . $response->body());
    }
}
