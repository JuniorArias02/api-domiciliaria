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
    Modules\Zonas\Providers\ZonaServiceProvider::class,
    Modules\Comunas\Providers\ComunaServiceProvider::class,
    Modules\Barrios\Providers\BarrioServiceProvider::class,
    Modules\Cuidadores\Providers\CuidadorServiceProvider::class,
    Modules\Tutelas\Providers\TutelaServiceProvider::class,
    Modules\PacienteDiagnosticos\Providers\PacienteDiagnosticoServiceProvider::class,
    Modules\SolicitudesEquipos\Providers\SolicitudEquipoServiceProvider::class,
    Modules\DetalleSolicitudEquipos\Providers\DetalleSolicitudEquipoServiceProvider::class,
    Modules\OrdenesMedicas\Providers\OrdenMedicaServiceProvider::class,
    Modules\VisitasDomiciliarias\Providers\VisitaDomiciliariaServiceProvider::class,
    Modules\Laboratorios\Providers\LaboratorioServiceProvider::class,
    Modules\Telexperticias\Providers\TelexperticiaServiceProvider::class,
    Modules\Especialidades\Providers\EspecialidadServiceProvider::class,
    Modules\Servicios\Providers\ServicioServiceProvider::class,
    Modules\Mapas\Providers\MapaServiceProvider::class,
    Modules\Dashboard\Providers\DashboardServiceProvider::class,
    Modules\Agenda\Providers\AgendaServiceProvider::class,
    Modules\Ingresos\Providers\IngresoServiceProvider::class,
];
