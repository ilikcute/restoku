<template>
    <AppPage
        title="Import Transaksi Excel"
        :breadcrumb="[$t('common.sales'), 'Import Transaksi']"
    >
        <div class="space-y-3.5 max-w-6xl mx-auto mt-2 pb-8">
            <!-- Header Information -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col">
                    <p class="text-slate-500 text-xs sm:text-sm">Unggah file Excel (Data Penjualan) untuk diimport ke dalam pesanan.</p>
                </div>
                <div v-if="hasStagingData" class="flex gap-2">
                    <Button 
                        v-if="hasEmptyRecords"
                        label="Hapus Data Kosong" 
                        icon="pi pi-trash" 
                        severity="danger" 
                        outlined 
                        class="!rounded-lg !text-xs !py-1.5 !px-3 font-medium"
                        :loading="cleaningEmpty"
                        @click="deleteEmptyStaging"
                    />
                    <Button 
                        label="Unggah File Lain" 
                        icon="pi pi-upload" 
                        outlined 
                        severity="secondary" 
                        class="!rounded-lg !text-xs !py-1.5 !px-3 font-medium"
                        @click="resetStagingView"
                    />
                </div>
            </div>

            <!-- Active Shift Verification Banner (Compact) -->
            <div v-if="!loadingSummary && hasActiveShiftToday" class="bg-emerald-50/80 border border-emerald-200/80 rounded-xl px-3.5 py-2 flex items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-slate-700 flex-wrap">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-500 text-white shrink-0">
                        <i class="pi pi-check text-[10px]"></i>
                    </span>
                    <span class="font-bold text-slate-800">Shift Kasir Aktif</span>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-extrabold px-1.5 py-0.5 rounded-full border border-emerald-200 uppercase tracking-wide">Open</span>
                    <span class="text-slate-300">•</span>
                    <span>Tanggal: <strong class="text-slate-800">{{ formatDate(activeShift?.date) }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>Kasir: <strong class="text-slate-800">{{ activeShift?.user_name || 'Kasir' }}</strong></span>
                </div>
                <div class="text-[11px] text-emerald-700 font-medium hidden md:inline-flex items-center gap-1 shrink-0">
                    <i class="pi pi-shield"></i> Validasi tanggal shift aktif
                </div>
            </div>

            <!-- Inactive Shift Warning Banner (Compact) -->
            <div v-else-if="!loadingSummary" class="bg-amber-50/90 border border-amber-200 rounded-xl px-3.5 py-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 text-xs">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-500 text-white shrink-0">
                        <i class="pi pi-exclamation-triangle text-xs"></i>
                    </span>
                    <div>
                        <span class="font-bold text-amber-950">Shift Kasir Belum Dibuka Untuk Hari Ini:</span>
                        <span class="text-amber-800 ml-1">
                            {{ !activeShift ? 'Tidak ada shift berstatus open. Wajib buka shift kasir terlebih dahulu.' : 'Shift aktif bukan untuk hari ini. Tutup shift sebelumnya dan buka shift baru.' }}
                        </span>
                    </div>
                </div>
                <router-link to="/sales/shifts" class="shrink-0 w-full sm:w-auto">
                    <Button 
                        label="Buka Shift di Kasir" 
                        icon="pi pi-external-link" 
                        severity="warning" 
                        class="!rounded-lg text-xs !px-3 !py-1 font-bold shadow-xs w-full sm:w-auto"
                    />
                </router-link>
            </div>

            <!-- STEP 1: Upload File View (if no staging data is present) -->
            <div v-if="uploading" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col items-center justify-center gap-6 min-h-[350px]">
                <div class="flex flex-col items-center justify-center gap-4 text-center max-w-md">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100 relative animate-bounce">
                        <i class="pi pi-spin pi-spinner text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Proses Impor Sedang Berjalan...</h3>
                    <p class="text-sm text-slate-500">Sistem sedang membaca, memvalidasi, dan mengunggah data transaksi dari file Excel ke penyimpanan sementara. Harap jangan menutup halaman ini.</p>
                </div>
            </div>

            <div v-else-if="!hasStagingData && !loadingSummary" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col items-center justify-center gap-6 min-h-[350px]">
                <!-- Drag and Drop Zone -->
                <div 
                    v-if="!selectedFile"
                    class="w-full max-w-lg border-2 border-dashed rounded-3xl p-8 flex flex-col items-center justify-center gap-4 cursor-pointer transition-all duration-300 min-h-[250px] border-slate-200 hover:border-emerald-500 hover:bg-slate-50/50 bg-white group"
                    :class="isDragging ? '!border-emerald-500 bg-emerald-50/30' : ''"
                    @click="triggerFileSelect"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="onDrop"
                >
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept=".xls,.xlsx" 
                        @change="onFileSelect" 
                    />
                    
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm border border-slate-100 transition-transform duration-300 group-hover:scale-105">
                        <i class="pi pi-cloud-upload text-3xl"></i>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-slate-700 mb-1">Pilih atau Seret File Excel</p>
                        <p class="text-xs text-slate-400">Mendukung format .xls dan .xlsx (Maks. 10MB)</p>
                    </div>
                    <Button 
                        label="Pilih File dari Komputer" 
                        icon="pi pi-plus" 
                        class="!bg-emerald-600 !border-none !rounded-xl !px-4 !py-2.5 shadow-sm text-sm"
                        @click.stop="triggerFileSelect"
                    />
                </div>

                <!-- Selected File Display -->
                <div v-else class="w-full max-w-lg bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                    <!-- File Info Card -->
                    <div class="flex items-center gap-4 p-4 bg-slate-50/50 rounded-xl border border-slate-100">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                            <i class="pi pi-file-excel text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-800 text-sm truncate">{{ selectedFile.name }}</h4>
                            <p class="text-xs text-slate-400 font-semibold mt-0.5">{{ formatFileSize(selectedFile.size) }}</p>
                        </div>
                        <span class="text-[10px] font-bold text-orange-500 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-100 shrink-0">Pending</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3">
                        <Button 
                            label="Batal" 
                            icon="pi pi-times" 
                            outlined 
                            severity="secondary" 
                            class="!rounded-xl flex-1 !py-3"
                            @click="clearSelectedFile"
                        />
                        <Button 
                            label="Mulai Import" 
                            icon="pi pi-upload" 
                            class="!bg-emerald-600 !border-none !rounded-xl flex-1 !py-3 shadow-md shadow-emerald-100 disabled:opacity-50 disabled:cursor-not-allowed"
                            :loading="uploading"
                            :disabled="!hasActiveShiftToday"
                            @click="onStartImport"
                        />
                    </div>
                </div>
            </div>

            <!-- Loading Summary State -->
            <div v-else-if="loadingSummary" class="flex flex-col items-center justify-center min-h-[350px] gap-3">
                <i class="pi pi-spin pi-spinner text-4xl text-emerald-500"></i>
                <p class="text-sm font-medium text-slate-500">Memuat data staging...</p>
            </div>

            <!-- STEP 2: Staging Dashboard View (if staging data is present) -->
            <div v-else class="space-y-3">
                <!-- Mismatched Dates Barrier Alert (Compact) -->
                <div v-if="hasMismatchedDates" class="bg-rose-50 border border-rose-200 rounded-xl px-3.5 py-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 text-rose-900">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-500 text-white shrink-0">
                            <i class="pi pi-shield text-[10px]"></i>
                        </span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-rose-950">Jegatan Shift:</span>
                            <span class="text-[10px] bg-rose-200 text-rose-900 font-extrabold px-1.5 py-0.5 rounded-full border border-rose-300 uppercase tracking-wide">Diblokir</span>
                            <span class="text-rose-800">Transaksi staging selain tanggal <strong>{{ formatDate(activeShift?.date) }}</strong> diblokir agar laporan kasir tidak selisih.</span>
                        </div>
                    </div>
                    <Button 
                        label="Hapus Data Beda Tanggal" 
                        icon="pi pi-trash" 
                        severity="danger" 
                        outlined 
                        class="!rounded-lg text-xs !py-1 !px-2.5 shrink-0 !bg-white !border-rose-300 hover:!bg-rose-50 font-semibold"
                        :loading="cleaningMismatched"
                        @click="deleteMismatchedStaging"
                    />
                </div>

                <!-- Stats Grid (Compact) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white py-2.5 px-4 rounded-xl border border-slate-100 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi Tersimpan</p>
                            <h3 class="text-base font-black text-slate-800 mt-0.5">{{ summary.total_orders }} Transaksi</h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs shrink-0">
                            <i class="pi pi-shopping-cart text-sm"></i>
                        </div>
                    </div>
                    <div class="bg-white py-2.5 px-4 rounded-xl border border-slate-100 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Staging</p>
                            <h3 class="text-base font-black text-slate-800 mt-0.5">{{ formatCurrency(summary.total_amount) }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs shrink-0">
                            <i class="pi pi-money-bill text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Selection Panel Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                    <!-- Dates Panel (Left 5 cols) -->
                    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-100 shadow-xs flex flex-col">
                        <div class="p-3 border-b border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-calendar text-emerald-600 text-sm"></i>
                                <span class="font-bold text-slate-800 text-sm">Filter Tanggal</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox v-model="selectAllDates" :binary="true" @change="onToggleAllDates" id="select-all-dates" />
                                <label for="select-all-dates" class="text-xs font-semibold text-slate-600 cursor-pointer">Pilih Semua</label>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto max-h-[360px] p-3 space-y-2">
                            <div 
                                v-for="dateItem in validDates" 
                                :key="dateItem.date"
                                class="flex items-center justify-between p-2.5 rounded-lg border transition-colors text-xs"
                                :class="!isShiftDate(dateItem.date) ? 'bg-rose-50/30 border-rose-100 opacity-60' : 'border-slate-50 bg-slate-50/20 hover:bg-slate-50'"
                            >
                                <div class="flex items-center gap-2.5">
                                    <Checkbox v-model="selectedDates" :value="dateItem.date" :disabled="!isShiftDate(dateItem.date)" />
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-700 text-xs">{{ formatDate(dateItem.date) }}</span>
                                            <span v-if="!isShiftDate(dateItem.date)" class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-full border border-rose-200 shrink-0">Beda Shift</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ dateItem.count }} Transaksi</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-slate-700 text-xs">{{ formatCurrency(dateItem.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Numbers Panel (Right 7 cols) -->
                    <div class="lg:col-span-7 bg-white rounded-xl border border-slate-100 shadow-xs flex flex-col">
                        <div class="p-3 border-b border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-ticket text-emerald-600 text-sm"></i>
                                <span class="font-bold text-slate-800 text-sm">Pilih Nomor Transaksi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox v-model="selectAllOrders" :binary="true" @change="onToggleAllOrders" id="select-all-orders" />
                                <label for="select-all-orders" class="text-xs font-semibold text-slate-600 cursor-pointer">Pilih Semua</label>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto max-h-[360px] p-3 space-y-2">
                            <div v-if="filteredOrders.length === 0" class="flex flex-col items-center justify-center h-full py-10 text-slate-400 text-center gap-2">
                                <i class="pi pi-info-circle text-xl text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-500">Pilih tanggal di sebelah kiri untuk melihat daftar nomor transaksi.</p>
                            </div>
                            <div 
                                v-else
                                v-for="order in filteredOrders" 
                                :key="order.order_number"
                                class="flex items-center justify-between p-2.5 rounded-lg border border-slate-50 transition-colors text-xs"
                                :class="order.already_imported || !isShiftDate(order.date) ? 'bg-slate-100/55 opacity-70 cursor-not-allowed' : 'bg-slate-50/20 hover:bg-slate-50'"
                            >
                                <div class="flex items-center gap-2.5">
                                    <Checkbox v-model="selectedOrderNumbers" :value="order.order_number" :disabled="order.already_imported || !isShiftDate(order.date)" />
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-slate-700 text-xs">#{{ order.order_number }}</span>
                                            <span v-if="order.already_imported" class="text-[9px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded-full border border-red-100">Sudah Diimpor</span>
                                            <span v-else-if="!isShiftDate(order.date)" class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-full border border-rose-200">Beda Shift</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-medium">Meja: {{ order.table_number || '-' }} | Tanggal: {{ formatDate(order.date) }}</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-slate-700 text-xs">{{ formatCurrency(order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Confirmation Panel (Compact) -->
                <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-center sm:text-left">
                        <h4 class="font-bold text-slate-700 text-sm">Siap untuk diimport ke kasir?</h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ activeSelectionCount }} transaksi terpilih untuk diproses.
                        </p>
                    </div>
                    <div class="flex gap-2.5 w-full sm:w-auto">
                        <Button 
                            label="Hapus Pilihan" 
                            icon="pi pi-times" 
                            severity="secondary" 
                            outlined 
                            class="!rounded-lg !text-xs !py-2 !px-3.5 flex-1 sm:flex-initial"
                            @click="clearSelections"
                        />
                        <Button 
                            label="Konfirmasi & Proses Impor" 
                            icon="pi pi-check" 
                            severity="success" 
                            class="!rounded-lg !text-xs !py-2 !px-4 !bg-emerald-600 !border-none shadow-xs shadow-emerald-100 flex-1 sm:flex-initial font-bold"
                            :loading="committing"
                            :disabled="activeSelectionCount === 0 || hasInvalidDateSelected"
                            @click="onConfirmImport"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppPage>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import AppPage from '@/components/layout/AppPage.vue';
import api from '@/api/axios';

const toast = useToast();

const uploading = ref(false);
const committing = ref(false);
const loadingSummary = ref(false);
const cleaningEmpty = ref(false);
const cleaningMismatched = ref(false);

const summary = ref({
    total_orders: 0,
    total_amount: 0,
    dates: [],
    orders: [],
    active_shift: null,
    mismatched_dates_count: 0
});

const selectedDates = ref([]);
const selectedOrderNumbers = ref([]);
const selectAllDates = ref(false);
const selectAllOrders = ref(false);

const selectedFile = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

// Shift & Date helpers
const activeShift = computed(() => summary.value.active_shift || null);
const hasActiveShiftToday = computed(() => {
    return !!activeShift.value && activeShift.value.is_today === true && activeShift.value.status === 'open';
});

const isShiftDate = (dateStr) => {
    if (!dateStr || !activeShift.value?.date) return false;
    const dStr = String(dateStr).split('T')[0];
    const sStr = String(activeShift.value.date).split('T')[0];
    return dStr === sStr;
};

const hasStagingData = computed(() => {
    return summary.value.total_orders > 0 && Number(summary.value.total_amount) > 0;
});

const validDates = computed(() => {
    return (summary.value.dates || []).filter(d => d.date && Number(d.total) > 0 && d.count > 0);
});

const hasMismatchedDates = computed(() => {
    if (!activeShift.value?.date) return false;
    return (summary.value.mismatched_dates_count > 0) || validDates.value.some(d => !isShiftDate(d.date));
});

const hasInvalidDateSelected = computed(() => {
    const invalidDates = selectedDates.value.some(d => !isShiftDate(d));
    const invalidOrders = selectedOrderNumbers.value.some(num => {
        const order = summary.value.orders.find(o => o.order_number === num);
        return order && !isShiftDate(order.date);
    });
    return invalidDates || invalidOrders;
});

const hasEmptyRecords = computed(() => {
    const hasZeroDates = (summary.value.dates || []).some(d => !d.date || Number(d.total) <= 0);
    const hasZeroOrders = (summary.value.orders || []).some(o => Number(o.total_amount) <= 0);
    return hasZeroDates || hasZeroOrders;
});

const filteredOrders = computed(() => {
    if (selectedDates.value.length === 0) {
        return [];
    }
    return (summary.value.orders || []).filter(order => 
        selectedDates.value.includes(order.date) && Number(order.total_amount) > 0
    );
});

const activeSelectionCount = computed(() => {
    // If selected dates are checked, count all valid orders corresponding to those dates + individual orders not on those dates
    const ordersFromSelectedDates = (summary.value.orders || [])
        .filter(o => selectedDates.value.includes(o.date) && isShiftDate(o.date) && Number(o.total_amount) > 0 && !o.already_imported)
        .map(o => o.order_number);
    const individualValidOrders = (selectedOrderNumbers.value || []).filter(num => {
        const order = summary.value.orders.find(o => o.order_number === num);
        return order && isShiftDate(order.date) && Number(order.total_amount) > 0 && !order.already_imported;
    });
    const combinedSet = new Set([...ordersFromSelectedDates, ...individualValidOrders]);
    return combinedSet.size;
});

// Load summary on mount
const loadSummary = async () => {
    loadingSummary.value = true;
    try {
        const response = await api.get('/orders/import/summary');
        if (response.data.status === 'success' || response.data.success) {
            summary.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to load import summary', error);
    } finally {
        loadingSummary.value = false;
    }
};

onMounted(() => {
    loadSummary();
});

// Selection handlers
const onToggleAllDates = () => {
    if (selectAllDates.value) {
        selectedDates.value = validDates.value
            .filter(d => isShiftDate(d.date))
            .map(d => d.date);
    } else {
        selectedDates.value = [];
    }
};

const onToggleAllOrders = () => {
    if (selectAllOrders.value) {
        selectedOrderNumbers.value = filteredOrders.value
            .filter(o => !o.already_imported && isShiftDate(o.date))
            .map(o => o.order_number);
    } else {
        const visibleNumbers = filteredOrders.value.map(o => o.order_number);
        selectedOrderNumbers.value = selectedOrderNumbers.value.filter(num => !visibleNumbers.includes(num));
    }
};

watch(selectedDates, (newDates) => {
    // Keep selectedOrderNumbers synced: remove any order numbers whose date is no longer selected
    selectedOrderNumbers.value = selectedOrderNumbers.value.filter(orderNo => {
        const order = summary.value.orders.find(o => o.order_number === orderNo);
        return order && newDates.includes(order.date);
    });
    
    // Also reset selectAllOrders if there are no visible filtered orders
    if (filteredOrders.value.length === 0) {
        selectAllOrders.value = false;
    }
}, { deep: true });

const clearSelections = () => {
    selectedDates.value = [];
    selectedOrderNumbers.value = [];
    selectAllDates.value = false;
    selectAllOrders.value = false;
};

const deleteEmptyStaging = async () => {
    cleaningEmpty.value = true;
    try {
        const response = await api.delete('/orders/import/empty-records');
        if (response.data.status === 'success' || response.data.success) {
            toast.add({
                severity: 'info',
                summary: 'Berhasil',
                detail: response.data.message || 'Data kosong telah dibersihkan.',
                life: 3000
            });
            if (response.data.data?.summary) {
                summary.value = response.data.data.summary;
            } else {
                await loadSummary();
            }
            clearSelections();
        }
    } catch (error) {
        console.error('Failed to delete empty records', error);
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: 'Gagal membersihkan data kosong.',
            life: 3000
        });
    } finally {
        cleaningEmpty.value = false;
    }
};

const deleteMismatchedStaging = async () => {
    cleaningMismatched.value = true;
    try {
        const response = await api.delete('/orders/import/mismatched-records');
        if (response.data.status === 'success' || response.data.success) {
            toast.add({
                severity: 'info',
                summary: 'Berhasil',
                detail: response.data.message || 'Data transaksi beda tanggal telah dibersihkan.',
                life: 3000
            });
            if (response.data.data?.summary) {
                summary.value = response.data.data.summary;
            } else {
                await loadSummary();
            }
            clearSelections();
        }
    } catch (error) {
        console.error('Failed to delete mismatched records', error);
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: 'Gagal membersihkan data transaksi beda tanggal.',
            life: 3000
        });
    } finally {
        cleaningMismatched.value = false;
    }
};

