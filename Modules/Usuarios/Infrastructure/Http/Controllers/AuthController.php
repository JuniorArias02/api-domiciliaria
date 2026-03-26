<?php

namespace Modules\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\UseCases\LoginUseCase;
use Modules\Auth\Application\UseCases\LogoutUseCase;
use Modules\Auth\Application\UseCases\MeUseCase;
use Modules\Auth\Application\UseCases\RefreshTokenUseCase;
use Modules\Auth\Infrastructure\Http\Requests\LoginRequest;

/**
 * AuthController — Capa HTTP del módulo Auth.
 *
 * ✅ Solo orquesta: recibe request → llama UseCase → devuelve JSON.
 * ❌ Sin lógica de negocio.
 * ❌ Sin consultas a la BD.
 * ❌ Sin validaciones de negocio.
 */
class AuthController extends Controller
{
    // -----------------------------------------------------------------------
    // POST /api/v1/auth/login
    // -----------------------------------------------------------------------

}
