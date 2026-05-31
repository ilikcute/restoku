<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempOrder extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'order_number',
        'table_number',
        'subtotal',
        'tax_amount',
        'service_charge',
        'total_amount',
        'date',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TempOrderItem::class, 'temp_order_id');
    }
}
