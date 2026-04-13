<?php

declare(strict_types=1);

namespace Modules\Agenda\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Agenda\Application\Contracts\CrearAgendaCompletaUseCaseInterface;
use Modules\Agenda\Application\UseCases\CrearAgendaCompletaUseCase;
use Modules\Agenda\Application\Contracts\ListarAgendasPaginadasUseCaseInterface;
use Modules\Agenda\Application\UseCases\ListarAgendasPaginadasUseCase;
use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;
use Modules\Agenda\Infrastructure\Repositories\OrdenEloquentRepository;
use Modules\Agenda\Domain\Contracts\VisitaRepositoryInterface;
use Modules\Agenda\Infrastructure\Repositories\VisitaMasivaRepository;

class AgendaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. Vinculamos el Repositorio de Órdenes
        $this->app->bind(OrdenRepositoryInterface::class, OrdenEloquentRepository::class);

        // 2. Vinculamos el Repositorio de Visitas
        $this->app->bind(VisitaRepositoryInterface::class, VisitaMasivaRepository::class);

        // 3. Vinculamos los Casos de Uso
        $this->app->bind(CrearAgendaCompletaUseCaseInterface::class, CrearAgendaCompletaUseCase::class);
        $this->app->bind(ListarAgendasPaginadasUseCaseInterface::class, ListarAgendasPaginadasUseCase::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
             ->middleware('api')
             ->group(base_path('Modules/Agenda/Routes/api.php'));
    }
}
