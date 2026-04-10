# 📘 Guía de Documentación: Sintaxis Moderna Swagger (PHP 8)

Este proyecto utiliza **L5-Swagger / OpenApi** para la generación de la documentación interactiva de la API, y estrictamente utiliza la **Sintaxis Moderna a través de Atributos de PHP 8** (`#[OA\...]`). 

No se deben utilizar los antiguos bloques de comentarios (DocBlocks `/** @OA\... */`).

La ventaja principal de esta sintaxis es que está integrada directamente en PHP, lo que permite aprovechar el tipado duro, auto-completado inteligente desde el IDE, validación de errores estructurales y mantener un código mucho más ordenado.

---

## 🚀 1. Reglas Generales

* **Prefijo Seguro (`#[OA\]`):** Absolutamente todo lo referente a la documentación se envuelve con corchetes en estilo de atributo.
* **Importación obligatoria:** Todos los controladores que vayan a ser documentados deben contener esta línea en la cabecera:
  ```php
  use OpenApi\Attributes as OA;
  ```
* **Variables con nombre (Named Arguments):** Para enviar propiedades a las anotaciones se deben utilizar argumentos con nombre, por ejemplo: `path: '/api/v1/ejemplo'`, `response: 200`.

---

## 📝 2. Comparativa Categórica

### ❌ Forma ANTIGUA (Ignorada/Prohibida en este proyecto)
```php
/**
 * @OA\Post(
 *     path="/api/v1/auth/login",
 *     tags={"Autenticación"},
 *     @OA\Response(
 *         response=200,
 *         description="Genera el Token JWT"
 *     )
 * )
 */
public function login() { ... }
```

### ✅ Forma ACTUAL y OBLIGATORIA (Atributos Nativos PHP 8)
```php
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/login',
    summary: 'Iniciar sesión',
    tags: ['Autenticación']
)]
#[OA\Response(
    response: 200, 
    description: 'Genera el Token JWT'
)]
public function login() { ... }
```

---

## 🧩 3. Construyendo un JSON Request Body Complejo

Al tener la sintaxis nativa de atributos, los anidados en Arrays o en JSON se manejan como instancias de objetos `new OA\...`:

```php
#[OA\RequestBody(
    required: true,
    content: new OA\MediaType(
        mediaType: 'application/json',
        schema: new OA\Schema(
            required: ['nombre', 'email'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', example: 'Juan Pérez'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@demo.com'),
                new OA\Property(property: 'edad', type: 'integer', example: 30) // Propiedad opcional
            ]
        )
    )
)]
```

---

## 🚪 4. Autenticación Global en Rutas (Middlewares)

Para cualquier ruta que necesite token (JWT), asegúrate de agregar explícitamente el array en la llave de seguridad del método Http que lo requiera:

```php
#[OA\Get(
    path: '/api/v1/usuarios/perfil',
    summary: 'Ver mi perfil',
    security: [['bearerAuth' => []]], // ¡Avisa al panel de documentación que debe blindar con Bearer JWT!
    tags: ['Usuarios']
)]
```

---

## 📟 5. Generar la Documentación en Vivo

Siempre que modifiques, agregues o remuevas un atributo `#[OA\]` en algún Controller, **debes lanzar este comando** en la consola del proyecto para compilar visualmente los resultados en `/api/documentation`:

```bash
php artisan l5-swagger:generate
```
