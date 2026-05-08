<?php

namespace App\Http\Resources\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_name' => $this->brand_name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'code' => $this->code,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'cost_price' => (float) $this->cost_price,
            'price' => (float) $this->price,
            'discount_amount' => (float) $this->discount_amount,
            'ojol_price' => (float) $this->ojol_price,
            'ojol_discount' => (float) $this->ojol_discount,
            'wholesale_price' => (float) $this->wholesale_price,
            'wholesale_discount' => (float) $this->wholesale_discount,
            'tax_rate' => (float) $this->tax_rate,
            'service_charge_rate' => (float) $this->service_charge_rate,
            'stock_type' => $this->stock_type,
            'minimum_stock' => (float) $this->minimum_stock,
            'maximum_stock' => (float) $this->maximum_stock,
            'reorder_quantity' => (float) $this->reorder_quantity,
            'safety_stock' => (float) $this->safety_stock,
            'lead_time' => (int) $this->lead_time,
            'is_active' => (bool) $this->is_active,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'stock' => new StockResource($this->whenLoaded('stock')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
