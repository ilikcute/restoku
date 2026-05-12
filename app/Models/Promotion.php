<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'content',
        'type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'applicable_type',
        'requirement_data',
        'is_active',
        'start_date',
        'end_date',
        'priority',
        'is_stackable',
        'is_multiple',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_stackable' => 'boolean',
        'is_multiple' => 'boolean',
        'priority' => 'integer',
        'discount_value' => 'float',
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'requirement_data' => 'array',
    ];

    /**
     * Relationship with specific products.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    /**
     * Relationship with specific categories.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_promotions')
            ->withPivot('discount_amount');
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_promotions')
            ->withPivot('discount_amount');
    }

    /**
     * Scope a query to only include active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }
}
