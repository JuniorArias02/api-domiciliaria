<?php

declare(strict_types=1);

namespace Modules\Agenda\Domain\Exceptions;

use Exception;

class FrecuenciaInvalidaException extends Exception
{
    public static function menorQueCero(): self
    {
        return new self("La frecuencia en días de las sesiones no puede ser negativa.");
    }
}
