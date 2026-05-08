<?php

namespace App\Models;

use App\Traits\LogsTenantActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory;
    use LogsTenantActivity;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'user_id',
        'purchase_number',
        'purchase_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'total_return',
        'return_date',
        'return_user_id',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function returnUser()
    {
        return $this->belongsTo(User::class, 'return_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
