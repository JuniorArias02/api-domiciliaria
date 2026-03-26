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
use OpenApi\Attributes as OA;

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

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Iniciar sesión y obtener el token JWT',
        description: 'Recibe email y password, y si el usuario está activo, retorna un token persistente de la sesión.',
        tags: ['Autenticación']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@domiciliaria.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Token JWT generado correctamente')]
    #[OA\Response(response: 401, description: 'Credenciales inválidas')]
    #[OA\Response(response: 403, description: 'Usuario inactivo')]
    public function login(LoginRequest $request, LoginUseCase $useCase): JsonResponse
    {
        $result  = $useCase->execute(
            email:    $request->email,
            password: $request->password,
            meta: [
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'dispositivo' => $request->header('X-Device-Type'),
            ]
        );

        $u = $result['usuario'];

        return response()->json([
            'success'    => true,
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'usuario'    => [
                'id_usuario'      => $u->idUsuario,
                'nombre_completo' => $u->nombreCompleto,
                'email'           => $u->email,
                'estado'          => $u->estado,
                'rol'             => [
                    'id_rol' => $u->idRol,
                    'nombre' => $u->rolNombre,
                ],
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/auth/logout  [auth:api]
    // -----------------------------------------------------------------------

    public function logout(Request $request, LogoutUseCase $useCase): JsonResponse
    {
        $useCase->execute(
            idUsuario: auth()->user()->id_usuario,
            meta: [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/auth/me  [auth:api]
    // -----------------------------------------------------------------------

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: 'Obtener perfil del usuario',
        description: 'Consulta quién es el usuario autenticado usando el token alojado en los headers.',
        security: [['bearerAuth' => []]],
        tags: ['Autenticación']
    )]
    #[OA\Response(response: 200, description: 'Datos del usuario extraídos satisfactoriamente')]
    public function me(MeUseCase $useCase): JsonResponse
    {
        $u = $useCase->execute(auth()->user()->id_usuario);

        return response()->json([
            'success' => true,
            'data'    => [
                'id_usuario'      => $u->idUsuario,
                'nombre_completo' => $u->nombreCompleto,
                'email'           => $u->email,
                'estado'          => $u->estado,
                'rol'             => [
                    'id_rol' => $u->idRol,
                    'nombre' => $u->rolNombre,
                ],
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/auth/refresh  [auth:api]
    // -----------------------------------------------------------------------

    public function refresh(RefreshTokenUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute();

        return response()->json([
            'success'    => true,
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }
}
