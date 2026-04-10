<?php

namespace Modules\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Dashboard\Domain\Contracts\DashboardRepositoryInterface;
use Modules\Dashboard\Infrastructure\Repositories\DashboardRepository;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }
}
