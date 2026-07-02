<?php

namespace Modules\SabanaClinica\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearFilaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'identificacion' => 'required|string|max:20',
            'nombre_y_apellido' => 'required|string|max:200',
            'tipo_documento' => 'nullable|string|in:CC,TI,CE,PA,RC,MS,AS',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string|in:M,F,N',
            'id_aseguradora' => 'nullable|integer',
            'direccion' => 'nullable|string|max:255',
        ];
    }
}
