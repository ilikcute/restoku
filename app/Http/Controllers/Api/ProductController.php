<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Product\StoreProductRequest;
use App\Http\Requests\Api\Master\Product\UpdateProductRequest;
use App\Http\Resources\Api\Master\ProductResource;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends BaseApiController
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Product::where('tenant_id', $request->user()->tenant_id)
            ->with(['category', 'unit', 'supplier', 'stock']);

        // Server-side filtering
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('code', 'like', '%'.$request->q.'%')
                    ->orWhere('barcode', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate($request->per_page ?? 10);

        return $this->successResponse([
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $this->imageService->compressAndStore($request->file('image'));
            $validated['image'] = $path;
        }

        return DB::transaction(function () use ($request, $validated) {
            $validated['tenant_id'] = $request->user()->tenant_id;
            $validated['slug'] = Str::slug($validated['name']).'-'.rand(1000, 9999);

            $product = Product::create($validated);

            return $this->successResponse(new ProductResource($product->load(['category', 'unit', 'supplier', 'stock'])), 'Product created successfully', 201);
        });
    }

    public function show(Product $product)
    {
        $this->authorizeTenant($product);

        return $this->successResponse(new ProductResource($product->load(['category', 'unit', 'supplier', 'stock'])));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorizeTenant($product);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $this->imageService->compressAndStore($request->file('image'));
            $validated['image'] = $path;
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']).'-'.rand(1000, 9999);
        }

        $product->update($validated);

        return $this->successResponse(new ProductResource($product->load(['category', 'unit', 'supplier', 'stock'])), 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $this->authorizeTenant($product);
        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }

    public function getNextCode(Request $request)
    {
        $lastProduct = Product::where('tenant_id', $request->user()->tenant_id)
            ->whereRaw('code REGEXP "^[0-9]+$"')
            ->orderByRaw('CAST(code AS UNSIGNED) DESC')
            ->first();

        if (! $lastProduct) {
            $nextCode = '10000001';
        } else {
            $nextCode = (string) ((int) $lastProduct->code + 1);
        }

        return $this->successResponse(['next_code' => $nextCode]);
    }
}
