<?php

declare(strict_types=1);

namespace Modules\Ingresos\Application\UseCases;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;

class DetectarAutorizacionEnUso
{
    private $repo;

    public function __construct(IngresoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $autorizacion): array
    {
        $autorizacionLimpia = trim($autorizacion);

        if (empty($autorizacionLimpia)) {
            return [
                'en_uso' => false,
                'descripcion' => 'La autorización no es válida o está vacía'
            ];
        }

        $enUso = $this->repo->existeAutorizacion($autorizacionLimpia);

        return [
            'en_uso' => $enUso,
            'descripcion' => $enUso ? 'La autorización ya está en uso' : 'La autorización está disponible'
        ];
    }
}
