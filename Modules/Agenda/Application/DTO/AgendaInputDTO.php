<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\DTO;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\Agenda\Domain\Exceptions\FrecuenciaInvalidaException;

class AgendaInputDTO
{
    public function __construct(
        public readonly int $id_paciente,
        public readonly int $id_servicio,
        public readonly int $numero_sesiones,
        public readonly int $frecuencia_dias,
        public readonly Carbon $fecha_inicio,
        public readonly ?int $id_orden = null,
        public readonly ?int $id_personal = null
    ) {
        if ($this->numero_sesiones <= 0) {
            throw new InvalidArgumentException("El número de sesiones debe ser mayor a 0.");
        }
        
        if ($this->frecuencia_dias < 0) {
            throw FrecuenciaInvalidaException::menorQueCero();
        }
    }
}
