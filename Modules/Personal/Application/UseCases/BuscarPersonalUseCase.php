<?php

declare(strict_types=1);

namespace Modules\Personal\Application\UseCases;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;

class BuscarPersonalUseCase
{
    public function __construct(
        private readonly PersonalRepositoryInterface $repository
    ) {
    }

    public function execute(string $query, int $limit = 5)
    {
        return $this->repository->buscar($query, $limit);
    }
}
