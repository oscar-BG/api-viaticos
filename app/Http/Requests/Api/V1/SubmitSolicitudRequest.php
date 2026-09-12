<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SolicitudViatico;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');

        return $solicitud instanceof SolicitudViatico
            && ($this->user()?->can('submit', $solicitud) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.required' => 'La contraseña de confirmación es obligatoria.',
            'password.string' => 'La contraseña de confirmación debe ser una cadena de texto.',
        ];
    }
}
