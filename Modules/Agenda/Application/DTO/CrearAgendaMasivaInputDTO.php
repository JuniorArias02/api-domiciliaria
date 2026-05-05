<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\DTO;

class CrearAgendaMasivaInputDTO
{
    /**
     * @param int $id_ingreso
     * @param string|null $observaciones
     * @param ServicioAgendaDTO[] $servicios
     */
    public function __construct(
        public readonly int $id_ingreso,
        public readonly ?string $observaciones,
        public readonly array $servicios
    ) {
    }

    public static function fromRequest(array $data): self
    {
        $servicios = [];
        if (isset($data['servicios']) && is_array($data['servicios'])) {
            foreach ($data['servicios'] as $servicio) {
                $servicios[] = ServicioAgendaDTO::fromArray($servicio);
            }
        }

        return new self(
            id_ingreso: (int) $data['id_ingreso'],
            observaciones: $data['observaciones'] ?? null,
            servicios: $servicios
        );
    }
}
