<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Organization;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        // Qualquer usuário autenticado pode listar, mas a listagem deve ser filtrada por escopo no resource
        return $authUser !== null;
    }

    public function view(AuthUser $authUser, Organization $organization): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        // Apenas pode ver organizações às quais pertence
        $orgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
        return in_array($organization->id, $orgIds, true);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Organization');
    }

    public function update(AuthUser $authUser, Organization $organization): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super-admin')) {
            return true;
        }
        // Manager só pode editar sua própria organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            $orgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            return in_array($organization->id, $orgIds, true);
        }
        return false;
    }

    public function delete(AuthUser $authUser, Organization $organization): bool
    {
        return $authUser->can('Delete:Organization');
    }

    public function restore(AuthUser $authUser, Organization $organization): bool
    {
        return $authUser->can('Restore:Organization');
    }

    public function forceDelete(AuthUser $authUser, Organization $organization): bool
    {
        return $authUser->can('ForceDelete:Organization');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Organization');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Organization');
    }

    public function replicate(AuthUser $authUser, Organization $organization): bool
    {
        return $authUser->can('Replicate:Organization');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Organization');
    }

}