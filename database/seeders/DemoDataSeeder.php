<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use App\Models\Promotion;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(InventoryService $inventoryService, OrderService $orderService): void
    {
        // 1. Roles & Permissions (Lengkap berdasarkan API Routes & Sidebar)
        $permissions = [
            // Dashboard
            'view-dashboard',
            'view-dashboard-stats',

            // Master Data
            'view-master-data',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            'view-units',
            'create-units',
            'edit-units',
            'delete-units',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',
            'view-promotions',
            'create-promotions',
            'edit-promotions',
            'delete-promotions',

            // Inventory
            'view-inventory',
            'view-stocks',
            'view-stock-movements',
            'view-stock-adjustments',
            'process-inventory-adjustments',
            'view-inventory-recommendations',
            'view-inventory-alerts',

            // Sales (POS)
            'view-sales',
            'manage-shifts',
            'view-shifts',
            'view-orders',
            'create-orders',
            'view-order-receipt',
            'view-pos',
            'view-sales-returns',

            // Purchasing
            'view-purchasing',
            'view-purchases',
            'create-purchases',
            'view-purchase-pdf',
            'view-purchase-returns',
            'view-procurement',

            // Finance
            'view-finance',
            'manage-accounts',
            'view-accounts',
            'view-finance-transactions',
            'create-finance-transactions',
            'view-transactions',
            'view-finance-categories',
            'view-closings',

            // Returns & Reports
            'process-returns',
            'view-reports',
            'view-report-sales',
            'view-report-purchases',
            'view-report-returns',
            'view-report-tax',

            // Daily Closing
            'manage-daily-closings',

            // Settings & Users
            'manage-tenant-settings',
            'view-business-profile',
            'view-profile',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-users',
            'manage-roles-permissions',

            // Others
            'export-reports',
            'view-audit-logs',
            'sync-dpkad'
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Role: Admin (Full Permissions)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // Role: Manager (Sebagian besar kecuali User Management & Tenant Settings)
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = array_filter($permissions, function ($p) {
            return !Str::contains($p, ['users', 'roles', 'tenant-settings', 'delete']);
        });
        $managerRole->syncPermissions($managerPermissions);

        // Role: Cashier (Hanya POS & Dashboard)
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $cashierPermissions = [
            'view-dashboard-stats',
            'manage-shifts',
            'create-orders',
            'view-orders',
            'view-order-receipt',
            'view-products',
            'view-customers',
            'create-customers'
        ];
        $cashierRole->syncPermissions($cashierPermissions);

        // 2. Tenant
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'restoku-demo'],
            ['name' => 'Restoku Demo']
        );

        // 3. Users
        $password = Hash::make('password');
        $admin = User::updateOrCreate(
            ['email' => 'admin@restoku.id'],
            ['tenant_id' => $tenant->id, 'name' => 'Admin Restoku', 'password' => $password, 'role' => 'admin']
        );
        $admin->assignRole('admin');

        $manager = User::updateOrCreate(
            ['email' => 'manager@restoku.id'],
            ['tenant_id' => $tenant->id, 'name' => 'Manager Restoku', 'password' => $password, 'role' => 'manager']
        );
        $manager->assignRole('manager');

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@restoku.id'],
            ['tenant_id' => $tenant->id, 'name' => 'Cashier Restoku', 'password' => $password, 'role' => 'cashier']
        );
        $cashier->assignRole('cashier');


        // 4. Master Data
        $units = ['Pcs', 'Box', 'Kg', 'Gram', 'Liter', 'Pack', 'Botol', 'Porsi'];
        foreach ($units as $u) {
            Unit::updateOrCreate(['tenant_id' => $tenant->id, 'name' => $u], ['short_name' => strtolower($u)]);
        }
        $unitPcs = Unit::where('tenant_id', $tenant->id)->where('name', 'Pcs')->first();

        $categories = ['Makanan', 'Minuman', 'Snack', 'Rokok', ' Beverages'];
        foreach ($categories as $cat) {
            Category::updateOrCreate(['tenant_id' => $tenant->id, 'name' => $cat], ['slug' => Str::slug($cat)]);
        }
        $catFood = Category::where('tenant_id', $tenant->id)->where('name', 'Makanan')->first();

        $supplier = Supplier::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'supplier@example.com'],
            ['name' => 'Supplier Utama', 'contact_person' => 'Budi', 'phone' => '08123456789', 'is_active' => true]
        );

        $customer = Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0899999999'],
            ['name' => 'Pelanggan Setia', 'email' => 'customer@example.com']
        );

        // 5. Products & Initial Stock
        $categories = Category::where('tenant_id', $tenant->id)->get()->keyBy('name');

        $productsData = [
            // Makanan
            [
                'name' => 'Nasi Goreng Special',
                'short_name' => 'Nasi Goreng',
                'price' => 25000,
                'cost' => 15000,
                'code' => '10000001',
                'category' => 'Makanan',
                'barcode' => '8991234567001',
                'brand' => 'Restoku Kitchen',
                'description' => 'Nasi goreng dengan telur, sayuran segar, dan bumbu pilihan. Hidangan favorit untuk sarapan maupun makan siang.',
                'ojol_price' => 27000,
                'wholesale_price' => 20000,
                'ojol_discount' => 2000,
                'wholesale_discount' => 5000,
                'image' => 'products/nasi-goreng-special.jpg'
            ],
            [
                'name' => 'Mie Goreng Seafood',
                'short_name' => 'Mie Goreng',
                'price' => 30000,
                'cost' => 18000,
                'code' => '10000002',
                'category' => 'Makanan',
                'barcode' => '8991234567002',
                'brand' => 'Restoku Kitchen',
                'description' => 'Mie goreng premium dengan udang, cumi, dan ikan. Cita rasa laut yang autentik dan lezat.',
                'ojol_price' => 32000,
                'wholesale_price' => 24000,
                'ojol_discount' => 2000,
                'wholesale_discount' => 6000,
                'image' => 'products/mie-goreng-seafood.jpg'
            ],
            [
                'name' => 'Ayam Bakar Madu',
                'short_name' => 'Ayam Bakar',
                'price' => 35000,
                'cost' => 20000,
                'code' => '10000003',
                'category' => 'Makanan',
                'barcode' => '8991234567003',
                'brand' => 'Restoku Kitchen',
                'description' => 'Ayam bakar dengan lapisan madu alami, empuk dan berempah nikmat. Sempurna dengan nasi putih hangat.',
                'ojol_price' => 37000,
                'wholesale_price' => 28000,
                'ojol_discount' => 2000,
                'wholesale_discount' => 7000,
                'image' => 'products/ayam-bakar-madu.jpg'
            ],
            [
                'name' => 'Soto Ayam',
                'short_name' => 'Soto Ayam',
                'price' => 20000,
                'cost' => 12000,
                'code' => '10000004',
                'category' => 'Makanan',
                'barcode' => '8991234567004',
                'brand' => 'Restoku Kitchen',
                'description' => 'Soto ayam tradisional dengan rempah pilihan dan kuning alami. Hangat dan menyehatkan untuk keluarga.',
                'ojol_price' => 22000,
                'wholesale_price' => 16000,
                'ojol_discount' => 2000,
                'wholesale_discount' => 4000,
                'image' => 'products/soto-ayam.jpg'
            ],
            [
                'name' => 'Rendang Daging',
                'short_name' => 'Rendang',
                'price' => 40000,
                'cost' => 25000,
                'code' => '10000005',
                'category' => 'Makanan',
                'barcode' => '8991234567005',
                'brand' => 'Restoku Kitchen',
                'description' => 'Rendang daging Padang asli dengan santan dan rempah. Kaya rasa dan tekstur yang sempurna.',
                'ojol_price' => 42000,
                'wholesale_price' => 32000,
                'ojol_discount' => 2000,
                'wholesale_discount' => 8000,
                'image' => 'products/rendang-daging.jpg'
            ],

            // Minuman
            [
                'name' => 'Es Teh Manis',
                'short_name' => 'Es Teh',
                'price' => 5000,
                'cost' => 1500,
                'code' => '20000001',
                'category' => 'Minuman',
                'barcode' => '8991234568001',
                'brand' => 'Restoku Drinks',
                'description' => 'Es teh segar dengan pemanis alami dan es batu. Minuman favorit di hari yang panas.',
                'ojol_price' => 6000,
                'wholesale_price' => 4000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/es-teh-manis.jpg'
            ],
            [
                'name' => 'Jus Jeruk',
                'short_name' => 'Jus Jeruk',
                'price' => 10000,
                'cost' => 4000,
                'code' => '20000002',
                'category' => 'Minuman',
                'barcode' => '8991234568002',
                'brand' => 'Restoku Drinks',
                'description' => 'Jus jeruk segar dari buah pilihan. Kaya vitamin C dan menyegarkan untuk tubuh.',
                'ojol_price' => 11000,
                'wholesale_price' => 8000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 2000,
                'image' => 'products/jus-jeruk.jpg'
            ],
            [
                'name' => 'Kopi Hitam',
                'short_name' => 'Kopi',
                'price' => 8000,
                'cost' => 3000,
                'code' => '20000003',
                'category' => 'Minuman',
                'barcode' => '8991234568003',
                'brand' => 'Restoku Drinks',
                'description' => 'Kopi hitam murni dari biji pilihan. Aroma kuat dan rasa nikmat untuk menemani aktivitas.',
                'ojol_price' => 9000,
                'wholesale_price' => 6500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/kopi-hitam.jpg'
            ],
            [
                'name' => 'Es Campur',
                'short_name' => 'Es Campur',
                'price' => 15000,
                'cost' => 6000,
                'code' => '20000004',
                'category' => 'Minuman',
                'barcode' => '8991234568004',
                'brand' => 'Restoku Drinks',
                'description' => 'Es campur tradisional dengan beragam isian. Segar dan menyenangkan di setiap tegukan.',
                'ojol_price' => 16000,
                'wholesale_price' => 12000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 3000,
                'image' => 'products/es-campur.jpg'
            ],

            // Snack
            [
                'name' => 'Keripik Kentang',
                'short_name' => 'Keripik',
                'price' => 12000,
                'cost' => 5000,
                'code' => '30000001',
                'category' => 'Snack',
                'barcode' => '8991234569001',
                'brand' => 'Restoku Snacks',
                'description' => 'Keripik kentang renyah dengan rasa yang lezat. Camilan sempurna untuk acara atau santai.',
                'ojol_price' => 13000,
                'wholesale_price' => 9500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 2500,
                'image' => 'products/keripik-kentang.jpg'
            ],
            [
                'name' => 'Coklat Batang',
                'short_name' => 'Coklat',
                'price' => 8000,
                'cost' => 3000,
                'code' => '30000002',
                'category' => 'Snack',
                'barcode' => '8991234569002',
                'brand' => 'Restoku Snacks',
                'description' => 'Coklat batang premium dengan cokelat berkualitas tinggi. Manis dan lembut di mulut.',
                'ojol_price' => 9000,
                'wholesale_price' => 6000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 2000,
                'image' => 'products/coklat-batang.jpg'
            ],
            [
                'name' => 'Biskuit Marie',
                'short_name' => 'Biskuit',
                'price' => 6000,
                'cost' => 2500,
                'code' => '30000003',
                'category' => 'Snack',
                'barcode' => '8991234569003',
                'brand' => 'Restoku Snacks',
                'description' => 'Biskuit Marie renyah dan gurih. Camilan sehat untuk keluarga sepanjang hari.',
                'ojol_price' => 7000,
                'wholesale_price' => 4500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/biskuit-marie.jpg'
            ],

            // Rokok
            [
                'name' => 'Sampoerna Mild',
                'short_name' => 'Sampoerna',
                'price' => 25000,
                'cost' => 20000,
                'code' => '40000001',
                'category' => 'Rokok',
                'barcode' => '8991234570001',
                'brand' => 'Sampoerna',
                'description' => 'Rokok mild premium dengan rasa yang halus. Kemasan standar isi 12 batang.',
                'ojol_price' => 26000,
                'wholesale_price' => 22000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 3000,
                'image' => 'products/sampoerna-mild.jpg'
            ],
            [
                'name' => 'Gudang Garam',
                'short_name' => 'Gudang Garam',
                'price' => 22000,
                'cost' => 18000,
                'code' => '40000002',
                'category' => 'Rokok',
                'barcode' => '8991234570002',
                'brand' => 'Gudang Garam',
                'description' => 'Rokok Gudang Garam dengan cita rasa yang khas. Pilihan favorit konsumen Indonesia.',
                'ojol_price' => 23000,
                'wholesale_price' => 20000,
                'ojol_discount' => 1000,
                'wholesale_discount' => 2000,
                'image' => 'products/gudang-garam.jpg'
            ],

            // Beverages
            [
                'name' => 'Coca Cola',
                'short_name' => 'Coca Cola',
                'price' => 7000,
                'cost' => 3000,
                'code' => '50000001',
                'category' => ' Beverages',
                'barcode' => '8991234571001',
                'brand' => 'Coca Cola',
                'description' => 'Coca Cola original dengan cita rasa refreshing. Minuman favorit keluarga di seluruh dunia.',
                'ojol_price' => 8000,
                'wholesale_price' => 5500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/coca-cola.jpg'
            ],
            [
                'name' => 'Sprite',
                'short_name' => 'Sprite',
                'price' => 7000,
                'cost' => 3000,
                'code' => '50000002',
                'category' => ' Beverages',
                'barcode' => '8991234571002',
                'brand' => 'Sprite',
                'description' => 'Sprite dengan rasa lemon yang segar. Minuman yang pas untuk melepas dahaga.',
                'ojol_price' => 8000,
                'wholesale_price' => 5500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/sprite.jpg'
            ],
            [
                'name' => 'Fanta',
                'short_name' => 'Fanta',
                'price' => 7000,
                'cost' => 3000,
                'code' => '50000003',
                'category' => ' Beverages',
                'barcode' => '8991234571003',
                'brand' => 'Fanta',
                'description' => 'Fanta dengan berbagai pilihan rasa buah. Minuman yang segar dan menyenangkan.',
                'ojol_price' => 8000,
                'wholesale_price' => 5500,
                'ojol_discount' => 1000,
                'wholesale_discount' => 1500,
                'image' => 'products/fanta.jpg'
            ],
        ];

        $products = [];
        foreach ($productsData as $p) {
            $category = $categories[$p['category']] ?? $catFood;
            $product = Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $p['code']],
                [
                    'category_id' => $category->id,
                    'unit_id' => $unitPcs->id,
                    'supplier_id' => $supplier->id,
                    'name' => $p['name'],
                    'short_name' => $p['short_name'],
                    'slug' => Str::slug($p['name']),
                    'barcode' => $p['barcode'],
                    'description' => $p['description'],
                    'price' => $p['price'],
                    'cost_price' => $p['cost'],
                    'ojol_price' => $p['ojol_price'],
                    'ojol_discount' => $p['ojol_discount'],
                    'wholesale_price' => $p['wholesale_price'],
                    'wholesale_discount' => $p['wholesale_discount'],
                    'brand_name' => $p['brand'],
                    'image' => $p['image'],
                    'stock_type' => 'trackable',
                    'is_active' => true,
                    'minimum_stock' => 10,
                    'maximum_stock' => 200,
                    'reorder_quantity' => 50,
                    'safety_stock' => 20,
                    'tax_rate' => 10,
                    'service_charge_rate' => 0,
                ]
            );
            $products[] = $product;

            // Initial stock
            $inventoryService->updateStock($tenant->id, $product->id, rand(50, 150), 'initial', $admin->id);
        }

        // 6. Finance
        $account = Account::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Kas Toko'],
            ['account_number' => '101', 'balance' => 1000000, 'is_active' => true]
        );

        $expCat = ExpenseCategory::updateOrCreate(['tenant_id' => $tenant->id, 'name' => 'Operasional']);

        // 7. Promotions
        Promotion::updateOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Promo Grand Opening'],
            [
                'content' => 'Diskon 10% untuk semua menu!',
                'type' => 'discount_percentage',
                'discount_value' => 10,
                'applicable_type' => 'all',
                'is_active' => true,
                'priority' => 1
            ]
        );

        $promoFood = Promotion::updateOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Nasi Goreng Hemat'],
            [
                'content' => 'Potongan 5rb khusus Nasi Goreng!',
                'type' => 'discount_fixed',
                'discount_value' => 5000,
                'applicable_type' => 'products',
                'is_active' => true,
                'priority' => 2
            ]
        );
        $promoFood->products()->sync([$products[0]->id]);

        $promoDrink = Promotion::updateOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Minuman Segar'],
            [
                'content' => 'Diskon 15% untuk semua kategori Minuman!',
                'type' => 'discount_percentage',
                'discount_value' => 15,
                'applicable_type' => 'categories',
                'is_active' => true,
                'priority' => 3
            ]
        );
        $promoDrink->categories()->sync([$categories['Minuman']->id]);

        // 8. Transactions Simulation
        // A. Open Shift
        $shift = Shift::create([
            'tenant_id' => $tenant->id,
            'user_id' => $cashier->id,
            'status' => 'open',
            'start_time' => now()->subHours(4),
            'starting_cash' => 200000,
        ]);

        // B. Create an Order
        $orderItems = [
            ['product_id' => $products[0]->id, 'quantity' => 2, 'price' => $products[0]->price, 'notes' => 'Pedas'],
            ['product_id' => $products[2]->id, 'quantity' => 2, 'price' => $products[2]->price, 'notes' => 'Dingin'],
        ];

        $totals = $orderService->calculateOrderTotals($orderItems, 'regular');

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'shift_id' => $shift->id,
            'user_id' => $cashier->id,
            'order_number' => $orderService->generateOrderNumber($tenant->id),
            'customer_name' => $customer->name,
            'customer_id' => $customer->id,
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_total'],
            'tax_amount' => $totals['tax_total'],
            'total_amount' => $totals['grand_total'],
            'paid_amount' => $totals['grand_total'],
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $order->items()->createMany($totals['items']);
        $inventoryService->adjustStockFromOrder($order, $cashier->id, $account->id);
        $shift->increment('total_sales', $order->total_amount);

        // C. Create a Purchase
        $purchaseNumber = 'PUR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $purchase = Purchase::create([
            'tenant_id' => $tenant->id,
            'supplier_id' => $supplier->id,
            'user_id' => $admin->id,
            'purchase_number' => $purchaseNumber,
            'purchase_date' => now(),
            'subtotal' => 500000,
            'total_amount' => 500000,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $purchase->items()->create([
            'product_id' => $products[0]->id,
            'product_name' => $products[0]->name,
            'cost_price' => 15000,
            'quantity' => 10,
            'subtotal' => 150000,
        ]);

        $inventoryService->adjustStockFromPurchase($purchase, $admin->id, $account->id);

        $this->command->info('Simulation data with full permissions seeded successfully!');
    }
}
