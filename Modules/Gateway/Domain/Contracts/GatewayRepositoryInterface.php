<?php

namespace Modules\Gateway\Domain\Contracts;

interface GatewayRepositoryInterface
{
    /**
     * Consulta el detalle de un ingreso en el sistema externo (Gateway/KubApp)
     *
     * @param int|string $idIngreso
     * @return array
     */
    public function consultarDetalleIngreso($idIngreso): array;

    /**
     * Busca personal (profesional) en el sistema externo por nombre
     *
     * @param string $nombre
     * @return array
     */
    public function buscarUsuarioPorNombre(string $nombre): array;
}
