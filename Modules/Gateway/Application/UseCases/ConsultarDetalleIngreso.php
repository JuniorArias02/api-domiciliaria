<?php

namespace Modules\Gateway\Application\UseCases;

use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Modules\Gateway\Application\Services\FiltroServiciosService;
use Exception;

class ConsultarDetalleIngreso
{
    private $repo;
    private $filtroService;

    public function __construct(GatewayRepositoryInterface $repo, FiltroServiciosService $filtroService)
    {
        $this->repo = $repo;
        $this->filtroService = $filtroService;
    }

    public function execute($idIngreso)
    {
        if (empty($idIngreso) || !is_numeric($idIngreso)) {
            throw new Exception("El ID del ingreso proporcionado no es válido.");
        }

        $detalles = $this->repo->consultarDetalleIngreso($idIngreso);

        // Filtrar el JSON externo aplicando todas las reglas de negocio
        $detallesFiltrados = $this->filtroService->filtrarPorServiciosLocales($detalles);

        if (empty($detallesFiltrados)) {
            throw new Exception("No hay datos válidos para registrar o el ingreso ya existe en el sistema.");
        }

        return $detallesFiltrados;
    }
}
