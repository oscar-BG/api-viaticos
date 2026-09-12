<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SolicitudViatico;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');

        return $solicitud instanceof SolicitudViatico
            && ($this->user()?->can('reject', $solicitud) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de rechazo es obligatorio.',
            'motivo.string' => 'El motivo de rechazo debe ser una cadena de texto.',
            'motivo.max' => 'El motivo de rechazo no debe exceder 5000 caracteres.',
        ];
    }
}
