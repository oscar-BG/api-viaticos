<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SolicitudViatico;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApproveSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');

        return $solicitud instanceof SolicitudViatico
            && ($this->user()?->can('approve', $solicitud) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'importe_autorizado' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'password' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'importe_autorizado.required' => 'El importe autorizado es obligatorio.',
            'importe_autorizado.numeric' => 'El importe autorizado debe ser numérico.',
            'importe_autorizado.decimal' => 'El importe autorizado debe tener como máximo 2 decimales.',
            'importe_autorizado.gt' => 'El importe autorizado debe ser mayor que cero.',
            'importe_autorizado.max' => 'El importe autorizado excede el monto permitido.',
            'password.required' => 'La contraseña de confirmación es obligatoria.',
            'password.string' => 'La contraseña de confirmación debe ser una cadena de texto.',
        ];
    }
}
