<?php

namespace Modules\Mapas\Domain\Contracts;

interface MapaRepositoryInterface
{
     /**
     * Obtener marcadores livianos con paginación dinámica.
     */
    public function obtenerPuntosPacientes(array $filtros);

    /**
     * Obtener detalle clínico y de servicio de un paciente.
     */
    public function obtenerDetallePaciente(int $id_paciente);

    /**
     * Obtiene la ruta de visitas (coordenadas de pacientes) organizadas por fecha.
     */
    public function obtenerRutaVisitas(array $filtros);

     /**
     * Obtiene las órdenes médicas, el paciente y el profesional vinculado basado en un id_paciente.
     */
    public function obtenerOrdenesPaciente(int $id_paciente);

    /**
     * Obtiene los pacientes filtrados por comuna con datos geográficos básicos.
     */
    public function obtenerPacientesPorComuna(int $id_comuna);

    /**
     * Obtiene todos los marcadores livianos sin paginación.
     */
    public function obtenerTodosLosPuntos(array $filtros);

    /**
     * Predice y optimiza las rutas del mes basado en frecuencia y última visita.
     */
    public function optimizarRutasMes(array $filtros);

    /**
     * Optimiza las rutas del mes basándose estrictamente en el campo orden_mapa de los pacientes.
     */
    public function optimizarRutasMesMetodoOrden(array $filtros);

    /**
     * Organiza rutas basadas en proximidad geográfica con un mínimo de 8 pacientes.
     */
    public function optimizarRutasMesCercania(array $filtros);

    /**
     * Obtiene los datos base para la optimización (Pacientes + Ordenes + Última Visita).
     */
    public function obtenerDatosBaseOptimizacion(array $filtros);

    /**
     * Obtiene los datos base para la predicción de visitas (Pacientes + Ordenes Servicios + Última Visita).
     */
    public function obtenerDatosBasePrediccion(array $filtros);
}

