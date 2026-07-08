<?php

namespace Modules\Gateway\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Modules\Gateway\Infrastructure\Repositories\KubAppApiRepository;

class GatewayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            GatewayRepositoryInterface::class,
            KubAppApiRepository::class
        );
    }
}
