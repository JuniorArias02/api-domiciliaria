<?php

namespace Modules\SabanaClinica\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarColumnaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'field' => 'required|string',
            'value' => 'nullable', // Puede ser numérico, string, fecha, etc.
        ];
    }
}
