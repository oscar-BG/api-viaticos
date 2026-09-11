<?php

namespace App\Actions\Viaticos;

use App\Enums\TipoComprobanteViaticoClave;
use App\Exceptions\MissingUserHierarchicalLevelException;
use App\Exceptions\MissingViaticoLimitConfigurationException;
use App\Models\ConceptoViatico;
use App\Models\TipoComprobanteViatico;
use App\Models\User;
use App\Models\ViaticoLimite;
use Illuminate\Support\Collection;

class ResolveUserViaticoLimits
{
    /**
     * @return Collection<int, array{
     *     concepto: ConceptoViatico,
     *     tipo_comprobante: TipoComprobanteViatico|null,
     *     limite_aplicado: string|null,
     *     excedente: string
     * }>
     */
    public function handle(User $user): Collection
    {
        if ($user->nivel_jerarquico_id === null) {
            throw new MissingUserHierarchicalLevelException;
        }

        $conceptos = ConceptoViatico::query()
            ->active()
            ->oldest('id')
            ->get();

        $tiposComprobante = TipoComprobanteViatico::query()
            ->active()
            ->get()
            ->keyBy(fn (TipoComprobanteViatico $tipo): string => $tipo->clave->value);

        $limites = ViaticoLimite::query()
            ->active()
            ->where('nivel_jerarquico_id', $user->nivel_jerarquico_id)
            ->whereIn('concepto_id', $conceptos->modelKeys())
            ->get()
            ->keyBy(fn (ViaticoLimite $limite): string => $this->configurationKey(
                $limite->concepto_id,
                $limite->tipo_comprobante_id,
            ));

        return $conceptos->flatMap(function (ConceptoViatico $concepto) use ($tiposComprobante, $limites): array {
            if (! $concepto->tiene_limite) {
                return [[
                    'concepto' => $concepto,
                    'tipo_comprobante' => null,
                    'limite_aplicado' => null,
                    'excedente' => '0.00',
                ]];
            }

            $clavesRequeridas = $concepto->diferencia_tipo_comprobante
                ? [TipoComprobanteViaticoClave::Factura, TipoComprobanteViaticoClave::ValeAzul]
                : [TipoComprobanteViaticoClave::NoAplica];

            return array_map(function (TipoComprobanteViaticoClave $clave) use ($concepto, $tiposComprobante, $limites): array {
                $tipoComprobante = $tiposComprobante->get($clave->value);

                if (! $tipoComprobante) {
                    throw new MissingViaticoLimitConfigurationException($concepto->clave->value, $clave->value);
                }

                $limite = $limites->get($this->configurationKey($concepto->id, $tipoComprobante->id));

                if (! $limite) {
                    throw new MissingViaticoLimitConfigurationException($concepto->clave->value, $clave->value);
                }

                return [
                    'concepto' => $concepto,
                    'tipo_comprobante' => $tipoComprobante,
                    'limite_aplicado' => $limite->monto_maximo,
                    'excedente' => '0.00',
                ];
            }, $clavesRequeridas);
        })->values();
    }

    private function configurationKey(int $conceptoId, int $tipoComprobanteId): string
    {
        return $conceptoId.':'.$tipoComprobanteId;
    }
}
