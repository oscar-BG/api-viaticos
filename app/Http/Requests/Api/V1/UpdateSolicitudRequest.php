<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SolicitudViatico;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');

        return $solicitud instanceof SolicitudViatico
            && ($this->user()?->can('update', $solicitud) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lugar' => ['sometimes', 'required', 'string', 'max:255'],
            'motivo' => ['sometimes', 'required', 'string', 'max:5000'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['sometimes', 'required', 'date', 'after_or_equal:fecha_inicio'],
            'importe_solicitado' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return (new StoreSolicitudRequest)->messages();
    }

    protected function prepareForValidation(): void
    {
        $solicitud = $this->route('solicitud');

        if (! $solicitud instanceof SolicitudViatico || ! $this->hasAny(['fecha_inicio', 'fecha_fin'])) {
            return;
        }

        $this->merge([
            'fecha_inicio' => $this->input('fecha_inicio', $solicitud->fecha_inicio->toDateString()),
            'fecha_fin' => $this->input('fecha_fin', $solicitud->fecha_fin->toDateString()),
        ]);
    }
}
