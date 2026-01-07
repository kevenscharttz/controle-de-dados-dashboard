<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('super-admin') || $authUser->hasRole('super_admin'))) {
            return true;
        }
        // Organization-manager pode listar usuários da sua organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            return true;
        }
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, AuthUser $targetUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('super-admin') || $authUser->hasRole('super_admin'))) {
            return true;
        }
        // Organization-manager pode ver usuários da sua organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            $managerOrgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            $targetOrgIds = $targetUser->organizations()->pluck('organizations.id')->toArray();
            return count(array_intersect($managerOrgIds, $targetOrgIds)) > 0;
        }
        return $authUser->can('View:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser, AuthUser $targetUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('super-admin') || $authUser->hasRole('super_admin'))) {
            return true;
        }
        // Manager só pode editar usuários da sua organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            $managerOrgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            $targetOrgIds = $targetUser->organizations()->pluck('organizations.id')->toArray();
            return count(array_intersect($managerOrgIds, $targetOrgIds)) > 0;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the given target user.
     */
    public function delete(AuthUser $authUser, AuthUser $targetUser): bool
    {
        // Super-admin pode tudo
        if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('super-admin') || $authUser->hasRole('super_admin'))) {
            return true;
        }

        // Organization-manager: só pode excluir usuários que compartilhem ao menos uma organização
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            $managerOrgIds = $authUser->organizations()->pluck('organizations.id')->toArray();
            $targetOrgIds = $targetUser->organizations()->pluck('organizations.id')->toArray();
            return count(array_intersect($managerOrgIds, $targetOrgIds)) > 0;
        }

        // Fallback para permissões granulares
        return $authUser->can('Delete:User');
    }

    /**
     * Determine whether the user can perform bulk deletes.
     * Filament calls this to authorize bulk delete actions. Allow for super-admin and
     * organization-manager (list scoping should prevent managers from seeing other org users).
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('super-admin') || $authUser->hasRole('super_admin'))) {
            return true;
        }

        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('organization-manager')) {
            return true;
        }

        return $authUser->can('DeleteAny:User');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:User');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:User');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }

}