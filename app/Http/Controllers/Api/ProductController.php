<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Product\StoreProductRequest;
use App\Http\Requests\Api\Master\Product\UpdateProductRequest;
use App\Http\Resources\Api\Master\ProductResource;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends BaseApiController
{
    public function __construct(
        protected ImageService $imageService,
        protected ProductRepositoryInterface $productRepository,
        protected \App\Services\ProductExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $products = $this->productRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->q,
            $request->integer('category_id') ?: null,
            $request->integer('per_page') ?: 10
        );

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

            $product = $this->productRepository->create($validated);

            return $this->successResponse(
                new ProductResource($product->load(['category', 'unit', 'supplier', 'stock'])),
                'Product created successfully',
                201
            );
        });
    }

    public function show(Product $product)
    {
        $this->authorizeTenant($product);

        return $this->successResponse(
            new ProductResource($product->load(['category', 'unit', 'supplier', 'stock']))
        );
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorizeTenant($product);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $this->imageService->compressAndStore($request->file('image'));
            $validated['image'] = $path;
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']).'-'.rand(1000, 9999);
        }

        $product = $this->productRepository->update($product->id, $validated);

        return $this->successResponse(
            new ProductResource($product->load(['category', 'unit', 'supplier', 'stock'])),
            'Product updated successfully'
        );
    }

    public function destroy(Product $product)
    {
        $this->authorizeTenant($product);

        $this->productRepository->delete($product->id);

        return $this->successResponse(null, 'Product deleted successfully');
    }

    public function getNextCode(Request $request)
    {
        $nextCode = $this->productRepository->getNextCode($request->user()->tenant_id);

        return $this->successResponse(['next_code' => $nextCode]);
    }

    public function export(Request $request)
    {
        $writer = $this->exportService->export($request->user()->tenant_id);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'products_export_' . date('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $writer = $this->exportService->downloadTemplate();
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_produk.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, \App\Services\ProductImportService $importService)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:2048',
        ]);

        $result = $importService->import($request->file('file'), $request->user()->tenant_id);

        return $this->successResponse($result, 'Import process completed');
    }
}
