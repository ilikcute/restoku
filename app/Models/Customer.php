<?php

namespace App\Models;

use App\Traits\LogsTenantActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    use LogsTenantActivity;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