const resetStagingView = async () => {
    try {
        await api.delete('/orders/import/clear');
    } catch (e) {
        console.error('Failed to clear staging on server', e);
    }
    summary.value = {
        total_orders: 0,
        total_amount: 0,
        dates: [],
        orders: []
    };
    clearSelections();
    clearSelectedFile();
};

// Formatting helpers
const formatCurrency = (val) => {
    return Number(val).toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const triggerFileSelect = () => {
    fileInput.value.click();
};

const onFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        handleFileSelection(file);
    }
};

const onDragOver = () => {
    isDragging.value = true;
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        handleFileSelection(file);
    }
};

const handleFileSelection = (file) => {
    const allowedExtensions = ['.xls', '.xlsx'];
    const fileName = file.name.toLowerCase();
    const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
    
    if (!isValidExtension) {
        toast.add({
            severity: 'error',
            summary: 'File Tidak Valid',
            detail: 'Hanya mendukung file Excel (.xls, .xlsx)',
            life: 3000
        });
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        toast.add({
            severity: 'error',
            summary: 'File Terlalu Besar',
            detail: 'Ukuran file maksimal adalah 10MB',
            life: 3000
        });
        return;
    }

    selectedFile.value = file;
};

const clearSelectedFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const onStartImport = async () => {
    if (!hasActiveShiftToday.value) {
        toast.add({
            severity: 'warn',
            summary: 'Shift Belum Aktif',
            detail: 'Harap buka shift kasir untuk hari ini terlebih dahulu sebelum mengunggah file.',
            life: 4000
        });
        return;
    }

    const file = selectedFile.value;
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    uploading.value = true;

    try {
        const response = await api.post('/orders/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        if (response.data.status === 'success' || response.data.success) {
            toast.add({
                severity: 'success',
                summary: 'Sukses',
                detail: response.data.message || 'Transaksi berhasil diunggah ke staging area.',
                life: 3000
            });
            
            if (response.data.data && response.data.data.summary) {
                summary.value = response.data.data.summary;
            } else {
                await loadSummary();
            }
            clearSelectedFile();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: error.response?.data?.message || error.message || 'Terjadi kesalahan saat mengimport',
            life: 5000
        });
    } finally {
        uploading.value = false;
    }
};

// Confirmation handler (Step 2)
const onConfirmImport = async () => {
    if (hasInvalidDateSelected.value) {
        toast.add({
            severity: 'error',
            summary: 'Transaksi Ditolak',
            detail: 'Terdapat transaksi dengan tanggal yang tidak sesuai dengan shift kasir aktif. Transaksi beda shift tidak dapat diproses.',
            life: 4000
        });
        return;
    }

    committing.value = true;
    try {
        const response = await api.post('/orders/import/confirm', {
            dates: selectedDates.value,
            order_numbers: selectedOrderNumbers.value
        });

        if (response.data.status === 'success' || response.data.success) {
            const count = response.data.data.committed_count;
            toast.add({
                severity: 'success',
                summary: 'Impor Berhasil',
                detail: `Berhasil memindahkan ${count} transaksi ke pesanan utama.`,
                life: 5000
            });
            
            // Reload summary (should be empty if all selected transactions are processed)
            await loadSummary();
            clearSelections();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal Impor',
            detail: error.response?.data?.message || error.message || 'Gagal memproses impor transaksi.',
            life: 5000
        });
    } finally {
        committing.value = false;
    }
};
</script>
