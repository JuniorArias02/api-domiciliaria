<?php

namespace Modules\Mapas\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Modules\Mapas\Infrastructure\Repositories\MapaRepository;

class MapaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(MapaRepositoryInterface::class, MapaRepository::class);
    }

    public function boot()
    {
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
             ->middleware('api')
             ->group(base_path('Modules/Mapas/Routes/api.php'));
    }
}
