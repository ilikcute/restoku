<?php

namespace App\Models;

use App\Traits\LogsTenantActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use LogsTenantActivity;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'shift_id',
        'user_id',
        'customer_id',
        'order_number',
        'idempotency_key',
        'customer_name',
        'table_number',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'rounding',
        'total_amount',
        'total_return',
        'return_date',
        'return_user_id',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
        'notes',
        'is_synced_to_dpkad',
        'synced_to_dpkad_at',
    ];

    protected $casts = [
        'is_synced_to_dpkad' => 'boolean',
        'synced_to_dpkad_at' => 'datetime',
    ];

    public function returnUser()
    {
        return $this->belongsTo(User::class, 'return_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
