<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'actual_stock',
        'recorded_stock',
        'adjustment_quantity',
        'cost_price',
        'reason',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
