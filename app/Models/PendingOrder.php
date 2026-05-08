<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'token',
        'tenant_id',
        'customer_name',
        'table_number',
        'items',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
