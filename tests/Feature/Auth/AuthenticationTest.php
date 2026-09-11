<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('returns a token and preserves the server-side role for valid credentials', function () {
    $user = User::factory()->colaborador()->create([
        'email' => 'colaborador@example.com',
        'password' => Hash::make('secret-password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'colaborador@example.com',
        'password' => 'secret-password',
        'device_name' => 'pest',
        'role' => UserRole::Finanzas->value,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.role', UserRole::Colaborador->value)
        ->assertJsonPath('message', 'Inicio de sesión exitoso.');
    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('returns 401 for invalid credentials', function () {
    User::factory()->create([
        'email' => 'invalid@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'invalid@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertExactJson([
            'message' => 'Las credenciales proporcionadas no son válidas.',
            'code' => 'CREDENCIALES_INVALIDAS',
            'errors' => [],
        ]);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('returns 401 when an inactive user attempts to login', function () {
    User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'inactive@example.com',
        'password' => 'secret-password',
    ])->assertUnauthorized();
});

it('returns 422 with Spanish messages when login data is missing', function () {
    $this->postJson('/api/v1/auth/login')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email' => 'El correo electrónico es obligatorio.',
            'password' => 'La contraseña es obligatoria.',
        ]);
});

it('returns 401 when no token is provided to a protected route', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertExactJson([
            'message' => 'No autenticado.',
            'code' => 'NO_AUTENTICADO',
            'errors' => [],
        ]);
});

it('returns the authenticated user role', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);
    $token = $user->createToken('pest')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.role', $role->value)
        ->assertJsonMissingPath('data.password');
})->with(UserRole::cases());

it('returns 403 when an authenticated user has been deactivated', function () {
    $user = User::factory()->inactive()->create();
    $token = $user->createToken('pest')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertForbidden()
        ->assertJsonPath('code', 'USUARIO_INACTIVO');
});

it('deletes only the current access token on logout', function () {
    $user = User::factory()->create();
    $currentToken = $user->createToken('current');
    $user->createToken('other');

    $this->withToken($currentToken->plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $currentToken->accessToken->id]);
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('returns 429 after five login attempts for the same identity and IP', function () {
    $payload = [
        'email' => 'throttled@example.com',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', $payload)->assertUnauthorized();
    }

    $this->postJson('/api/v1/auth/login', $payload)->assertTooManyRequests();
});
