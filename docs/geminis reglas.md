

# 🧠 🧾 MASTER CONTEXT – PROYECTO LARAVEL MODULAR + JWT

---

## 🚀 1. CONTEXTO GENERAL

Este proyecto es:

* Backend en Laravel
* Arquitectura: **Modular Monolith + Clean ligera**
* Autenticación: **JWT**
* Dominio: **Sistema de atención domiciliaria médica**
* idioma: español

---

## 🧱 2. ESTRUCTURA OBLIGATORIA

```bash
Modules/
 ├── {Modulo}/
 │    ├── Domain/
 │    │    ├── Entities/
 │    │    └── Contracts/
 │    │
 │    ├── Application/
 │    │    └── UseCases/
 │    │
 │    ├── Infrastructure/
 │    │    ├── Models/
 │    │    ├── Repositories/
 │    │    ├── Services/
 │    │    └── Http/Controllers/
 │    │
 │    └── Routes/
```

---

## 📌 3. REGLAS ESTRICTAS (MUY IMPORTANTE)

### 🔴 PROHIBIDO

* ❌ lógica en Controllers
* ❌ Services gigantes tipo “Dios”
* ❌ mezclar lógica de negocio con Eloquent directo
* ❌ usar `app/Models`
* ❌ usar `app/Http/Controllers` para negocio

---

### 🟢 OBLIGATORIO

* ✅ Cada acción = un **UseCase**
* ✅ Controllers solo delegan
* ✅ Eloquent SOLO en Infrastructure
* ✅ Lógica reutilizable = Services
* ✅ DB se accede vía Repository

---

## 🧠 4. FLUJO ARQUITECTÓNICO

```txt
Request
 → Route (Modules)
 → Controller
 → UseCase
 → Repository
 → Model (Eloquent)
 → DB
```

---

## 🔐 5. AUTENTICACIÓN JWT

### Reglas:

* JWT obligatorio en endpoints protegidos
* Middleware: `auth:api`
* Usuario autenticado se obtiene con:

```php
auth()->user();
```

---

## 📦 6. EJEMPLO COMPLETO (PACIENTES)

---

### 📍 Controller

```php
class PacienteController
{
    public function store(Request $request, CrearPaciente $useCase)
    {
        return response()->json(
            $useCase->execute($request->all())
        );
    }
}
```

---

### 📍 UseCase

```php
class CrearPaciente
{
    private $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        // Validación básica
        if (empty($data['nombre_completo'])) {
            throw new \Exception("Nombre requerido");
        }

        return $this->repo->crear($data);
    }
}
```

---

### 📍 Contract

```php
interface PacienteRepositoryInterface
{
    public function crear(array $data);
}
```

---

### 📍 Repository

```php
class PacienteRepository implements PacienteRepositoryInterface
{
    public function crear(array $data)
    {
        return Paciente::create($data);
    }
}
```

---

### 📍 Model (Eloquent)

```php
class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'nombre_completo',
        'identificacion',
        'direccion',
        'latitud',
        'longitud'
    ];
}
```

---

### 📍 Ruta

```php
Route::prefix('pacientes')->middleware('auth:api')->group(function () {
    Route::post('/', [PacienteController::class, 'store']);
});
```

---

## 🧠 7. CÓMO TRADUCIR TUS CASOS DE USO

Tú ya tienes esto 👇

```txt
crearPaciente
registrarCheckInVisita
optimizarRuta
```

👉 Se convierte en:

```bash
UseCases/
 ├── CrearPaciente.php
 ├── RegistrarCheckInVisita.php
 ├── OptimizarRuta.php
```

---

## 🔥 8. EJEMPLO COMPLEJO (CHECK-IN)

```php
class RegistrarCheckInVisita
{
    public function execute($idVisita, $lat, $lng)
    {
        $visita = Visita::findOrFail($idVisita);

        if ($visita->estado !== 'PROGRAMADA') {
            throw new \Exception("Visita no válida");
        }

        $visita->update([
            'latitud_checkin' => $lat,
            'longitud_checkin' => $lng,
            'estado' => 'EN_PROCESO'
        ]);

        return $visita;
    }
}
```

---

## 🧠 9. SERVICES (CUÁNDO USARLOS)

Solo cuando hay lógica compleja:

Ejemplo:

```bash
Services/
 ├── OptimizacionRutaService.php
 ├── ValidacionTutelaService.php
```

---

Ejemplo:

```php
class OptimizacionRutaService
{
    public function calcular(array $visitas)
    {
        // lógica pesada tipo algoritmo
    }
}
```

---

## 🔌 10. REGISTRO DE MÓDULOS

### Composer

```json
"autoload": {
  "psr-4": {
    "Modules\\": "Modules/"
  }
}
```

---

### Cargar rutas

```php
foreach (glob(base_path('Modules/*/Routes/api.php')) as $routeFile) {
    require $routeFile;
}
```

---

## 🧪 11. ESTÁNDARES PARA IA (MUY IMPORTANTE)

Cuando la IA genere código:

* Debe crear UseCase SIEMPRE
* Debe usar Repository SIEMPRE
* No puede meter lógica en Controller
* No puede saltarse capas
* Debe respetar nombres exactos de tablas
* No inventar campos

---

## 🧠 12. DOMINIOS DEL PROYECTO

Basado en tu sistema:

* Autenticación
* Pacientes
* Geolocalización
* Visitas
* Personal Médico
* Logística
* Laboratorios

---

## 🔥 13. BONUS PRO (VERSIONADO API)

```php
Route::prefix('v1')->group(function () {
    require base_path('Modules/Pacientes/Routes/api.php');
});
```

