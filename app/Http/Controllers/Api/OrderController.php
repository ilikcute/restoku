<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Transactions\Order\StoreOrderRequest;
use App\Http\Resources\Api\Transactions\OrderResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrderController extends BaseApiController implements HasMiddleware
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('shift', only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        $orders = $this->orderRepository->getAllByTenant($request->user()->tenant_id, 20);

        return $this->successResponse(OrderResource::collection($orders)->response()->getData(true));
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $tenantId = $request->user()->tenant_id;
        $userId = $request->user()->id;
        $requestIdempotencyKey = $validated['idempotency_key'];

        $shift = $request->attributes->get('current_shift');

        if (! $shift instanceof Shift || $shift->tenant_id !== $tenantId) {
            return $this->errorResponse('Anda harus membuka Shift terlebih dahulu sebelum melakukan transaksi.', 422);
        }

        $result = $this->orderRepository->processOrder(
            $tenantId,
            $userId,
            $shift,
            $validated,
            $requestIdempotencyKey
        );

        if ($result['status'] === 'existing') {
            return $this->successResponse(new OrderResource($result['order']), 'Order already processed.');
        }

        return $this->successResponse(new OrderResource($result['order']), 'Order created successfully', 201);
    }

    public function show(Order $order)
    {
        $this->authorizeTenant($order);

        return $this->successResponse(new OrderResource($order->load(['items.product', 'user', 'shift'])));
    }

    public function downloadReceipt(Order $order)
    {
        $this->authorizeTenant($order);

        $order->load(['items.product.category', 'user', 'tenant', 'shift']);

        $pdf = Pdf::loadView('reports.order_receipt_pdf', ['order' => $order])
            ->setPaper([0, 0, 226, 800], 'portrait'); // Custom height for thermal-like PDF

        return $pdf->download('Receipt_'.$order->order_number.'.pdf');
    }
}
