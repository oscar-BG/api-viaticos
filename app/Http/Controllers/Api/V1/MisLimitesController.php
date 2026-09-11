<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Viaticos\ResolveUserViaticoLimits;
use App\Http\Controllers\Controller;
use App\Http\Resources\LimiteViaticoResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MisLimitesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        ResolveUserViaticoLimits $resolveUserViaticoLimits,
    ): AnonymousResourceCollection {
        /** @var User $user */
        $user = $request->user();

        return LimiteViaticoResource::collection(
            $resolveUserViaticoLimits->handle($user),
        )->additional(['message' => 'Límites obtenidos correctamente.']);
    }
}
