<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Requests\Api\Transactions\Order\StoreOrderRequest;
use App\Http\Resources\Api\Transactions\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shift;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PrinterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseApiController implements HasMiddleware
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected PrinterService $printerService,
        protected OrderService $orderService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('shift', only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        $orders = Order::where('tenant_id', $request->user()->tenant_id)
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(OrderResource::collection($orders)->response()->getData(true));
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $tenantId = $request->user()->tenant_id;
        $requestIdempotencyKey = $validated['idempotency_key'];
        $lockKey = 'order:'.$tenantId.':'.$requestIdempotencyKey;

        $existingOrder = Order::where('tenant_id', $tenantId)
            ->where('idempotency_key', $requestIdempotencyKey)
            ->with(['items.product', 'user', 'shift'])
            ->first();

        if ($existingOrder) {
            return $this->successResponse(new OrderResource($existingOrder), 'Order already processed.');
        }

        // Cegah duplikasi order: lock berlaku 60 detik.
        // Jika request yang sama dikirim ulang saat proses masih berjalan,
        // request kedua ditolak sementara. Setelah sukses, retry akan mengembalikan order yang sama.
        $lock = Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            $existingOrder = Order::where('tenant_id', $tenantId)
                ->where('idempotency_key', $requestIdempotencyKey)
                ->with(['items.product', 'user', 'shift'])
                ->first();

            if ($existingOrder) {
                return $this->successResponse(new OrderResource($existingOrder), 'Order already processed.');
            }

            return $this->errorResponse(
                'Transaksi ini sedang diproses. Mohon tunggu sebentar atau refresh halaman jika order tidak muncul.',
                409
            );
        }

        try {
            return DB::transaction(function () use ($request, $validated, $tenantId, $requestIdempotencyKey) {
                $userId = $request->user()->id;

                $existingOrder = Order::where('tenant_id', $tenantId)
                    ->where('idempotency_key', $requestIdempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existingOrder) {
                    return $this->successResponse(
                        new OrderResource($existingOrder->load(['items.product', 'user', 'shift'])),
                        'Order already processed.'
                    );
                }

                // 1. Ambil shift aktif yang sudah divalidasi oleh ShiftMiddleware.
                $shift = $request->attributes->get('current_shift');

                if (! $shift instanceof Shift || $shift->tenant_id !== $tenantId) {
                    return $this->errorResponse('Anda harus membuka Shift terlebih dahulu sebelum melakukan transaksi.', 422);
                }

                // 2. Hitung total via OrderService — single source of truth antara FE & BE
                $orderType = $validated['order_type'] ?? 'regular';
                $totals = $this->orderService->calculateOrderTotals($validated['items'], $orderType);

                $paidAmount = $validated['paid_amount'] ?? 0;
                $changeAmount = $paidAmount - $totals['grand_total'];

                if ($changeAmount < 0 && $validated['payment_method'] === 'cash') {
                    return $this->errorResponse(
                        'Uang pembayaran tidak boleh kurang dari total tagihan (Rp '.number_format($totals['grand_total'], 0, ',', '.').').',
                        422
                    );
                }

                if ($validated['payment_method'] !== 'cash') {
                    $paidAmount = $totals['grand_total'];
                    $changeAmount = 0;
                }

                // 3. Generate nomor order unik (ORD-YYMMDD-XXXX)
                $orderNumber = $this->orderService->generateOrderNumber($tenantId);

                // 4. Buat record order
                $order = Order::create([
                    'tenant_id' => $tenantId,
                    'shift_id' => $shift->id,
                    'user_id' => $userId,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'order_number' => $orderNumber,
                    'idempotency_key' => $requestIdempotencyKey,
                    'customer_name' => $validated['customer_name'] ?? (
                        $validated['customer_id']
                            ? Customer::find($validated['customer_id'])?->name
                            : ($validated['order_type'] ?? 'Regular')
                    ),
                    'table_number' => $validated['table_number'] ?? null,
                    'subtotal' => $totals['subtotal'] + $totals['discount_total'],
                    'discount_amount' => $totals['discount_total'],
                    'service_charge' => $totals['service_total'],
                    'tax_amount' => $totals['tax_total'],
                    'rounding' => $totals['rounding'],
                    'total_amount' => $totals['grand_total'],
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 5. Simpan item order (bulk insert)
                $order->items()->createMany($totals['items']);

                // 6. Kurangi stok & catat transaksi finansial
                $this->inventoryService->adjustStockFromOrder($order, $userId, $validated['account_id']);

                // 7. Update total penjualan shift
                $shift->increment('total_sales', $order->total_amount);

                // 8. Cetak struk (gagal cetak tidak boleh batalkan transaksi)
                try {
                    $this->printerService->printOrder($order);
                    $this->printerService->printKitchenReceipt($order);
                } catch (\Exception $e) {
                    \Log::warning('Printing failed on checkout: '.$e->getMessage());
                }

                $order->load(['items.product', 'user', 'shift']);
                event(new OrderCreated($order));

                return $this->successResponse(new OrderResource($order), 'Order created successfully', 201);
            });
        } catch (QueryException $e) {
            $existingOrder = Order::where('tenant_id', $tenantId)
                ->where('idempotency_key', $requestIdempotencyKey)
                ->with(['items.product', 'user', 'shift'])
                ->first();

            if ($existingOrder) {
                return $this->successResponse(new OrderResource($existingOrder), 'Order already processed.');
            }

            throw $e;
        } finally {
            // Pastikan lock selalu dilepas setelah proses selesai (sukses atau gagal)
            $lock->release();
        }
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
