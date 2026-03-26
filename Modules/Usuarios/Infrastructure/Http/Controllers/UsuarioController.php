<?php

namespace Modules\Usuarios\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Usuarios\Application\UseCases\CrearUsuario;
use Modules\Usuarios\Application\UseCases\DesactivarUsuario;
use Modules\Usuarios\Application\UseCases\ActualizarContrasena;
use Modules\Usuarios\Application\UseCases\ActualizarPerfil;
use Illuminate\Routing\Controller;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Usuarios',
    description: 'Endpoints para la gestión, creación y actualización de usuarios'
)]
class UsuarioController extends Controller
{
    #[OA\Post(
        path: '/api/v1/usuarios',
        summary: 'Crear un nuevo usuario',
        description: 'Crea un usuario en el sistema. Requiere token de autenticación (JWT).',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre_completo', 'email', 'password_hash', 'id_rol'],
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Dr. House'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'dr.house@hospital.com'),
                    new OA\Property(property: 'password_hash', type: 'string', format: 'password', example: 'secreta123'),
                    new OA\Property(property: 'id_rol', type: 'integer', example: 2),
                    new OA\Property(property: 'estado', description: 'Opcional. 1 por defecto', type: 'integer', example: 1),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Usuario creado exitosamente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Usuario creado exitosamente'),
                    new OA\Property(property: 'data', type: 'object'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Error de validación o parámetros faltantes'
    )]
    public function store(Request $request, CrearUsuario $useCase)
    {
        try {
            $usuario = $useCase->execute($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data'    => $usuario
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[OA\Put(
        path: '/api/v1/usuarios/{id}/desactivar',
        summary: 'Desactivar un usuario',
        description: 'Cambia el estado de un usuario a inactivo recibiendo su ID en la URL. Requiere JWT.',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID del usuario a desactivar',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Usuario desactivado exitosamente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Usuario desactivado exitosamente'),
                    new OA\Property(property: 'data', type: 'object'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'El usuario no existe o surgió un error'
    )]
    public function destroy($idUsuario, DesactivarUsuario $useCase)
    {
        try {
            $usuario = $useCase->execute((int) $idUsuario);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desactivado exitosamente',
                'data'    => $usuario
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[OA\Put(
        path: '/api/v1/usuarios/contrasena',
        summary: 'Actualizar contraseña',
        description: 'Permite al usuario autenticado cambiar su contraseña sabiendo la actual. Requiere JWT.',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['contrasena_actual', 'nueva_contrasena'],
                properties: [
                    new OA\Property(property: 'contrasena_actual', type: 'string', format: 'password', example: 'secreta123'),
                    new OA\Property(property: 'nueva_contrasena', type: 'string', format: 'password', example: 'claveSuperSegura456'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Contraseña actualizada exitosamente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Contraseña actualizada exitosamente'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Contraseña actual incorrecta o falta de datos'
    )]
    public function updatePassword(Request $request, ActualizarContrasena $useCase)
    {
        try {
            // El ID del usuario está en el token auth()->id() que usa el UseCase
            $contrasenaActual = $request->input('contrasena_actual', '');
            $nuevaContrasena = $request->input('nueva_contrasena', '');

            $usuario = $useCase->execute($contrasenaActual, $nuevaContrasena);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[OA\Put(
        path: '/api/v1/usuarios/perfil',
        summary: 'Actualizar perfil del usuario',
        description: 'Actualiza la información personal del usuario que hace la solicitud. Requiere JWT.',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Doctor Strange'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'dr.strange@hospital.com'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Perfil actualizado',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Perfil actualizado exitosamente'),
                    new OA\Property(property: 'data', type: 'object'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Error de validación o datos faltantes'
    )]
    public function updateProfile(Request $request, ActualizarPerfil $useCase)
    {
        try {
            $usuario = $useCase->execute($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data'    => $usuario
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
