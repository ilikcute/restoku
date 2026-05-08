<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Transactions\Purchase\StorePurchaseRequest;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends BaseApiController
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $purchases = Purchase::where('tenant_id', $request->user()->tenant_id)
            ->with(['supplier', 'user'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(PurchaseResource::collection($purchases)->response()->getData(true));
    }

    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $tenantId = $request->user()->tenant_id;
            $userId = $request->user()->id;

            // 1. Calculate Totals
            $subtotal = 0;
            $taxAmountTotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('tenant_id', $tenantId)->findOrFail($item['product_id']);
                $itemSubtotal = $item['cost_price'] * $item['quantity'];

                // Calculate per item tax if applicable (e.g. 11% PPN)
                $taxRate = $validated['tax_rate'] ?? 0;
                $itemTax = $itemSubtotal * ($taxRate / 100);

                $subtotal += $itemSubtotal;
                $taxAmountTotal += $itemTax;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'cost_price' => $item['cost_price'],
                    'quantity' => $item['quantity'],
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $totalAmount = $subtotal + $taxAmountTotal;
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $paymentStatus = ($paymentMethod === 'credit') ? 'unpaid' : 'paid';

            // 2. Create Purchase
            $purchase = Purchase::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $userId,
                'purchase_number' => $this->inventoryService->generatePurchaseNumber($tenantId),
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmountTotal,
                'total_amount' => $totalAmount,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // 3. Create Purchase Items
            foreach ($itemsData as $itemData) {
                $purchase->items()->create($itemData);

                // Optional: Update product cost price to latest purchase price
                Product::where('tenant_id', $tenantId)
                    ->where('id', $itemData['product_id'])
                    ->update([
                        'cost_price' => $itemData['cost_price'],
                    ]);
            }

            // 4. Update Stock and Record Financial Transaction
            $this->inventoryService->adjustStockFromPurchase($purchase, $userId, $validated['account_id'] ?? null);

            return $this->successResponse(new PurchaseResource($purchase->load('items.product')), 'Purchase recorded and stock updated.', 201);
        });
    }

    public function show(Purchase $purchase)
    {
        $this->authorizeTenant($purchase);

        return $this->successResponse(new PurchaseResource($purchase->load(['items.product', 'supplier', 'user'])));
    }

    public function downloadPdf(Purchase $purchase)
    {
        $this->authorizeTenant($purchase);

        $purchase->load(['items.product', 'user', 'tenant', 'supplier']);

        $pdf = Pdf::loadView('reports.purchase_receipt_pdf', ['purchase' => $purchase])
            ->setPaper([0, 0, 226, 800], 'portrait');

        return $pdf->download('Purchase_'.$purchase->purchase_number.'.pdf');
    }
}
