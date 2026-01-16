<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
        'created_by',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dashboards()
    {
        return $this->hasMany(Dashboard::class, 'folder_id');
    }
}
