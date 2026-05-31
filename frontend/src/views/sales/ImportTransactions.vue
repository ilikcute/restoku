<template>
    <AppPage
        title="Import Transaksi Excel"
        :breadcrumb="[$t('common.sales'), 'Import Transaksi']"
    >
        <div class="space-y-6 max-w-6xl mx-auto mt-4 pb-12">
            <!-- Header Information -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <p class="text-slate-500 text-sm">Unggah file Excel (Data Penjualan) untuk diimport ke dalam pesanan.</p>
                </div>
                <div v-if="hasStagingData" class="flex gap-2">
                    <Button 
                        label="Unggah File Lain" 
                        icon="pi pi-upload" 
                        outlined 
                        severity="secondary" 
                        class="!rounded-xl"
                        @click="resetStagingView"
                    />
                </div>
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
                            class="!bg-emerald-600 !border-none !rounded-xl flex-1 !py-3 shadow-md shadow-emerald-100"
                            :loading="uploading"
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
            <div v-else class="space-y-6">
                <!-- Info Message / Shift status -->
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i class="pi pi-info-circle text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-800">Review Data Staging</h4>
                        <p class="text-xs text-slate-500">Transaksi berhasil disimpan sementara. Pilih tanggal dan nomor transaksi yang ingin Anda masukkan ke database utama.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                            <i class="pi pi-shopping-cart text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi Tersimpan</p>
                            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ summary.total_orders }} Transaksi</h3>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                            <i class="pi pi-money-bill text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nilai Staging</p>
                            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ formatCurrency(summary.total_amount) }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Selection Panel Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Dates Panel (Left 5 cols) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col min-h-[400px]">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-calendar text-emerald-600"></i>
                                <span class="font-bold text-slate-800">Filter Tanggal</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox v-model="selectAllDates" :binary="true" @change="onToggleAllDates" id="select-all-dates" />
                                <label for="select-all-dates" class="text-xs font-semibold text-slate-600 cursor-pointer">Pilih Semua</label>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto max-h-[350px] p-4 space-y-3">
                            <div 
                                v-for="dateItem in summary.dates" 
                                :key="dateItem.date"
                                class="flex items-center justify-between p-3 rounded-xl border border-slate-50 bg-slate-50/20 hover:bg-slate-50 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="selectedDates" :value="dateItem.date" />
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-sm">{{ formatDate(dateItem.date) }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ dateItem.count }} Transaksi</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-slate-700 text-sm">{{ formatCurrency(dateItem.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Numbers Panel (Right 7 cols) -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col min-h-[400px]">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-ticket text-emerald-600"></i>
                                <span class="font-bold text-slate-800">Pilih Nomor Transaksi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox v-model="selectAllOrders" :binary="true" @change="onToggleAllOrders" id="select-all-orders" />
                                <label for="select-all-orders" class="text-xs font-semibold text-slate-600 cursor-pointer">Pilih Semua</label>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto max-h-[350px] p-4 space-y-2">
                            <div v-if="filteredOrders.length === 0" class="flex flex-col items-center justify-center h-full py-12 text-slate-400 text-center gap-2">
                                <i class="pi pi-info-circle text-2xl text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-500">Pilih tanggal di sebelah kiri untuk melihat daftar nomor transaksi.</p>
                            </div>
                            <div 
                                v-else
                                v-for="order in filteredOrders" 
                                :key="order.order_number"
                                class="flex items-center justify-between p-3 rounded-xl border border-slate-50 transition-colors"
                                :class="order.already_imported ? 'bg-slate-100/55 opacity-70 cursor-not-allowed' : 'bg-slate-50/20 hover:bg-slate-50'"
                            >
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="selectedOrderNumbers" :value="order.order_number" :disabled="order.already_imported" />
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-slate-700 text-sm">#{{ order.order_number }}</span>
                                            <span v-if="order.already_imported" class="text-[9px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full border border-red-100">Sudah Diimpor</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-medium">Meja: {{ order.table_number || '-' }} | Tanggal: {{ formatDate(order.date) }}</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-slate-700 text-sm">{{ formatCurrency(order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Confirmation Panel -->
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <h4 class="font-bold text-slate-700">Siap untuk diimport ke kasir?</h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ activeSelectionCount }} transaksi terpilih untuk diproses.
                        </p>
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <Button 
                            label="Hapus Pilihan" 
                            icon="pi pi-times" 
                            severity="secondary" 
                            outlined 
                            class="!rounded-xl flex-1 sm:flex-initial"
                            @click="clearSelections"
                        />
                        <Button 
                            label="Konfirmasi & Proses Impor" 
                            icon="pi pi-check" 
                            severity="success" 
                            class="!rounded-xl !bg-emerald-600 !border-none shadow-md shadow-emerald-100 flex-1 sm:flex-initial"
                            :loading="committing"
                            :disabled="activeSelectionCount === 0"
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

const summary = ref({
    total_orders: 0,
    total_amount: 0,
    dates: [],
    orders: []
});

const selectedDates = ref([]);
const selectedOrderNumbers = ref([]);
const selectAllDates = ref(false);
const selectAllOrders = ref(false);

const selectedFile = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

// Computed properties
const hasStagingData = computed(() => {
    return summary.value.total_orders > 0;
});

const filteredOrders = computed(() => {
    if (selectedDates.value.length === 0) {
        return [];
    }
    return summary.value.orders.filter(order => selectedDates.value.includes(order.date));
});

const activeSelectionCount = computed(() => {
    // If selected dates are checked, count all orders corresponding to those dates + individual orders not on those dates
    const ordersFromSelectedDates = summary.value.orders.filter(o => selectedDates.value.includes(o.date)).map(o => o.order_number);
    const combinedSet = new Set([...ordersFromSelectedDates, ...selectedOrderNumbers.value]);
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
        selectedDates.value = summary.value.dates.map(d => d.date);
    } else {
        selectedDates.value = [];
    }
};

const onToggleAllOrders = () => {
    if (selectAllOrders.value) {
        selectedOrderNumbers.value = filteredOrders.value
            .filter(o => !o.already_imported)
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

const resetStagingView = () => {
    summary.value = {
        total_orders: 0,
        total_amount: 0,
        dates: [],
        orders: []
    };
    clearSelections();
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
