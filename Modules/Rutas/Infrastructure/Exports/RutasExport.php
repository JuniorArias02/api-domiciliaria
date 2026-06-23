<?php

namespace Modules\Rutas\Infrastructure\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RutasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private $rutas;

    public function __construct($rutas)
    {
        $this->rutas = $rutas;
    }

    public function collection()
    {
        return $this->rutas;
    }

    public function headings(): array
    {
        return [
            'ID Ruta',
            'Fecha Ruta',
            'Estado',
            'Profesional',
            'Especialidad Profesional',
            'Total Pacientes Asignados',
        ];
    }

    public function map($ruta): array
    {
        return [
            $ruta->id_ruta,
            $ruta->fecha_ruta,
            $ruta->estado,
            $ruta->personal ? $ruta->personal->nombres . ' ' . $ruta->personal->apellidos : 'Sin Profesional',
            $ruta->personal && $ruta->personal->especialidad ? $ruta->personal->especialidad->nombre : 'N/A',
            $ruta->visitas ? $ruta->visitas->count() : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila 1 (encabezados)
            1    => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D6EFD']]],
        ];
    }
}
