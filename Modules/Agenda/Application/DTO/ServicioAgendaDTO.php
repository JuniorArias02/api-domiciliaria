<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\DTO;

use Carbon\Carbon;

class ServicioAgendaDTO
{
    public function __construct(
        public readonly int $id_servicio,
        public readonly ?int $id_personal,
        public readonly Carbon $fecha_inicio,
        public readonly int $sesiones
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id_servicio: (int) $data['id_servicio'],
            id_personal: isset($data['id_personal']) ? (int) $data['id_personal'] : null,
            fecha_inicio: Carbon::parse($data['fecha_inicio']),
            sesiones: (int) $data['sesiones']
        );
    }
}
