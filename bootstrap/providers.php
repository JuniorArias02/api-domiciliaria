<?php

use App\Providers\AppServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Usuarios\Providers\UsuariosServiceProvider::class,
    Modules\Pacientes\Providers\PacienteServiceProvider::class,
    Modules\Cargos\Providers\CargosServiceProvider::class,
    Modules\Aseguradoras\Providers\AseguradoraServiceProvider::class,
    Modules\Personal\Providers\PersonalServiceProvider::class,
    Modules\Comunas\Providers\ComunaServiceProvider::class,
    Modules\Barrios\Providers\BarrioServiceProvider::class,
    Modules\Cuidadores\Providers\CuidadorServiceProvider::class,
    Modules\PacienteDiagnosticos\Providers\PacienteDiagnosticoServiceProvider::class,
    Modules\OrdenesMedicas\Providers\OrdenMedicaServiceProvider::class,
    Modules\VisitasDomiciliarias\Providers\VisitaDomiciliariaServiceProvider::class,
    Modules\Especialidades\Providers\EspecialidadServiceProvider::class,
    Modules\Servicios\Providers\ServicioServiceProvider::class,
    Modules\Mapas\Providers\MapaServiceProvider::class,
    Modules\Dashboard\Providers\DashboardServiceProvider::class,
    Modules\Agenda\Providers\AgendaServiceProvider::class,
    Modules\Ingresos\Providers\IngresoServiceProvider::class,
    Modules\OrdenesServicio\Providers\OrdenServicioServiceProvider::class,
    Modules\Departamentos\Providers\DepartamentoServiceProvider::class,
    Modules\Municipios\Providers\MunicipioServiceProvider::class,
    Modules\RegistroPrograma\Providers\RegistroProgramaServiceProvider::class,
];
