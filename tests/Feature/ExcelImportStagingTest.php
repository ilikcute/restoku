<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\TempOrder;
use App\Models\TempOrderItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * @property Tenant $tenant
 * @property User $cashier
 * @property Shift $shift
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->tenant = Tenant::factory()->create();
    $this->cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-orders');
    $this->cashier->givePermissionTo('view-orders');
    $this->shift = Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'status' => 'open',
    ]);
});

test('can retrieve empty staging summary when no data is imported', function () {
    /** @var TestCase $this */
    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'data' => [
            'total_orders' => 0,
            'total_amount' => 0,
            'dates' => [],
            'orders' => [],
        ],
    ]);
});

test('can retrieve staging summary with temp orders', function () {
    /** @var TestCase $this */
    // Create staging data
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TEST-001',
        'table_number' => 'Table 5',
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
        'total_amount' => 11500,
        'date' => '2026-05-28',
    ]);

    TempOrderItem::create([
        'temp_order_id' => $tempOrder->id,
        'product_name' => 'Nasi Goreng',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    $response->assertJsonPath('data.total_orders', 1);
    $response->assertJsonPath('data.total_amount', '11500.00');
    $response->assertJsonPath('data.dates.0.date', '2026-05-28');
});

test('can commit staging orders to main orders table', function () {
    /** @var TestCase $this */
    // Create product
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Nasi Goreng',
        'price' => 10000,
    ]);

    $shiftDate = $this->shift->start_time->format('Y-m-d');

    // Create staging data
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TEST-001',
        'table_number' => 'Table 5',
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
        'total_amount' => 11500,
        'date' => $shiftDate,
    ]);

    TempOrderItem::create([
        'temp_order_id' => $tempOrder->id,
        'product_name' => 'Nasi Goreng',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => [$shiftDate],
        ]);

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'data' => [
            'committed_count' => 1,
        ],
    ]);

    // Check database
    $this->assertDatabaseHas('orders', [
        'tenant_id' => $this->tenant->id,
        'order_number' => 'TEST-001',
        'customer_name' => '-',
        'total_amount' => 11500,
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_name' => 'Nasi Goreng',
        'quantity' => 1,
    ]);

    // Check temp orders are cleared
    expect(TempOrder::count())->toBe(0)
        ->and(TempOrderItem::count())->toBe(0);
});

test('cannot commit staging orders when temp order date does not match active shift date', function () {
    /** @var TestCase $this */
    // Staging date is 2026-10-02 while active shift is today
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'MISMATCH-001',
        'table_number' => '1',
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
        'total_amount' => 11500,
        'date' => '2026-10-02',
    ]);

    TempOrderItem::create([
        'temp_order_id' => $tempOrder->id,
        'product_name' => 'Nasi Goreng',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-10-02'],
        ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['status' => 'error']);
    expect($response->json('message'))->toContain('tidak sama dengan tanggal shift aktif');
});

test('can delete mismatched staging orders via endpoint', function () {
    /** @var TestCase $this */
    $shiftDate = $this->shift->start_time->format('Y-m-d');

    // Matching order
    $matchingOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'MATCH-001',
        'table_number' => '1',
        'subtotal' => 10000,
        'total_amount' => 10000,
        'date' => $shiftDate,
    ]);
    TempOrderItem::create([
        'temp_order_id' => $matchingOrder->id,
        'product_name' => 'Kopi',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
    ]);

    // Mismatched order (different date)
    $mismatchedOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'MISMATCH-DELETE',
        'table_number' => '1',
        'subtotal' => 20000,
        'total_amount' => 20000,
        'date' => '2026-10-02',
    ]);
    TempOrderItem::create([
        'temp_order_id' => $mismatchedOrder->id,
        'product_name' => 'Teh',
        'price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->deleteJson('/api/v1/orders/import/mismatched-records');

    $response->assertSuccessful();
    $response->assertJsonPath('data.deleted_count', 1);

    // Matching order must still exist, mismatched must be gone
    expect(TempOrder::where('order_number', 'MATCH-001')->exists())->toBeTrue()
        ->and(TempOrder::where('order_number', 'MISMATCH-DELETE')->exists())->toBeFalse();
});

test('cannot commit staging orders without open shift', function () {
    /** @var TestCase $this */
    $this->shift->update(['status' => 'closed']);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-05-28'],
        ]);

    $response->assertStatus(400);
});

test('staging summary automatically purges empty or 0-amount orders', function () {
    /** @var TestCase $this */
    // Order 1: valid
    $validOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'VALID-001',
        'table_number' => '1',
        'subtotal' => 20000,
        'total_amount' => 20000,
        'date' => '2026-05-28',
    ]);
    TempOrderItem::create([
        'temp_order_id' => $validOrder->id,
        'product_name' => 'Kopi',
        'price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
    ]);

    // Order 2: 0 amount & without items
    TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'EMPTY-001',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => null,
    ]);

    // Order 3: has items but 0 amount
    $zeroOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'ZERO-001',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => '2026-05-28',
    ]);
    TempOrderItem::create([
        'temp_order_id' => $zeroOrder->id,
        'product_name' => 'Air',
        'price' => 0,
        'quantity' => 1,
        'subtotal' => 0,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    // Only 1 valid order should remain
    $response->assertJsonPath('data.total_orders', 1);
    $response->assertJsonPath('data.total_amount', '20000.00');

    expect(TempOrder::where('tenant_id', $this->tenant->id)->count())->toBe(1)
        ->and(TempOrder::where('order_number', 'EMPTY-001')->exists())->toBeFalse()
        ->and(TempOrder::where('order_number', 'ZERO-001')->exists())->toBeFalse();
});

