<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthenticateUser $authenticateUser): JsonResponse
    {
        $authentication = $authenticateUser->handle(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('device_name', 'api-client')->toString(),
        );

        return response()->json([
            'data' => [
                'token' => $authentication['plain_text_token'],
                'token_type' => 'Bearer',
                'user' => (new UserResource($authentication['user']->load('nivelJerarquico')))->resolve(),
            ],
            'message' => 'Inicio de sesión exitoso.',
        ]);
    }

    public function logout(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()?->delete();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        return (new UserResource($user->load('nivelJerarquico')))
            ->additional(['message' => 'Usuario autenticado obtenido correctamente.']);
    }
}
