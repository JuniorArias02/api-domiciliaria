<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema de Atención Domiciliaria
|--------------------------------------------------------------------------
|
| Aquí cargamos dinámicamente las rutas de todos los módulos.
| Estas rutas ya vienen prefijadas con /api por bootstrap/app.php
|
*/

foreach (glob(base_path('Modules/*/Routes/api.php')) as $routeFile) {
    require $routeFile;
}
