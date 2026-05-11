<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Transactions\Purchase\StorePurchaseRequest;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Interfaces\PurchaseRepositoryInterface;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseController extends BaseApiController
{
    protected PurchaseRepositoryInterface $purchaseRepository;

    public function __construct(PurchaseRepositoryInterface $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    public function index(Request $request)
    {
        $purchases = $this->purchaseRepository->getAllByTenant($request->user()->tenant_id, 20);

        return $this->successResponse(PurchaseResource::collection($purchases)->response()->getData(true));
    }

    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        try {
            $purchase = $this->purchaseRepository->createPurchase(
                $request->user()->tenant_id,
                $request->user()->id,
                $validated
            );

            return $this->successResponse(new PurchaseResource($purchase), 'Purchase recorded and stock updated.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
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
