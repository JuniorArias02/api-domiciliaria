<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\DTO;

class PaginacionAgendaInputDTO
{
    public function __construct(
        public readonly int $per_page = 15,
        public readonly int $page = 1,
        public readonly ?string $buscar = null,
        public readonly ?string $estado = null
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 15,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            buscar: $data['buscar'] ?? null,
            estado: $data['estado'] ?? null
        );
    }
}
