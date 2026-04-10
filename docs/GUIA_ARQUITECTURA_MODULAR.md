# 🏛️ Guía Paso a Paso: Arquitectura Modular Monolith + Clean Architecture

¡Hola! Entiendo perfectamente que al principio esta arquitectura pueda verse abrumadora con tantas carpetas y archivos, pero una vez que entiendes el flujo, te darás cuenta de que es **extremadamente predecible, limpia y profesional**.

Esta arquitectura se basa en la **separación de responsabilidades**. En vez de tener todo mezclado en un `Controller` (validaciones, consultas a BD, lógica), cada capa tiene un único trabajo.

A continuación, te explico el **orden exacto** en el que debes crear los archivos cuando inicies un nuevo módulo (por ejemplo, imagina que vamos a crear el módulo **Aseguradoras**).

--- 

## 🏗️ Flujo de Creación (El Orden Ideal)

Cuando creas un módulo nuevo, el mejor truco es construir desde "lo más profundo" (la base de datos) hasta "lo más expuesto" (las rutas y controladores).

### PASO 1: El Modelo (La Base de Datos)
**Ubicación:** `Modules/Aseguradoras/Infrastructure/Models/Aseguradora.php`

**¿Qué hace?** Es tu representación directa de la tabla en la base de datos (Eloquent). Aquí solo defines los campos que se pueden llenar (`$fillable`).
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Infrastructure\Models;
use Illuminate\Database\Eloquent\Model;

class Aseguradora extends Model {
    protected $table = 'aseguradoras';
    protected $primaryKey = 'id_aseguradora';
    // Muy importante para proteger la base de datos de asignación masiva.
    protected $fillable = ['nombre', 'nit'];
}
```

---

### PASO 2: El Contrato o Interfaz (Las Reglas)
**Ubicación:** `Modules/Aseguradoras/Domain/Contracts/AseguradoraRepositoryInterface.php`

**¿Qué hace?** Es una lista de "promesas". Le dice al sistema: *"Cualquiera que maneje Aseguradoras TIENE obligatoriamente que tener estas funciones"*. ¡Aquí no hay código real, solo las firmas!
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Domain\Contracts;

interface AseguradoraRepositoryInterface {
    public function crear(array $data);
    public function listar();
}
```

---

### PASO 3: El Repositorio (El Trabajador de la Base de Datos)
**Ubicación:** `Modules/Aseguradoras/Infrastructure/Repositories/AseguradoraRepository.php`

**¿Qué hace?** Cumple el contrato del Paso 2. Es el **ÚNICO** lugar de todo tu módulo donde tienes permiso de usar llamados a Eloquent (ej: `Aseguradora::create()`). El resto de capas le pedirán favores a este archivo.
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Infrastructure\Repositories;
use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Modules\Aseguradoras\Infrastructure\Models\Aseguradora;

class AseguradoraRepository implements AseguradoraRepositoryInterface {
    public function crear(array $data) {
        return Aseguradora::create($data); // <--- Eloquent real
    }
    public function listar() {
        return Aseguradora::all();
    }
}
```

---

### PASO 4: Los Casos de Uso (La Lógica de Negocio)
**Ubicación:** `Modules/Aseguradoras/Application/UseCases/CrearAseguradora.php`

**¿Qué hace?** Aquí es donde pones las validaciones estrictas o lógica de la empresa. El caso de uso **no sabe si usas MySQL o un Excel**, él solo le pide datos a la Interfaz (Paso 2). IMPORTANTE: ¡Se crea un archivo por cada acción! (Uno para Crear, uno para Listar, etc).
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Application\UseCases;
use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Exception;

class CrearAseguradora {
    private $repo;

    // Se inyecta la interfaz automáticamente
    public function __construct(AseguradoraRepositoryInterface $repo) {
        $this->repo = $repo; 
    }

    public function execute(array $data) {
        // Regla de Negocio: El nombre es obligatorio
        if (empty($data['nombre'])) {
            throw new Exception("El nombre de la aseguradora es obligatorio");
        }
        
        // Si todo está bien, mandamos a crear al repositorio
        return $this->repo->crear($data);
    }
}
```

