<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $primaryKey = 'permission_id';

    protected $fillable = [
        'role_id',
        'permission_name',
        'can_access',
    ];

    protected $casts = [
        'can_access' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
