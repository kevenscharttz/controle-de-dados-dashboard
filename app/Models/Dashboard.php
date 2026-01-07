<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'organization_id',
        'type',
        'platform',
        'platform_custom',
        'url',
        'visibility',
        'scope_user_id',
        'scope_profile_id',
        'scope_organization_id',
        'tags',
        'settings',
        'description',
    ];

    protected $casts = [
        'settings' => 'array',
        'tags' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dashboard) {
            // Enforce single dashboard only when organization is configured as single_dashboard
            $organization = Organization::find($dashboard->organization_id);
            if ($organization && (bool) $organization->single_dashboard) {
                $exists = static::where('organization_id', $dashboard->organization_id)->exists();
                if ($exists) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['organization_id' => ['Esta organização está configurada para ter apenas um dashboard e já possui um criado.']]
                    );
                }
            }
        });

        static::updating(function ($dashboard) {
            // Enforce single dashboard only when organization is configured as single_dashboard
            $organization = Organization::find($dashboard->organization_id);
            if ($organization && (bool) $organization->single_dashboard) {
                $exists = static::where('organization_id', $dashboard->organization_id)
                    ->where('id', '!=', $dashboard->id)
                    ->exists();
                if ($exists) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['organization_id' => ['Esta organização está configurada para ter apenas um dashboard e já possui outro criado.']]
                    );
                }
            }
        });
    }

    // Relacionamentos
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Usuários que podem ver este dashboard quando ele é privado.
     */
    public function viewers()
    {
        return $this->belongsToMany(User::class, 'dashboard_user');
    }

    /**
     * Scope dashboards visible to a given user.
     * Usage: Dashboard::visibleTo($user)->get();
     */
    public function scopeVisibleTo($query, $user)
    {
        if (! $user) {
            // guests não podem ver nada
            return $query->whereRaw('0=1');
        }

        // Super-admins veem tudo
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return $query;
        }

        $organizationIds = $user->organizations()->pluck('organizations.id')->toArray();
        // Managers podem ver todos os dashboards (públicos e privados) da(s) sua(s) organização(ões)
        $isManager = method_exists($user, 'hasRole') && $user->hasRole('organization-manager');

        if ($isManager) {
            return $query->whereIn('organization_id', $organizationIds);
        }

        // Outros usuários:
        // - ver públicos da(s) sua(s) organização(ões)
        // - ver privados apenas quando estiverem na lista de viewers
        return $query
            ->whereIn('organization_id', $organizationIds)
            ->where(function ($q) use ($user) {
                $q->where('visibility', 'public')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('visibility', 'private')
                         ->whereHas('viewers', function ($vv) use ($user) {
                             $vv->where('users.id', $user->id);
                         });
                  });
            });
    }
}
