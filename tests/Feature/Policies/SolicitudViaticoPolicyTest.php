<?php

use App\Models\SolicitudViatico;
use App\Models\User;
use App\Policies\SolicitudViaticoPolicy;

it('grants collaborator collection and creation abilities only to collaborators', function () {
    $policy = new SolicitudViaticoPolicy;
    $colaborador = User::factory()->colaborador()->create();
    $finanzas = User::factory()->finanzas()->create();

    expect($policy->viewAny($colaborador))->toBeTrue()
        ->and($policy->create($colaborador))->toBeTrue()
        ->and($policy->viewAny($finanzas))->toBeFalse()
        ->and($policy->create($finanzas))->toBeFalse()
        ->and($policy->viewAnyForFinance($finanzas))->toBeTrue()
        ->and($policy->viewAnyForFinance($colaborador))->toBeFalse();
});

it('allows only the owner to update submit or cancel an editable request', function () {
    $policy = new SolicitudViaticoPolicy;
    $owner = User::factory()->colaborador()->create();
    $other = User::factory()->colaborador()->create();
    $solicitud = SolicitudViatico::factory()->for($owner)->create();

    expect($policy->update($owner, $solicitud))->toBeTrue()
        ->and($policy->submit($owner, $solicitud))->toBeTrue()
        ->and($policy->cancel($owner, $solicitud))->toBeTrue()
        ->and($policy->update($other, $solicitud))->toBeFalse()
        ->and($policy->submit($other, $solicitud))->toBeFalse()
        ->and($policy->cancel($other, $solicitud))->toBeFalse();
});

it('prevents collaborator changes after a request enters review', function () {
    $policy = new SolicitudViaticoPolicy;
    $owner = User::factory()->colaborador()->create();
    $solicitud = SolicitudViatico::factory()->for($owner)->enRevision()->create();

    expect($policy->update($owner, $solicitud))->toBeFalse()
        ->and($policy->submit($owner, $solicitud))->toBeFalse()
        ->and($policy->cancel($owner, $solicitud))->toBeFalse();
});

it('allows finance to review only requests that are under review', function () {
    $policy = new SolicitudViaticoPolicy;
    $finanzas = User::factory()->finanzas()->create();
    $colaborador = User::factory()->colaborador()->create();
    $underReview = SolicitudViatico::factory()->enRevision()->create();
    $draft = SolicitudViatico::factory()->create();

    expect($policy->approve($finanzas, $underReview))->toBeTrue()
        ->and($policy->reject($finanzas, $underReview))->toBeTrue()
        ->and($policy->approve($colaborador, $underReview))->toBeFalse()
        ->and($policy->reject($colaborador, $underReview))->toBeFalse()
        ->and($policy->approve($finanzas, $draft))->toBeFalse()
        ->and($policy->reject($finanzas, $draft))->toBeFalse();
});

it('allows owners and finance to view requests and their audit trail', function () {
    $policy = new SolicitudViaticoPolicy;
    $owner = User::factory()->colaborador()->create();
    $other = User::factory()->colaborador()->create();
    $finanzas = User::factory()->finanzas()->create();
    $solicitud = SolicitudViatico::factory()->for($owner)->create();

    expect($policy->view($owner, $solicitud))->toBeTrue()
        ->and($policy->viewAudit($owner, $solicitud))->toBeTrue()
        ->and($policy->view($other, $solicitud))->toBeFalse()
        ->and($policy->viewAudit($other, $solicitud))->toBeFalse()
        ->and($policy->view($finanzas, $solicitud))->toBeTrue()
        ->and($policy->viewAudit($finanzas, $solicitud))->toBeTrue();
});