---

### PASO 5: El Controlador (El Mesero)
**Ubicación:** `Modules/Aseguradoras/Infrastructure/Http/Controllers/AseguradoraController.php`

**¿Qué hace?** Recibe la petición web (HTTP), se la pasa al Caso de Uso (Paso 4), y devuelve la respuesta al usuario en formato JSON. **Prohibido** poner `if` de validaciones o consultas a la BD aquí. También es el lugar donde van los atributos modernos de documentación integrando Swagger (`#[OA\Post(...)]`).
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Infrastructure\Http\Controllers;
use Modules\Aseguradoras\Application\UseCases\CrearAseguradora;
use Illuminate\Http\Request;
// (Aquí irían los imports de Swagger)

class AseguradoraController {
    
    // #[OA\Post(...)] (Aquí documentas lo que entra y sale)
    public function store(Request $request, CrearAseguradora $useCase) {
        try {
            // Pasamos todo lo que llegó en el body del Request al Caso de Uso
            $nueva = $useCase->execute($request->all());
            return response()->json(['data' => $nueva], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

---

### PASO 6: Las Rutas
**Ubicación:** `Modules/Aseguradoras/Routes/api.php`

**¿Qué hace?** Expone las URL HTTP. Apunta a tu Controlador (Paso 5) y exige Auth si es necesario.
**Ejemplo:**
```php
use Illuminate\Support\Facades\Route;
use Modules\Aseguradoras\Infrastructure\Http\Controllers\AseguradoraController;

Route::prefix('aseguradoras')->middleware('auth:api')->group(function () {
    Route::post('/', [AseguradoraController::class, 'store']);
});
```

---

### PASO 7 FINAL: El Proveedor de Servicios (El Enchufe)
**Ubicación:** `Modules/Aseguradoras/Providers/AseguradoraServiceProvider.php` (Y luego atarlo en `bootstrap/providers.php`)

**¿Qué hace?** Le dice al núcleo Framework de Laravel: *"Oye, cada vez que en los Casos de Uso te pidan el contrato `AseguradoraRepositoryInterface`, entrégales la clase real `AseguradoraRepository`"*. Es lo que une todo el rompecabezas.
**Ejemplo:**
```php
namespace Modules\Aseguradoras\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Modules\Aseguradoras\Infrastructure\Repositories\AseguradoraRepository;

class AseguradoraServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->bind(
            AseguradoraRepositoryInterface::class, 
            AseguradoraRepository::class
        );
    }
}
```

---

## 🎯 Resumen del Flujo de Peticiones ("El Viaje de tus Datos")

Cuando alguien entra a Postman y manda un POST a `/api/v1/aseguradoras`, esto es lo que pasa tras bambalinas:

1. **Ruta (`Routes/api.php`)** atrapa la petición.
2. La ruta la manda al **Controlador** `store()`.
3. El Controlador agarra la data y le delega el trabajo pesado al **Caso de Uso** `CrearAseguradora->execute()`.
4. El Caso de uso aplica validaciones y si pasa, se lo exige strictamente al **Contrato/Interface**.
5. Laravel, gracias al **Service Provider**, sabe que detrás de ese contrato está trabajando tu **Repositorio** (tu capa de persistencia).
6. El Repositorio agarra el **Modelo (Eloquent)** y manda la consulta a la Base de Datos real.
7. El modelo devuelve la data guardada hasta llegar de nuevo al Controlador, el cual contesta un hermoso `JSON 200 OK`. 

¡Guarda y lee esta guía cuando estés construyendo el siguiente módulo! Aprender Arquitectura Modular con Clean Code toma unas semanas, pero el resultado a largo plazo da una ventaja competitiva brutal para proyectos escalables.
