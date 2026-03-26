<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Documentación oficial de la API para el sistema de atención domiciliaria',
    title: 'API Domiciliaria Médica',
    contact: new OA\Contact(email: 'admin@domiciliaria.com')
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer',
    description: 'Introduce tu token JWT con el formato **Bearer &lt;token&gt;**'
)]
abstract class Controller
{
    //
}