test('cannot commit empty or 0-amount orders to main database', function () {
    /** @var TestCase $this */
    // Create zero amount temp order
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'ZERO-COMMIT',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => '2026-05-28',
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-05-28'],
        ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.committed_count', 0);

    // Assert main orders table has no 0-amount orders
    expect(Order::where('order_number', 'ZERO-COMMIT')->exists())->toBeFalse()
        ->and(TempOrder::where('order_number', 'ZERO-COMMIT')->exists())->toBeFalse();
});

test('can clear staging records via endpoint', function () {
    /** @var TestCase $this */
    TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TO-DELETE',
        'table_number' => '1',
        'subtotal' => 10000,
        'total_amount' => 10000,
        'date' => '2026-05-28',
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->deleteJson('/api/v1/orders/import/clear');

    $response->assertSuccessful();
    expect(TempOrder::where('tenant_id', $this->tenant->id)->count())->toBe(0);
});

test('cannot upload excel when no open shift exists', function () {
    /** @var TestCase $this */
    $this->shift->update(['status' => 'closed']);

    $excelPath = createSampleExcelFile(now()->format('d/m/Y'));
    $file = new UploadedFile($excelPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import', ['file' => $file]);

    @unlink($excelPath);

    $response->assertStatus(400);
    $response->assertJsonFragment(['status' => 'error']);
});

test('cannot upload excel when open shift is not for today', function () {
    /** @var TestCase $this */
    $this->shift->update(['start_time' => now()->subDays(2)]);

    $excelPath = createSampleExcelFile(now()->format('d/m/Y'));
    $file = new UploadedFile($excelPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import', ['file' => $file]);

    @unlink($excelPath);

    $response->assertStatus(400);
    $response->assertJsonFragment(['status' => 'error']);
});

test('cannot upload excel when transaction date in file does not match active shift date', function () {
    /** @var TestCase $this */
    // Active shift is today, but file has only past dates
    $excelPath = createSampleExcelFile('01/01/2025');
    $file = new UploadedFile($excelPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import', ['file' => $file]);

    @unlink($excelPath);

    $response->assertStatus(400);
    $response->assertJsonFragment(['status' => 'error']);
    expect($response->json('message'))->toContain('Tidak ditemukan transaksi untuk tanggal shift aktif');
});

test('can upload multi-date excel and only transactions matching active shift date are imported', function () {
    /** @var TestCase $this */
    $todayDate = now()->format('d/m/Y');
    $excelPath = createMultiDateSampleExcelFile([
        ['order_number' => 'PAST-001', 'date' => '01/01/2025'],
        ['order_number' => 'TODAY-001', 'date' => $todayDate],
        ['order_number' => 'FUTURE-001', 'date' => '31/12/2028'],
    ]);
    $file = new UploadedFile($excelPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import', ['file' => $file]);

    @unlink($excelPath);

    $response->assertSuccessful();
    $response->assertJsonPath('data.imported_count', 1);

    // Assert only TODAY-001 exists in temp_orders, PAST and FUTURE are ignored/deleted
    expect(TempOrder::where('tenant_id', $this->tenant->id)->count())->toBe(1)
        ->and(TempOrder::where('order_number', 'TODAY-001')->exists())->toBeTrue()
        ->and(TempOrder::where('order_number', 'PAST-001')->exists())->toBeFalse()
        ->and(TempOrder::where('order_number', 'FUTURE-001')->exists())->toBeFalse();
});

test('can upload excel and temp_orders date is verified and matched to active shift date', function () {
    /** @var TestCase $this */
    $todayDateFormatted = now()->format('d/m/Y');
    $excelPath = createSampleExcelFile($todayDateFormatted, 'VALID-999');
    $file = new UploadedFile($excelPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import', ['file' => $file]);

    @unlink($excelPath);

    $response->assertSuccessful();

    $todayYmd = now()->format('Y-m-d');
    $tempOrder = TempOrder::where('tenant_id', $this->tenant->id)
        ->where('order_number', 'VALID-999')
        ->first();

    expect($tempOrder)->not->toBeNull()
        ->and($tempOrder->date->format('Y-m-d'))->toBe($todayYmd);
});

test('staging summary returns active shift information', function () {
    /** @var TestCase $this */
    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    $response->assertJsonPath('data.active_shift.id', $this->shift->id);
    $response->assertJsonPath('data.active_shift.is_today', true);
});

function createMultiDateSampleExcelFile(array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Input');

    $rowIndex = 5;
    foreach ($rows as $item) {
        $sheet->setCellValue('H'.$rowIndex, $item['order_number']);
        $sheet->setCellValue('D'.$rowIndex, $item['date']);
        $sheet->setCellValue('L'.$rowIndex, '1');
        $sheet->setCellValue('X'.$rowIndex, 'Nasi Goreng');
        $sheet->setCellValue('Y'.$rowIndex, '1');
        $sheet->setCellValue('Z'.$rowIndex, '25000');
        $rowIndex++;
    }

    $writer = new Xlsx($spreadsheet);
    $filePath = tempnam(sys_get_temp_dir(), 'test_multidate_excel_').'.xlsx';
    $writer->save($filePath);

    return $filePath;
}

function createSampleExcelFile(string $date, string $orderNumber = 'TEST-001'): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Input');

    $sheet->setCellValue('H5', $orderNumber);
    $sheet->setCellValue('D5', $date);
    $sheet->setCellValue('L5', '1');
    $sheet->setCellValue('X5', 'Nasi Goreng');
    $sheet->setCellValue('Y5', '1');
    $sheet->setCellValue('Z5', '25000');

    $writer = new Xlsx($spreadsheet);
    $filePath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
    $writer->save($filePath);

    return $filePath;
}
