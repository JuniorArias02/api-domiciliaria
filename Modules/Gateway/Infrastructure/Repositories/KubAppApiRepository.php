<?php

namespace Modules\Gateway\Infrastructure\Repositories;

use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class KubAppApiRepository implements GatewayRepositoryInterface
{
    protected $baseUrl;
    protected $clientId;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('KUBAPP_API_URL');
        $this->clientId = env('KUBAPP_CLIENT_ID');
        $this->apiKey = env('KUBAPP_API_KEY');

        if (!$this->baseUrl || !$this->apiKey || !$this->clientId) {
            throw new Exception("Las credenciales del Gateway (KubApp) no están configuradas correctamente en el archivo .env");
        }
    }

    public function consultarDetalleIngreso($idIngreso): array
    {
        // Petición HTTP al Gateway (KubApp) con timeout aumentado
        $response = Http::withHeaders([
            'X-Client-ID' => $this->clientId,
            'x-api-key' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->timeout(90)->get("{$this->baseUrl}/ingresos/{$idIngreso}/detalles");

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

    public function buscarUsuarioPorNombre(string $nombre): array
    {
        $response = Http::withHeaders([
            'X-Client-ID' => $this->clientId,
            'x-api-key' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->timeout(90)->get("{$this->baseUrl}/usuarios/buscar", [
            'nombre' => $nombre
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() === 404) {
            throw new Exception("Usuario no encontrado en el sistema externo.");
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new Exception("Error de autenticación con el Gateway externo.");
        }

        throw new Exception("Error al buscar el usuario en el Gateway externo: " . $response->body());
    }
}
