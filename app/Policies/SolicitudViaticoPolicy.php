<?php

namespace App\Policies;

use App\Enums\SolicitudViaticoStatus;
use App\Enums\UserRole;
use App\Models\SolicitudViatico;
use App\Models\User;

class SolicitudViaticoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Colaborador;
    }

    public function viewAnyForFinance(User $user): bool
    {
        return $user->role === UserRole::Finanzas;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $user->role === UserRole::Finanzas
            || ($user->role === UserRole::Colaborador && $solicitudViatico->user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Colaborador;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $user->role === UserRole::Colaborador
            && $solicitudViatico->user_id === $user->id
            && in_array($solicitudViatico->status, [
                SolicitudViaticoStatus::Borrador,
                SolicitudViaticoStatus::Rechazada,
            ], true);
    }

    public function submit(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $user->role === UserRole::Colaborador
            && $solicitudViatico->user_id === $user->id
            && $solicitudViatico->status === SolicitudViaticoStatus::Borrador;
    }

    public function cancel(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $this->update($user, $solicitudViatico);
    }

    public function approve(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $user->role === UserRole::Finanzas
            && $solicitudViatico->status === SolicitudViaticoStatus::EnRevision;
    }

    public function reject(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $this->approve($user, $solicitudViatico);
    }

    public function viewAudit(User $user, SolicitudViatico $solicitudViatico): bool
    {
        return $this->view($user, $solicitudViatico);
    }
}
