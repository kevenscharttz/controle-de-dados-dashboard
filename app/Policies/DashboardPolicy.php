<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Dashboard;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        return $authUser->can('ViewAny:Dashboard');
    }

    public function view(AuthUser $authUser, Dashboard $dashboard): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }

        // Só pode ver se o dashboard pertence a uma das organizações do usuário
        if ($dashboard->organization_id) {
            $orgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            if (! in_array($dashboard->organization_id, $orgIds, true)) {
                return false;
            }
        }

        // Managers da organização podem ver todos os dashboards da organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            return true;
        }

        // Públicos: qualquer membro da organização pode ver
        if ($dashboard->visibility === 'public') {
            return true;
        }

        // Privados: apenas usuários selecionados como viewers
        return $dashboard->viewers()->where('users.id', $authUser->id)->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        return $authUser->can('Create:Dashboard');
    }

    public function update(AuthUser $authUser, Dashboard $dashboard): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        // Manager só pode editar dashboards da sua organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            $orgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            return in_array($dashboard->organization_id, $orgIds, true);
        }
        return false;
    }

    public function delete(AuthUser $authUser, Dashboard $dashboard): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        return $authUser->can('Delete:Dashboard');
    }

    public function restore(AuthUser $authUser, Dashboard $dashboard): bool
    {
        return $authUser->can('Restore:Dashboard');
    }

    public function forceDelete(AuthUser $authUser, Dashboard $dashboard): bool
    {
        return $authUser->can('ForceDelete:Dashboard');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Dashboard');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Dashboard');
    }

    public function replicate(AuthUser $authUser, Dashboard $dashboard): bool
    {
        return $authUser->can('Replicate:Dashboard');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Dashboard');
    }

}