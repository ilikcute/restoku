<?php

namespace App\Models;

use App\Traits\LogsTenantActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use LogsTenantActivity;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'unit_id',
        'code',
        'supplier_id',
        'name',
        'brand_name',
        'short_name',
        'slug',
        'barcode',
        'description',
        'cost_price',
        'price',
        'discount_amount',
        'ojol_price',
        'ojol_discount',
        'wholesale_price',
        'wholesale_discount',
        'tax_rate',
        'service_charge_rate',
        'image',
        'is_active',
        'stock_type',
        'minimum_stock',
        'maximum_stock',
        'reorder_quantity',
        'safety_stock',
        'lead_time',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isTracked(): bool
    {
        return $this->stock_type === 'trackable';
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if (empty($product->code)) {
                $lastProduct = static::where('tenant_id', $product->tenant_id)
                    ->orderBy('code', 'desc')
                    ->first();

                if (! $lastProduct || ! is_numeric($lastProduct->code)) {
                    $product->code = '10000001';
                } else {
                    $product->code = (int) $lastProduct->code + 1;
                }
            }
        });
    }
}
