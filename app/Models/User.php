<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Explicitly allow Filament panel access for super_admin, super-admin, or panel_user roles.
     * Shield's HasPanelShield also provides this, but we ensure clarity and compatibility.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (method_exists($this, 'hasRole')) {
            $allowedRoles = [
                'super_admin',
                'super-admin',
                // Permitir acesso para gerentes de organização
                'organization-manager',
                // Papel genérico configurável do Shield para acesso ao painel
                config('filament-shield.panel_user.name', 'panel_user'),
                // Usuário padrão
                'user',
                'usuario',
            ];

            foreach ($allowedRoles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        }
        return false;
    }

        protected static function boot()
        {
            parent::boot();

            // Impedir que não-super-admin atribua role super-admin indevidamente
            static::saving(function (User $user) {
                $actor = auth()->user();
                $actorIsSuper = $actor && method_exists($actor, 'hasRole') && ($actor->hasRole('super_admin') || $actor->hasRole('super-admin'));
                if ($actor && ! $actorIsSuper) {
                    // Se o usuário já existe e tentarem sincronizar a role super-admin via formulário/relationship
                    if ($user->exists) {
                        // Remover pending super-admin do relation atribuído (post-save sincroniza via Filament)
                        // Não temos acesso direto às roles selecionadas antes do sync aqui, então após salvar garantimos a limpeza abaixo.
                    }
                }
            });

            static::saved(function (User $user) {
                $actor = auth()->user();
                $actorIsSuper = $actor && method_exists($actor, 'hasRole') && ($actor->hasRole('super_admin') || $actor->hasRole('super-admin'));
                if ($actor && ! $actorIsSuper) {
                    // Se por algum motivo a role super-admin foi atribuída, removê-la
                    if (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('super-admin'))) {
                        if ($user->hasRole('super_admin')) {
                            $user->removeRole('super_admin');
                        }
                        if ($user->hasRole('super-admin')) {
                            $user->removeRole('super-admin');
                        }
                    }
                }
            });

        }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
            ->select('organizations.*'); // Especifica explicitamente as colunas da tabela organizations
    }

}
