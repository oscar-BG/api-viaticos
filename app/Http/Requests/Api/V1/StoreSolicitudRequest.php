<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SolicitudViatico;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', SolicitudViatico::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lugar' => ['required', 'string', 'max:255'],
            'motivo' => ['required', 'string', 'max:5000'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'importe_solicitado' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'lugar.required' => 'El lugar es obligatorio.',
            'lugar.string' => 'El lugar debe ser una cadena de texto.',
            'lugar.max' => 'El lugar no debe exceder 255 caracteres.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.string' => 'El motivo debe ser una cadena de texto.',
            'motivo.max' => 'El motivo no debe exceder 5000 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'importe_solicitado.required' => 'El importe solicitado es obligatorio.',
            'importe_solicitado.numeric' => 'El importe solicitado debe ser numérico.',
            'importe_solicitado.decimal' => 'El importe solicitado debe tener como máximo 2 decimales.',
            'importe_solicitado.gt' => 'El importe solicitado debe ser mayor que cero.',
            'importe_solicitado.max' => 'El importe solicitado excede el monto permitido.',
        ];
    }
}
