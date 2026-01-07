<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
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

    // Relatórios podem ter múltiplos por organização (sem regra de unicidade)

    // 🔗 Relacionamentos
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Usuários que podem ver este relatório quando ele é privado.
     */
    public function viewers()
    {
        return $this->belongsToMany(User::class, 'report_user');
    }

    /**
     * Scope reports visible to a given user.
     * Usage: Report::visibleTo($user)->get();
     */
    public function scopeVisibleTo($query, $user)
    {
        if (! $user) {
            // guests não podem ver nada
            return $query->whereRaw('0=1');
        }

        // Super-admins veem tudo
        if (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('super_admin'))) {
            return $query;
        }

        $organizationIds = $user->organizations()->pluck('organizations.id')->toArray();
        // Managers podem ver todos os relatórios (públicos e privados) da(s) sua(s) organização(ões)
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
