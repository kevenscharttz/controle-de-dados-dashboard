<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Logos;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'single_dashboard',
        'logo_url',
        'logo_alt_text',
        'logo_settings',
        'logo_filename',
    ];

    protected $casts = [
        'logo_settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function (Organization $org) {
            if ($org->isDirty('logo_url')) {
                $original = $org->getOriginal('logo_url');
                if ($original && $original !== $org->logo_url) {
                    Logos::deleteIfExists($original);
                }
            }
        });

        static::deleting(function (Organization $org) {
            Logos::deleteIfExists($org->logo_url);
        });
    }

    // 🔗 Relacionamentos
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function dashboards()
    {
        return $this->hasMany(Dashboard::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
