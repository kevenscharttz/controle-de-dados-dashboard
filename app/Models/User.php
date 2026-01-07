<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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

        protected static function boot()
        {
            parent::boot();

            // Impedir que não-super-admin atribua role super-admin indevidamente
            static::saving(function (User $user) {
                $actor = auth()->user();
                if ($actor && method_exists($actor, 'hasRole') && ! $actor->hasRole('super-admin')) {
                    // Se o usuário já existe e tentarem sincronizar a role super-admin via formulário/relationship
                    if ($user->exists) {
                        // Remover pending super-admin do relation atribuído (post-save sincroniza via Filament)
                        // Não temos acesso direto às roles selecionadas antes do sync aqui, então após salvar garantimos a limpeza abaixo.
                    }
                }
            });

            static::saved(function (User $user) {
                $actor = auth()->user();
                if ($actor && method_exists($actor, 'hasRole') && ! $actor->hasRole('super-admin')) {
                    // Se por algum motivo a role super-admin foi atribuída, removê-la
                    if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                        $user->removeRole('super-admin');
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
