<template>
    <div class="fixed inset-0 z-[100] bg-gray-50 flex flex-col h-screen overflow-hidden text-gray-800">
        <!-- Header -->
        <header
            class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200 shadow-sm shrink-0">
            <div class="flex items-center gap-4">
                <Button icon="pi pi-arrow-left" text rounded aria-label="Back to Dashboard"
                    @click="$router.push('/')" />
                <div class="flex items-center gap-3">
                    <img v-if="tenant.logo_url" :src="tenant.logo_url" class="h-8 w-8 object-contain rounded" />
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                        {{ tenant.name || "Restoku POS" }}
                    </h1>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-900">
                        {{ currentTime }}
                    </div>
                    <div class="text-[10px] text-slate-500 uppercase font-medium tracking-tighter">
                        {{ currentDate }}
                    </div>
                </div>
                <div class="flex items-center gap-2 mr-2">
                    <!-- Connection Status -->
                    <Tag v-if="!isOnline" value="OFFLINE" severity="danger" icon="pi pi-wifi" class="text-[10px]" />

                    <!-- Sync Status -->
                    <Button v-if="pendingSyncCount > 0" :label="isSyncing
                        ? 'Syncing...'
                        : `Pending: ${pendingSyncCount}`
                        " :icon="isSyncing
                            ? 'pi pi-spin pi-refresh'
                            : 'pi pi-cloud-upload'
                            " severity="warn" size="small" text @click="syncOfflineOrders"
                        :disabled="!isOnline || isSyncing" />
                </div>
                <Button icon="pi pi-external-link" label="Layar Pelanggan" severity="secondary" size="small" outlined
                    @click="openCustomerDisplay" />
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Left Pane: Products -->
            <div class="flex flex-col flex-1 min-w-0 p-4 bg-gray-50/50">
                <!-- Search Bar -->
                <div class="mb-2 shrink-0">
                    <InputText v-model="query" class="w-full" :placeholder="$t('pos.search_item')" />
                </div>

                <!-- Category Tabs — horizontal scroll, fits roughly 4.5 items to show it's scrollable -->
                <div class="shrink-0 mb-4 max-w-full overflow-hidden">
                    <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar scroll-smooth">
                        <button
                            class="shrink-0 basis-[calc(22%)] min-w-[110px] px-2 py-2.5 rounded-xl text-xs font-bold border transition-all duration-200 whitespace-nowrap shadow-sm uppercase tracking-wider"
                            :class="selectedCategoryId === null
                                ? 'bg-primary-600 text-white border-primary-600 shadow-primary-200'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-primary-400 hover:text-primary-600'
                                " @click="selectedCategoryId = null">
                            {{ $t("pos.all_categories") }}
                        </button>
                        <button v-for="cat in categories" :key="cat.id"
                            class="shrink-0 basis-[calc(22%)] min-w-[110px] px-2 py-2.5 rounded-xl text-xs font-bold border transition-all duration-200 whitespace-nowrap shadow-sm uppercase tracking-wider"
                            :class="selectedCategoryId === cat.id
                                ? 'bg-primary-600 text-white border-primary-600 shadow-primary-200'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-primary-400 hover:text-primary-600'
                                " @click="selectedCategoryId = cat.id">
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 overflow-y-auto no-scrollbar">
                    <!-- Loading Skeleton -->
                    <div v-if="loadingProducts"
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 p-1">
                        <div v-for="n in 10" :key="n"
                            class="bg-white border border-gray-100 rounded-2xl overflow-hidden animate-pulse">
                            <div class="aspect-square bg-gray-200"></div>
                            <div class="p-3 space-y-2">
                                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 p-1">
                        <div v-if="!products.length"
                            class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                            <i class="pi pi-search text-5xl mb-3 opacity-40"></i>
                            <p>{{ $t("pos.empty_cart") }}</p>
                        </div>
                        <div v-for="product in products" :key="product.id"
                            class="relative flex flex-col overflow-hidden transition-shadow bg-white border border-gray-200 rounded-2xl hover:shadow-md cursor-pointer group"
                            @click="addToCart(product)">
                            <div class="aspect-square bg-gray-100 flex items-center justify-center p-4">
                                <img v-if="product.image_url" :src="product.image_url"
                                    class="object-cover w-full h-full rounded-xl" />
                                <i v-else class="text-4xl text-gray-300 pi pi-image"></i>
                                <div
                                    class="absolute top-2 right-2 bg-primary-500 text-white rounded-full w-8 h-8 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="pi pi-plus text-sm"></i>
                                </div>
                            </div>
                            <div class="p-3 pt-2">
                                <div class="text-[10px] text-gray-400 font-mono truncate" v-if="product.code">
                                    {{ product.code }}
                                </div>
                                <div class="text-sm font-semibold truncate">
                                    {{ product.name }}
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-primary-600 font-bold">Rp {{ money(product.price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalProducts > perPage"
                        class="flex items-center justify-center gap-2 py-4 border-t border-gray-100 mt-2">
                        <Button icon="pi pi-chevron-left" text rounded severity="secondary" :disabled="currentPage <= 1"
                            @click="changePage(currentPage - 1)" />
                        <span class="text-sm text-gray-600 px-2">{{ currentPage }} / {{ totalPages }}</span>
                        <Button icon="pi pi-chevron-right" text rounded severity="secondary"
                            :disabled="currentPage >= totalPages" @click="changePage(currentPage + 1)" />
                    </div>
                </div>
            </div>

            <!-- Right Pane: Cart Sidebar -->
            <div
                class="flex flex-col w-96 bg-white border-l border-gray-200 shrink-0 shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-10">
                <!-- Cart Header -->
                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-bold">
                        {{ $t("pos.cart_items") }}
                    </h2>
                    <div class="flex gap-2">
                        <Button icon="pi pi-qrcode" severity="info" rounded text @click="promptPendingOrder"
                            v-tooltip="'Load Self-Order QR'" />
                        <span class="text-xs font-semibold px-2 py-1 bg-primary-100 text-primary-700 rounded-full">{{
                            cart.length }} items</span>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 p-2 overflow-y-auto">
                    <div v-if="!cart.length" class="flex flex-col items-center justify-center h-full text-gray-400">
                        <i class="pi pi-shopping-cart text-5xl mb-4 opacity-50"></i>
                        <p>{{ $t("pos.empty_cart") }}</p>
                    </div>

                    <div v-for="item in cart" :key="item.id"
                        class="flex items-center gap-3 p-2 mb-2 bg-white border border-gray-100 rounded-xl shadow-sm">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">
                                {{ item.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Rp {{ money(itemPrice(item)) }}
                            </div>
                            <div v-if="item.notes" class="text-[10px] text-orange-600 font-medium italic mt-0.5">
                                Note: {{ item.notes }}
                            </div>
                            <button @click="editItemNote(item)" class="text-[9px] text-primary-600 underline">
                                Add Note
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-minus" severity="secondary" rounded outlined size="small"
                                class="w-6 h-6 p-0 flex items-center justify-center" @click="decreaseQty(item)" />
                            <span class="w-6 text-center text-sm font-semibold">{{ item.qty }}</span>
                            <Button icon="pi pi-plus" severity="secondary" rounded outlined size="small"
                                class="w-6 h-6 p-0 flex items-center justify-center" @click="addToCart(item)" />
                        </div>
                        <div class="font-bold text-sm text-right w-20 text-primary-600">
                            Rp {{ money(item.qty * itemPrice(item)) }}
                        </div>
                    </div>
                </div>

                <!-- Cart Footer / Summary -->
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">{{
                                $t("pos.order_type")
                            }}</span>
                            <Select v-model="orderType" :options="orderTypes" optionLabel="label" optionValue="value"
                                class="w-36 text-sm" />
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-1 text-gray-500 text-sm">
                        <span>{{ $t("pos.subtotal") }}</span>
                        <span>Rp {{ money(cartSubtotal) }}</span>
                    </div>
                    <div class="flex justify-between items-end mb-4">
                        <span class="text-gray-600 font-medium">{{
                            $t("pos.total")
                        }}</span>
                        <span class="text-2xl font-bold text-primary-600">Rp {{ money(cartTotal) }}</span>
                    </div>
                    <Button :label="$t('pos.proceed')" icon="pi pi-arrow-right" class="w-full h-12 text-lg font-bold"
                        :disabled="!cart.length" @click="showPayment = true" />
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <Dialog v-model:visible="showPayment" :header="$t('checkout.process_payment')" modal
            class="w-[900px] overflow-hidden" :pt="{ content: { class: 'p-0 overflow-hidden' } }">
            <div class="flex h-[500px] max-h-[90vh] overflow-hidden">
                <!-- Left Side: Summary & Meta (40%) -->
                <div class="w-[38%] bg-slate-50 p-4 border-r border-gray-100 flex flex-col gap-3 overflow-hidden">
                    <div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                            Ringkasan & Detail
                        </h3>
                        <div class="space-y-2.5">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>{{ $t("pos.subtotal") }} (Gross)</span>
                                <span class="font-medium">Rp {{ money(cartGrossSubtotal) }}</span>
                            </div>
                            <div v-if="cartPromotionDiscount > 0" class="flex justify-between text-sm text-red-500">
                                <span>Total Diskon</span>
                                <span class="font-medium">-Rp {{ money(cartPromotionDiscount) }}</span>
                            </div>
                            <div v-if="cartServiceCharge > 0" class="flex justify-between text-sm text-slate-600">
                                <span>{{ $t("pos.service_charge") }}</span>
                                <span class="font-medium">Rp {{ money(cartServiceCharge) }}</span>
                            </div>
                            <div v-if="cartTax > 0" class="flex justify-between text-sm text-slate-600">
                                <span>{{ $t("pos.tax") }}</span>
                                <span class="font-medium">Rp {{ money(cartTax) }}</span>
                            </div>
                            <div v-if="cartRounding !== 0"
                                class="flex justify-between text-sm text-slate-600 text-amber-600">
                                <span>{{ $t("pos.rounding") }}</span>
                                <span class="font-medium">Rp {{ money(cartRounding) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Inputs & Customer (Stacked Vertically) -->
                    <div class="space-y-2.5 pt-3 border-t border-slate-200">
                        <!-- Row 1: Table -->
                        <div>
                            <label class="text-[9px] font-bold text-slate-500 uppercase block mb-1">Nomor Meja</label>
                            <InputText v-model="tableNumber" class="w-full h-9 text-sm font-bold border-slate-300"
                                placeholder="Contoh: 01, A1, dll" />
                        </div>

                        <!-- Row 2: Customer -->
                        <div>
                            <label class="text-[9px] font-bold text-slate-500 uppercase block mb-1">{{
                                $t("checkout.customer") }}</label>
                            <div class="flex gap-2">
                                <Select v-model="selectedCustomer" :options="customers" filter optionLabel="name"
                                    :placeholder="$t('checkout.select_customer')
                                        " class="flex-1 h-9 text-xs" showClear />
                                <Button icon="pi pi-plus" severity="secondary" outlined class="h-9 w-9 p-0"
                                    @click="showAddCustomer = true" />
                            </div>
                        </div>

                        <!-- Row 3: Account -->
                        <div>
                            <label class="text-[9px] font-bold text-slate-500 uppercase block mb-1">{{
                                $t("checkout.financial_account") }}</label>
                            <Select v-model="accountId" :options="accounts" optionLabel="name" optionValue="id"
                                class="w-full h-9 text-xs" />
                        </div>
                    </div>

                    <div class="mt-auto bg-white p-4 rounded-2xl border-2 border-primary-500 shadow-sm text-center">
                        <span class="text-[10px] font-bold text-primary-600 uppercase block mb-0.5">Total Tagihan</span>
                        <div class="text-3xl font-black text-primary-700 tracking-tighter">
                            Rp {{ money(cartTotal) }}
                        </div>
                    </div>
                </div>

                <!-- Right Side: Payment Logic (60%) -->
                <div class="flex-1 p-5 flex flex-col overflow-hidden bg-white">
                    <div class="flex-1 space-y-4">
                        <!-- Payment Method -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{
                                $t("checkout.payment_method") }}</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="method in paymentMethods" :key="method.value"
                                    class="h-10 rounded-xl font-bold text-[11px] transition-all duration-200 flex items-center justify-center border-2 px-2"
                                    :class="paymentMethod === method.value
                                        ? 'bg-primary-50 text-primary-700 border-primary-500 shadow-sm'
                                        : 'bg-white text-slate-500 border-slate-100 hover:border-slate-200'
                                        " @click="paymentMethod = method.value">
                                    {{ method.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Cash Input Area -->
                        <div v-if="paymentMethod === 'cash'"
                            class="space-y-3 animate-in fade-in zoom-in-95 duration-200">
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Tunai Diterima
                                    (Cash In)</label>
                                <InputNumber v-model="paidAmount" class="w-full h-10" mode="decimal" :min="0" autofocus
                                    fluid
                                    inputClass="text-2xl font-black text-right text-slate-800 bg-transparent border-none" />
                            </div>

                            <div class="grid grid-cols-4 gap-1.5">
                                <button v-for="denom in quickCashDenominations" :key="denom"
                                    class="py-2 text-[11px] font-black rounded-lg border-2 transition-all duration-150 shadow-sm"
                                    :class="paidAmount === denom
                                        ? 'bg-emerald-500 text-white border-emerald-500'
                                        : 'bg-white text-slate-600 border-slate-100 hover:border-emerald-300'
                                        " @click="paidAmount = denom">
                                    {{ money(denom) }}
                                </button>
                                <button
                                    class="py-2 text-[11px] font-black rounded-lg border-2 border-primary-100 bg-primary-50 text-primary-700 hover:border-primary-400 transition-all duration-150"
                                    @click="paidAmount = cartTotal">
                                    UANG PAS
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Change & Confirm -->
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div v-if="paymentMethod === 'cash'"
                            class="px-4 py-2.5 bg-slate-900 rounded-xl flex justify-between items-center shadow-xl">
                            <span class="text-slate-400 font-bold uppercase text-[9px] tracking-widest">{{
                                $t("checkout.change_due") }}</span>
                            <span class="text-xl font-black" :class="changeAmount >= 0
                                ? 'text-emerald-400'
                                : 'text-red-400'
                                ">
                                Rp {{ money(Math.max(0, changeAmount)) }}
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <Button :label="$t('common.cancel')" severity="secondary" text
                                class="flex-1 h-11 font-bold text-sm" @click="showPayment = false" />
                            <Button :label="$t('checkout.confirm_payment')" icon="pi pi-check"
                                class="flex-[2] h-11 text-base font-black shadow-lg" :loading="saving" :disabled="paymentMethod === 'cash' && changeAmount < 0
                                    " @click="checkout" />
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>

        <!-- Receipt Modal -->
        <Dialog v-model:visible="showReceipt" :header="$t('checkout.success_title')" modal :closable="false"
            class="w-[400px]">
            <div class="text-center pt-4">
                <i class="pi pi-check-circle text-green-500 text-6xl mb-4"></i>
                <h2 class="text-xl font-bold mb-2">
                    {{ $t("checkout.success_title") }}
                </h2>
                <p class="text-gray-500 mb-6">
                    Order {{ lastOrder?.order_number }} has been processed
                    successfully.
                </p>

                <div class="bg-gray-50 p-4 rounded-xl text-left text-sm font-mono mb-6 space-y-1">
                    <div class="flex justify-between">
                        <span>{{ $t("pos.subtotal") }}:</span>
                        <span>Rp {{ money(lastOrder?.subtotal) }}</span>
                    </div>
                    <div v-if="lastOrder?.discount_amount > 0" class="flex justify-between text-red-500">
                        <span>Diskon:</span>
                        <span>-Rp {{ money(lastOrder?.discount_amount) }}</span>
                    </div>
                    <div v-if="lastOrder?.service_charge > 0" class="flex justify-between">
                        <span>{{ $t("pos.service_charge") }}:</span>
                        <span>Rp {{ money(lastOrder?.service_charge) }}</span>
                    </div>
                    <div v-if="lastOrder?.tax_amount > 0" class="flex justify-between">
                        <span>{{ $t("pos.tax") }}:</span>
                        <span>Rp {{ money(lastOrder?.tax_amount) }}</span>
                    </div>
                    <div v-if="lastOrder?.rounding != 0" class="flex justify-between">
                        <span>{{ $t("pos.rounding") }}:</span>
                        <span>Rp {{ money(lastOrder?.rounding) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 mt-2 pt-2 font-bold text-lg">
                        <span>{{ $t("pos.total") }}:</span>
                        <span>Rp {{ money(lastOrder?.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between mt-2 pt-2 border-t border-gray-200">
                        <span>{{ $t("checkout.cash_given") }}:</span>
                        <span>Rp {{ money(lastOrder?.paid_amount) }}</span>
                    </div>
                    <div v-if="lastOrder?.payment_method === 'cash'"
                        class="flex justify-between font-bold text-green-600">
                        <span>{{ $t("checkout.change_due") }}:</span>
                        <span>Rp {{ money(lastOrder?.change_amount) }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <Button :label="$t('checkout.print_receipt')" icon="pi pi-print" @click="printReceipt" />
                    <Button :label="$t('checkout.send_wa')" icon="pi pi-whatsapp" severity="success"
                        @click="sendWhatsApp" />
                    <Button :label="$t('checkout.new_order')" severity="secondary" class="mt-2" @click="resetPOS" />
                </div>
            </div>
        </Dialog>
        <!-- Printable Receipt (Hidden from screen, visible on print) -->
        <div id="printable-receipt"
            class="print-block bg-white text-black p-4 font-mono text-sm max-w-[300px] mx-auto hidden">
            <div class="text-center font-bold text-lg mb-2">RESTOKU</div>
            <div class="text-center text-xs mb-4">
                <div>Receipt: {{ lastOrder?.order_number }}</div>
                <div>
                    {{
                        new Date(lastOrder?.created_at).toLocaleString("id-ID")
                    }}
                </div>
                <div v-if="lastOrder?.customer_name">
                    Customer: {{ lastOrder?.customer_name }}
                </div>
            </div>

            <div class="border-t border-dashed border-black my-2"></div>

            <div v-for="item in lastOrder?.items" :key="item.id" class="mb-2 text-xs">
                <div>{{ item.product_name || item.product?.name }}</div>
                <div class="flex justify-between">
                    <span>{{ item.quantity }} x Rp {{ money(item.price) }}</span>
                    <span>Rp {{ money(item.subtotal) }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-black my-2"></div>

            <div class="text-xs space-y-1">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>Rp {{ money(lastOrder?.subtotal) }}</span>
                </div>
                <div v-if="lastOrder?.discount_amount > 0" class="flex justify-between">
                    <span>Discount:</span>
                    <span>-Rp {{ money(lastOrder?.discount_amount) }}</span>
                </div>
                <div v-if="lastOrder?.service_charge > 0" class="flex justify-between">
                    <span>Service:</span>
                    <span>Rp {{ money(lastOrder?.service_charge) }}</span>
                </div>
                <div v-if="lastOrder?.tax_amount > 0" class="flex justify-between">
                    <span>Tax (PPN):</span>
                    <span>Rp {{ money(lastOrder?.tax_amount) }}</span>
                </div>
                <div v-if="lastOrder?.rounding != 0" class="flex justify-between">
                    <span>Rounding:</span>
                    <span>Rp {{ money(lastOrder?.rounding) }}</span>
                </div>

                <div class="border-t border-dashed border-black my-1"></div>

                <div class="flex justify-between font-bold text-sm">
                    <span>GRAND TOTAL:</span>
                    <span>Rp {{ money(lastOrder?.total_amount) }}</span>
                </div>

                <div class="flex justify-between mt-2">
                    <span>Paid ({{ lastOrder?.payment_method }}):</span>
                    <span>Rp {{ money(lastOrder?.paid_amount) }}</span>
                </div>
                <div v-if="lastOrder?.payment_method === 'cash'" class="flex justify-between font-bold">
                    <span>Change:</span>
                    <span>Rp {{ money(lastOrder?.change_amount) }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-black my-2"></div>
            <div class="text-center text-xs mt-4">
                Thank you for your visit!
            </div>
        </div>

        <!-- Quick Add Customer Dialog -->
        <Dialog v-model:visible="showAddCustomer" :header="`${$t('common.add')} ${$t('checkout.customer')}`" modal
            class="w-[350px]">
            <div class="space-y-4 pt-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">{{
                        $t("common.name")
                    }}</label>
                    <InputText v-model="newCustomer.name" class="w-full"
                        :placeholder="`${$t('common.add')} ${$t('common.name')}`" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Telepon</label>
                    <InputText v-model="newCustomer.phone" class="w-full" placeholder="No. Telepon" />
                </div>
            </div>
            <template #footer>
                <Button :label="$t('common.cancel')" severity="secondary" text @click="showAddCustomer = false" />
                <Button :label="$t('common.save')" icon="pi pi-check" :loading="savingCustomer"
                    @click="quickAddCustomer" />
            </template>
        </Dialog>

        <!-- Shift Notice Dialog -->
        <Dialog v-model:visible="showShiftNotice" header="Shift Belum Dibuka" modal :closable="false" class="w-[400px]">
            <div class="flex flex-col items-center py-4 text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-exclamation-triangle text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">
                    {{
                        activeShift && activeShift.is_expired
                            ? "Shift Kadaluwarsa"
                            : "Shift Dibutuhkan"
                    }}
                </h3>
                <p class="text-gray-600 mb-6" v-if="activeShift && activeShift.is_expired">
                    Shift Anda dari tanggal
                    <strong>{{
                        new Date(activeShift.start_time).toLocaleDateString(
                            "id-ID",
                        )
                    }}</strong>
                    sudah melewati batas waktu. Silakan tutup shift tersebut
                    terlebih dahulu sebelum memulai hari baru.
                </p>
                <p class="text-gray-600 mb-6" v-else>
                    Anda harus membuka Shift terlebih dahulu sebelum dapat
                    melakukan transaksi penjualan di POS.
                </p>
                <div class="flex gap-3 w-full">
                    <Button label="Kembali ke Dashboard" severity="secondary" class="flex-1"
                        @click="$router.push('/')" />
                    <Button :label="activeShift && activeShift.is_expired
                        ? 'Tutup Shift & Kelola'
                        : 'Buka Shift Sekarang'
                        " severity="primary" class="flex-1" @click="$router.push('/sales/shifts')" />
                </div>
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import { useAuthStore } from "@/stores/auth";
import echo from "@/echo";
import { financeApi } from "@/api/finance";
import { productApi, categoryApi, customerApi, promotionApi } from "@/api/master";
import { orderApi, shiftApi } from "@/api/sales";
import {
    db,
    syncProductsToLocal,
    syncCategoriesToLocal,
    saveOrderOffline,
    getPendingOrders,
    markOrderSynced,
} from "@/api/db";
import { v4 as uuidv4 } from "uuid";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";

const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();
const tenant = computed(
    () => authStore.user?.relationships?.tenant?.data?.attributes || {},
);

const products = ref([]);
const categories = ref([]);
const accounts = ref([]);
const customers = ref([]);
const cart = ref([]);
const isOnline = ref(navigator.onLine);
const pendingSyncCount = ref(0);
const isSyncing = ref(false);
const query = ref("");
const selectedCategoryId = ref(null);
const selectedCustomer = ref(null);
const promotions = ref([]);

const orderTypes = computed(() => [
    { label: "Makan Ditempat/Bungkus", value: "regular" },
    { label: "Ojek Online (Ojol)", value: "ojol" },
    { label: "Grosir/Partai", value: "wholesale" },
]);
const orderType = ref("regular");

import { useI18n } from "vue-i18n";
const { t: $t } = useI18n();

const paymentMethods = [
    { label: "Cash", value: "cash" },
    { label: "QRIS", value: "qris" },
    { label: "Transfer", value: "transfer" },
    { label: "Debit/Credit", value: "debit" },
];
const paymentMethod = ref("cash");
const accountId = ref(null);
const customerName = ref("");
const paidAmount = ref(0);

const showPayment = ref(false);
const showReceipt = ref(false);
const idempotencyKey = ref(uuidv4());

// Quick cash denominations — disesuaikan dengan pecahan umum Rupiah
const quickCashDenominations = [
    5000,
    10000,
    20000,
    50000,
    100000,
    50000 * 3,
    100000 * 2,
    100000 * 5,
];
const showAddCustomer = ref(false);
const saving = ref(false);
const savingCustomer = ref(false);
const activeShift = ref(null);
const showShiftNotice = ref(false);
const loading = ref(false);
const lastOrder = ref(null);
const newCustomer = ref({ name: "", phone: "" });
const tableNumber = ref("");

const currentTime = ref("");
const currentDate = ref("");
let timer = null;

// Pagination state
const loadingProducts = ref(false);
const totalProducts = ref(0);
const currentPage = ref(1);
const perPage = 10;
const totalPages = computed(() => Math.ceil(totalProducts.value / perPage));

// Debounce timer for search
let searchTimer = null;

watch(query, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        loadProducts();
    }, 400);
});

watch(selectedCategoryId, () => {
    currentPage.value = 1;
    loadProducts();
});

async function loadProducts() {
    loadingProducts.value = true;
    try {
        if (isOnline.value) {
            const params = {
                page: currentPage.value,
                per_page: perPage,
                is_active: 1,
            };
            if (query.value) params.q = query.value;
            if (selectedCategoryId.value)
                params.category_id = selectedCategoryId.value;

            const res = await productApi.getAll(params);
            const result = res?.data?.data || {};
            products.value = result.data || [];
            totalProducts.value = result.meta?.total || 0;

            // Background: Ambil semua produk untuk cache offline (hanya sesekali atau di bootstrap)
        } else {
            // Offline: Cari di IndexedDB
            let collection = db.products.where("is_active").equals(1);

            if (selectedCategoryId.value) {
                collection = collection.and(
                    (p) => p.category_id === selectedCategoryId.value,
                );
            }

            if (query.value) {
                const q = query.value.toLowerCase();
                collection = collection.and(
                    (p) =>
                        p.name.toLowerCase().includes(q) ||
                        p.code.toLowerCase().includes(q),
                );
            }

            totalProducts.value = await collection.count();
            products.value = await collection
                .offset((currentPage.value - 1) * perPage)
                .limit(perPage)
                .toArray();
        }
    } catch (error) {
        console.error("Load Products Error:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Gagal memuat produk.",
        });
    } finally {
        loadingProducts.value = false;
    }
}

function changePage(page) {
    currentPage.value = page;
    loadProducts();
}

function itemGrossPrice(item) {
    let price = item.price;
    if (orderType.value === "ojol") {
        price = item.ojol_price > 0 ? item.ojol_price : item.price;
    } else if (orderType.value === "wholesale") {
        price = item.wholesale_price > 0 ? item.wholesale_price : item.price;
    }
    return price;
}

function itemProductDiscount(item) {
    if (orderType.value === "ojol") {
        return item.ojol_discount || 0;
    } else if (orderType.value === "wholesale") {
        return item.wholesale_discount || 0;
    }
    return item.discount_amount || 0;
}

function getAppliedPromotion(item, grossSubtotal) {
    const price = itemGrossPrice(item);
    const qty = item.qty || 1;
    
    let stackableDiscountTotal = 0;
    let stackableId = null;
    let nonStackablePromos = [];

    promotions.value.forEach(promo => {
        if (promo.type === 'announcement') return;
        if (promo.min_purchase > 0 && grossSubtotal < promo.min_purchase) return;
        
        let isApplicable = false;
        if (promo.applicable_type === 'all') {
            isApplicable = true;
        } else if (promo.applicable_type === 'products' && (promo.product_ids || []).includes(item.id)) {
            isApplicable = true;
        } else if (promo.applicable_type === 'categories' && (promo.category_ids || []).includes(item.category_id)) {
            isApplicable = true;
        }
        
        if (isApplicable) {
            let totalVal = 0;
            if (promo.type === 'discount_percentage') {
                totalVal = (price * (promo.discount_value / 100)) * qty;
            } else if (promo.type === 'discount_fixed') {
                totalVal = promo.is_multiple ? (promo.discount_value * qty) : promo.discount_value;
            }

            if (promo.is_stackable) {
                stackableDiscountTotal += totalVal;
                if (!stackableId) stackableId = promo.id;
            } else {
                nonStackablePromos.push({ id: promo.id, value: totalVal });
            }
        }
    });

    // Pick best from non-stackable (Compare TOTAL value)
    let bestNonStackable = { id: null, value: 0 };
    nonStackablePromos.forEach(p => {
        if (p.value > bestNonStackable.value) {
            bestNonStackable = p;
        }
    });
    
    return { 
        discount: bestNonStackable.value + stackableDiscountTotal, 
        id: bestNonStackable.id || stackableId 
    };
}

function calculateItemPromotionDiscount(item, grossSubtotal) {
    const totalLineDiscount = getAppliedPromotion(item, grossSubtotal).discount;
    return totalLineDiscount / (item.qty || 1);
}

function itemPrice(item) {
    const gross = itemGrossPrice(item);
    const productDisc = itemProductDiscount(item);
    const promoLineDiscount = getAppliedPromotion(item, cartGrossSubtotal.value).discount;
    const unitPromoDiscount = promoLineDiscount / (item.qty || 1);
    
    return Math.max(0, gross - productDisc - unitPromoDiscount);
}

const cartGrossSubtotal = computed(() => {
    return cart.value.reduce(
        (sum, item) => sum + itemGrossPrice(item) * item.qty,
        0,
    );
});

const cartPromotionDiscount = computed(() => {
    const gross = cartGrossSubtotal.value;
    return cart.value.reduce((sum, item) => {
        const productDisc = itemProductDiscount(item);
        const promoDisc = calculateItemPromotionDiscount(item, gross);
        return sum + (productDisc + promoDisc) * item.qty;
    }, 0);
});

const cartSubtotal = computed(() => {
    return cartGrossSubtotal.value - cartPromotionDiscount.value;
});

const cartServiceCharge = computed(() => {
    return cart.value.reduce((sum, item) => {
        const subtotal = itemPrice(item) * item.qty;
        return sum + (subtotal * (item.service_charge_rate || 0)) / 100;
    }, 0);
});

const cartTax = computed(() => {
    return cart.value.reduce((sum, item) => {
        const subtotal = itemPrice(item) * item.qty;
        return sum + (subtotal * (item.tax_rate || 0)) / 100;
    }, 0);
});

const cartTotalBeforeRounding = computed(() => {
    return cartSubtotal.value + cartServiceCharge.value + cartTax.value;
});

const cartRounding = computed(() => {
    const total = cartTotalBeforeRounding.value;
    const remainder = total % 1000;
    return remainder > 0 ? 1000 - remainder : 0;
});

const cartTotal = computed(() => {
    return cartTotalBeforeRounding.value + cartRounding.value;
});

const changeAmount = computed(() => {
    return (paidAmount.value || 0) - cartTotal.value;
});

// Dual Screen / Customer Display
let customerDisplayChannel = null;

function openCustomerDisplay() {
    const url = router.resolve({ name: "pos-display" }).href;
    window.open(url, "RestokuCustomerDisplay", "width=1280,height=720");
}

function syncToCustomerDisplay() {
    if (customerDisplayChannel) {
        customerDisplayChannel.postMessage(
            JSON.parse(
                JSON.stringify({
                    type: "update_cart",
                    data: {
                        cart: cart.value,
                        subtotal: cartSubtotal.value,
                        tax: cartTax.value,
                        serviceCharge: cartServiceCharge.value,
                        total: cartTotal.value,
                    },
                }),
            ),
        );
    }
}

// Watch for cart changes to sync
watch(cart, syncToCustomerDisplay, { deep: true });
watch(cartTotal, syncToCustomerDisplay);

watch(showPayment, (val) => {
    if (val) {
        // Generate fresh UUID setiap kali modal dibuka — key baru = percobaan bayar baru
        idempotencyKey.value = uuidv4();
        if (customerDisplayChannel) {
            customerDisplayChannel.postMessage({ type: "payment_start" });
        }
    }
});

function money(value) {
    return Number(value || 0).toLocaleString("id-ID");
}

function updateTime() {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
    currentDate.value = now.toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

function addToCart(product) {
    const existing = cart.value.find((item) => item.id === product.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.value.push({ ...product, qty: 1 });
    }
}

function decreaseQty(item) {
    const existing = cart.value.find((i) => i.id === item.id);
    if (existing) {
        if (existing.qty > 1) {
            existing.qty -= 1;
        } else {
            cart.value = cart.value.filter((i) => i.id !== item.id);
        }
    }
}

async function promptPendingOrder() {
    const token = prompt("Masukkan Token QR Pelanggan (Contoh: PO-ABC123):");
    if (token) {
        loadPendingOrder(token);
    }
}
async function loadPendingOrder(token) {
    loading.value = true;
    try {
        const response = await orderApi.fetchPending(token);
        const pendingOrder = response.data.data;

        // Clear cart or merge? User said "otomatis memasukan", usually means loading that specific order.
        // We'll merge to be safe, but with full details from the API.

        for (const item of pendingOrder.items) {
            // Map OrderItem structure to Product structure used in cart
            const cartItem = {
                ...item,
                id: item.product_id, // Use product_id as the unique identifier in cart
                qty: item.quantity,   // Standardize field name to 'qty'
                notes: item.notes || ""
            };

            const existing = cart.value.find((i) => i.id === cartItem.id);

            if (existing && existing.notes === cartItem.notes) {
                existing.qty += cartItem.qty;
            } else {
                cart.value.push(cartItem);
            }
        }

        if (pendingOrder.table_number) {
            tableNumber.value = pendingOrder.table_number;
        }

        if (pendingOrder.customer_name) {
            // Optional: Set customer name if we have a field for it in POS
        }

        toast.add({
            severity: "success",
            summary: "Order Loaded",
            detail: `Order from ${pendingOrder.customer_name || "Customer"} loaded.`,
            life: 3000,
        });
    } catch (error) {
        console.error("QR Load Error:", error);
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "Invalid Token or Order already processed",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

function editItemNote(item) {
    const note = prompt("Add Note for " + item.name, item.notes || "");
    if (note !== null) {
        item.notes = note;
    }
}

async function checkout() {
    if (cart.value.length === 0) return;
    if (!accountId.value) {
        toast.add({
            severity: "warn",
            summary: "Peringatan",
            detail: "Pilih rekening kas.",
            life: 3000,
        });
        return;
    }

    saving.value = true;

    const payload = {
        idempotency_key: idempotencyKey.value,
        customer_id: selectedCustomer.value?.id || null,
        account_id: accountId.value,
        table_number: tableNumber.value,
        order_type: orderType.value,
        items: cart.value.map((item) => {
            const gross = itemGrossPrice(item);
            const productDisc = itemProductDiscount(item);
            const promo = getAppliedPromotion(item, cartGrossSubtotal.value);
            return {
                product_id: item.id,
                quantity: item.qty,
                price: gross,
                discount_amount: (productDisc * item.qty) + promo.discount,
                promotion_id: promo.id,
                notes: item.notes || "",
            };
        }),
        paid_amount: paidAmount.value,
        payment_method: paymentMethod.value,
        notes: "",
    };

    try {
        if (isOnline.value) {
            const res = await orderApi.create(payload);
            lastOrder.value = res.data.data;

            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: "Pesanan berhasil disimpan.",
                life: 3000,
            });
            showPayment.value = false;
            showReceipt.value = true;
            resetPOS();
        } else {
            // Offline Flow
            await saveOrderOffline(payload);
            await updatePendingSyncCount();

            toast.add({
                severity: "info",
                summary: "Offline",
                detail: "Pesanan disimpan lokal & akan disinkronkan saat online.",
                life: 5000,
            });
            showPayment.value = false;
            resetPOS();
        }
    } catch (error) {
        console.error("Checkout Error:", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail:
                error.response?.data?.message ||
                "Terjadi kesalahan saat memproses pesanan.",
            life: 5000,
        });
    } finally {
        saving.value = false;
    }
}

async function updatePendingSyncCount() {
    const pending = await getPendingOrders();
    pendingSyncCount.value = pending.length;
}

async function syncOfflineOrders() {
    if (!isOnline.value || isSyncing.value) return;

    const pending = await getPendingOrders();
    if (pending.length === 0) return;

    isSyncing.value = true;

    for (const order of pending) {
        try {
            await orderApi.create(order);
            await markOrderSynced(order.id);
        } catch (error) {
            console.error(`Failed to sync order #${order.id}:`, error);
            if (
                error.response?.status === 422 ||
                error.response?.status === 409
            ) {
                await markOrderSynced(order.id);
            }
        }
    }

    await updatePendingSyncCount();
    isSyncing.value = false;

    if (pendingSyncCount.value === 0) {
        toast.add({
            severity: "success",
            summary: "Sync Berhasil",
            detail: "Semua data offline telah tersinkronisasi.",
            life: 3000,
        });
    }
}

function resetPOS() {
    cart.value = [];
    selectedCustomer.value = null;
    customerName.value = "";
    tableNumber.value = "";
    paidAmount.value = 0;
    paymentMethod.value = "cash";
    orderType.value = "regular";
    showReceipt.value = false;
    lastOrder.value = null;
    if (customerDisplayChannel) {
        customerDisplayChannel.postMessage({ type: "reset" });
    }
}

async function quickAddCustomer() {
    if (!newCustomer.value.name) return;
    savingCustomer.value = true;
    try {
        const response = await customerApi.create(newCustomer.value);
        const created = response?.data?.data;
        customers.value.unshift(created);
        selectedCustomer.value = created;
        showAddCustomer.value = false;
        newCustomer.value = { name: "", phone: "" };
        toast.add({
            severity: "success",
            summary: "Customer Added",
            life: 2000,
        });
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to add customer",
            life: 3000,
        });
    } finally {
        savingCustomer.value = false;
    }
}

function printReceipt() {
    window.print();
}

function sendWhatsApp() {
    if (!lastOrder.value) return;
    let text = `Terima kasih telah berbelanja di Restoku!\n\n`;
    text += `No. Pesanan: ${lastOrder.value.order_number}\n`;
    text += `--------------------------------\n`;
    lastOrder.value.items.forEach((item) => {
        text += `${item.quantity}x ${item.product_name || item.product?.name} - Rp ${money(item.subtotal)}\n`;
    });
    text += `--------------------------------\n`;
    text += `Total: Rp ${money(lastOrder.value.total_amount)}\n`;
    text += `Pembayaran (${lastOrder.value.payment_method}): Rp ${money(lastOrder.value.paid_amount)}\n`;
    if (lastOrder.value.payment_method === "cash") {
        text += `Kembalian: Rp ${money(lastOrder.value.change_amount)}\n`;
    }

    const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(url, "_blank");
}

async function bootstrap() {
    try {
        if (isOnline.value) {
            const [categoryRes, accountRes, customerRes, allProductsRes, promotionRes] =
                await Promise.all([
                    categoryApi.getAll(),
                    financeApi.getAccounts(),
                    customerApi.getAll(),
                    productApi.getAll({ per_page: 1000, is_active: 1 }), // Ambil banyak untuk cache
                    promotionApi.getAll({ is_active: 1 }),
                ]);

            categories.value = categoryRes?.data?.data || [];
            accounts.value = accountRes?.data?.data || [];
            customers.value = customerRes?.data?.data || [];
            promotions.value = promotionRes?.data?.data || [];

            // Sync ke IndexedDB
            await syncCategoriesToLocal(categories.value);
            if (allProductsRes?.data?.data?.data) {
                await syncProductsToLocal(allProductsRes.data.data.data);
            }
        } else {
            // Load dari IndexedDB
            categories.value = await db.categories.toArray();
            // Customers & Accounts mungkin belum di-cache secara masif, tapi idealnya iya.
            toast.add({
                severity: "info",
                summary: "Mode Offline",
                detail: "Menggunakan data lokal.",
                life: 3000,
            });
        }

        accountId.value = accounts.value[0]?.id || null;
        await loadProducts();
        await updatePendingSyncCount();

        // Check active shift
        if (isOnline.value) {
            const shiftRes = await shiftApi.getCurrent();
            activeShift.value = shiftRes?.data?.data || null;
            if (!activeShift.value || activeShift.value.is_expired) {
                showShiftNotice.value = true;
            }
        }
    } catch (error) {
        console.error("Bootstrap Error:", error);
    }
}

function handleOnline() {
    isOnline.value = true;
    toast.add({
        severity: "success",
        summary: "Online",
        detail: "Koneksi internet terhubung kembali.",
        life: 3000,
    });
    syncOfflineOrders();
}

function handleOffline() {
    isOnline.value = false;
    toast.add({
        severity: "warn",
        summary: "Offline",
        detail: "Koneksi internet terputus. Bekerja dalam mode offline.",
        life: 5000,
    });
}

onMounted(() => {
    bootstrap();
    updateTime();
    timer = setInterval(updateTime, 60000);

    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    // Auto sync check every 30s if online
    const syncTimer = setInterval(() => {
        if (isOnline.value && pendingSyncCount.value > 0) {
            syncOfflineOrders();
        }
    }, 30000);

    // Initialize customer display channel
    customerDisplayChannel = new BroadcastChannel("pos_customer_display");
    customerDisplayChannel.onmessage = (event) => {
        if (event.data.type === "request_sync") {
            syncToCustomerDisplay();
        }
    };

    // Listen for real-time orders/payments
    if (echo && authStore.user?.tenant_id) {
        echo.private(`tenant.${authStore.user.tenant_id}`).listen(
            "OrderCreated",
            (e) => {
                toast.add({
                    severity: "info",
                    summary: "Pesanan Baru",
                    detail: `Pesanan ${e.order_number} sebesar Rp ${money(e.total_amount)} telah berhasil diproses.`,
                    life: 5000,
                });
                // Refresh products to update stock levels
                loadProducts();
            },
        );
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
    window.removeEventListener("online", handleOnline);
    window.removeEventListener("offline", handleOffline);

    if (customerDisplayChannel) customerDisplayChannel.close();
    if (echo && authStore.user?.tenant_id) {
        echo.leave(`tenant.${authStore.user.tenant_id}`);
    }
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #printable-receipt,
    #printable-receipt * {
        visibility: visible;
    }

    #printable-receipt {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 300px;
        margin: 0;
        padding: 10px;
    }
}

.print-block {
    display: none;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
