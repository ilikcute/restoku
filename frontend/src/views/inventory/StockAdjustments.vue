<template>
    <AppPage :title="$t('inventory.history')" :breadcrumb="[$t('common.inventory'), $t('inventory.adjustments')]"
        no-card>
        <template #actions>
            <Button :label="$t('inventory.new_adjustment')" icon="pi pi-plus"
                class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50"
                @click="openDialog()" />
        </template>

        <div class="space-y-6">
            <!-- Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <AppDataTable framed :value="rows" :loading="loading" paginator :rows="20" stripedRows
                    class="app-table">
                    <Column field="adjustment_number" header="Nomor" sortable class="font-bold text-primary-600" />
                    <Column field="adjustment_date" :header="$t('common.date')" sortable>
                        <template #body="{ data }">{{
                            formatDate(data.adjustment_date)
                        }}</template>
                    </Column>
                    <Column field="status" :header="$t('common.status')" class="text-center">
                        <template #body="{ data }">
                            <Tag :value="statusLabel(data.status)" :severity="statusSeverity(data.status)" rounded />
                        </template>
                    </Column>
                    <Column field="total_loss_amount" header="Loss (Rp)" class="text-right">
                        <template #body="{ data }">
                            <span v-if="data.total_loss_amount > 0" class="text-red-600 font-bold">
                                Rp {{ formatNumber(data.total_loss_amount) }}
                            </span>
                            <span v-else class="text-slate-400">-</span>
                        </template>
                    </Column>
                    <Column header="PIC" field="user.name" />
                    <Column header="" class="w-32 text-center">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-2">
                                <Button icon="pi pi-eye" severity="secondary" rounded text @click="viewDetail(data)" />
                                <Button v-if="data.status !== 'A'" icon="pi pi-pencil" severity="primary" rounded text
                                    @click="openDialog(data)" />
                            </div>
                        </template>
                    </Column>
                </AppDataTable>
            </div>
        </div>

        <!-- Adjustment Dialog -->
        <Dialog v-model:visible="dialogOpen" :header="dialogTitle" modal :style="{ width: '55rem' }" class="p-fluid">
            <div class="space-y-6 py-4">
                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Metode
                            Perhitungan</label>
                        <p class="text-sm font-medium text-slate-700">
                            Stock Opname Fisik (Multi-Stage)
                        </p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Saat Ini</label>
                        <div>
                            <Tag :value="statusLabel(form.status)" :severity="statusSeverity(form.status)" rounded />
                        </div>
                    </div>
                </div>

                <!-- Add Item Row (Only if not Finalized) -->
                <div v-if="form.status !== 'A'"
                    class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded-xl border border-emerald-100 shadow-sm">
                    <div class="md:col-span-7 space-y-1">
                        <label class="text-xs font-bold text-slate-500">{{
                            $t("common.name")
                        }}</label>
                        <Select v-model="activeItem.product_id" :options="products" optionLabel="name" optionValue="id"
                            filter :placeholder="$t('inventory.search_product')" class="w-full" />
                    </div>
                    <div class="md:col-span-3 space-y-1">
                        <label class="text-xs font-bold text-slate-500">Jumlah Fisik</label>
                        <InputNumber v-model="activeItem.actual_stock" mode="decimal" class="w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <Button icon="pi pi-plus" label="Tambah" severity="success" class="w-full" @click="addItem"
                            :disabled="!activeItem.product_id" />
                    </div>
                </div>

                <!-- Items Table -->
                <div class="border rounded-xl overflow-hidden">
                    <AppDataTable framed :value="form.items" class="app-table" stripedRows>
                        <Column header="Produk">
                            <template #body="{ data }">{{
                                nameById(data.product_id)
                            }}</template>
                        </Column>
                        <Column header="System" field="recorded_stock" class="text-center text-slate-400" />
                        <Column header="Fisik" class="text-center font-bold">
                            <template #body="{ data }">
                                <InputNumber v-if="form.status !== 'A'" v-model="data.actual_stock" mode="decimal"
                                    class="w-24 p-inputtext-sm text-center" />
                                <span v-else>{{ data.actual_stock }}</span>
                            </template>
                        </Column>
                        <Column header="Selisih" class="text-center">
                            <template #body="{ data }">
                                <span :class="calculateDiff(data) < 0
                                    ? 'text-red-600 font-bold'
                                    : calculateDiff(data) > 0
                                        ? 'text-green-600 font-bold'
                                        : 'text-slate-400'
                                    ">
                                    {{ calculateDiff(data) > 0 ? "+" : ""
                                    }}{{ calculateDiff(data) }}
                                </span>
                            </template>
                        </Column>
                        <Column v-if="form.status !== 'A'" class="w-12">
                            <template #body="slotProps">
                                <Button icon="pi pi-trash" severity="danger" text rounded
                                    @click="removeItem(slotProps.index)" />
                            </template>
                        </Column>
                    </AppDataTable>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500">Catatan Internal</label>
                    <Textarea v-model="form.notes" rows="2" class="w-full"
                        placeholder="Tambahkan keterangan jika ada barang hilang/rusak..." />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between w-full items-center">
                    <div class="flex gap-6">
                        <div class="text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Loss (HPP)
                            </p>
                            <p class="text-lg font-black text-rose-600">
                                Rp {{ formatNumber(totalLoss) }}
                            </p>
                        </div>
                        <div class="text-left border-l pl-6 border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Loss (Gross/Jual)
                            </p>
                            <p class="text-lg font-black text-slate-700">
                                Rp {{ formatNumber(totalGrossLoss) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button :label="$t('common.cancel')" severity="secondary" text @click="dialogOpen = false" />

                        <!-- Draft Action -->
                        <Button v-if="form.status === 'I'" :label="!form.id
                            ? 'Simpan Draft (I)'
                            : 'Draft Tersimpan (I)'
                            " icon="pi pi-save" outlined :loading="saving" :disabled="!!form.id" @click="save('I')" />

                        <!-- Verify Action -->
                        <Button v-if="form.status === 'I' || form.status === 'D'" :label="form.status === 'I'
                            ? 'Verifikasi (D)'
                            : 'Sudah Diverifikasi (D)'
                            " icon="pi pi-check-square" :severity="form.status === 'I' ? 'info' : 'secondary'
                                " :loading="saving" :disabled="form.status === 'D' || !form.id" @click="save('D')" />

                        <!-- Finalize Action -->
                        <Button v-if="
                            form.status === 'D' ||
                            (form.status === 'I' && form.id)
                        " label="Finalisasi (A)" icon="pi pi-check-circle" severity="danger" :loading="saving"
                            @click="save('A')" />
                    </div>
                </div>
            </template>
        </Dialog>

        <!-- Detail View Dialog -->
        <Dialog v-model:visible="detailOpen" header="Detail Penyesuaian Stok" modal :style="{ width: '45rem' }">
            <div v-if="selectedRow" class="space-y-4">
                <div class="flex justify-between border-b pb-3">
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-bold">
                            Nomor Bukti
                        </p>
                        <p class="text-xl font-black text-primary-600">
                            {{ selectedRow.adjustment_number }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 uppercase font-bold">
                            Tanggal
                        </p>
                        <p class="text-lg font-bold">
                            {{ formatDate(selectedRow.adjustment_date) }}
                        </p>
                    </div>
                </div>

                <AppDataTable framed :value="selectedRow.items" stripedRows class="app-table">
                    <Column header="Produk" field="product.name" />
                    <Column header="System" field="recorded_stock" class="text-center" />
                    <Column header="Fisik" field="actual_stock" class="text-center font-bold" />
                    <Column header="Selisih" class="text-center">
                        <template #body="{ data }">
                            <span :class="data.adjustment_quantity < 0
                                ? 'text-red-600'
                                : 'text-green-600'
                                ">
                                {{ data.adjustment_quantity }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Loss Value" class="text-right">
                        <template #body="{ data }">
                            <span v-if="data.loss_value > 0" class="text-red-600">Rp {{ formatNumber(data.loss_value)
                            }}</span>
                            <span v-else>-</span>
                        </template>
                    </Column>
                </AppDataTable>

                <div class="bg-slate-50 p-4 rounded-xl flex justify-between items-center">
                    <span class="font-bold text-slate-600">Total Kerugian (Inventory Loss)</span>
                    <span class="text-xl font-black text-red-600">Rp
                        {{ formatNumber(selectedRow.total_loss_amount) }}</span>
                </div>
            </div>
        </Dialog>
    </AppPage>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from "vue";
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useI18n } from "vue-i18n";
import { useToast } from "primevue/usetoast";
import { inventoryApi } from "@/api/inventory";
import { productApi } from "@/api/master";
import { unwrapCollection } from "@/utils/api";
import AppPage from "@/components/layout/AppPage.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";

const { t: $t } = useI18n();
const toast = useToast();

const loading = ref(false);
const saving = ref(false);
const rows = ref([]);
const products = ref([]);
const dialogOpen = ref(false);
const detailOpen = ref(false);
const selectedRow = ref(null);

const activeItem = reactive({ product_id: null, actual_stock: 0 });
const form = reactive({
    id: null,
    notes: "",
    status: "I",
    items: [],
});

const dialogTitle = computed(() => {
    if (form.id) return `Edit Stock Opname #${form.id}`;
    return "Inisialisasi Stock Opname Baru";
});

const totalLoss = computed(() => {
    return form.items.reduce((sum, item) => {
        const p = products.value.find((x) => x.id === item.product_id);
        const recorded = item.recorded_stock || 0;
        const diff = (item.actual_stock || 0) - recorded;

        // Nilai kerugian (HPP) jika selisih negatif
        if (diff < 0) {
            const costPrice = p?.cost_price || 0;
            return sum + Math.abs(diff) * costPrice;
        }
        return sum;
    }, 0);
});

const totalGrossLoss = computed(() => {
    return form.items.reduce((sum, item) => {
        const p = products.value.find((x) => x.id === item.product_id);
        const recorded = item.recorded_stock || 0;
        const diff = (item.actual_stock || 0) - recorded;

        // Nilai kerugian (Harga Jual) jika selisih negatif
        if (diff < 0) {
            const price = p?.price || 0;
            return sum + Math.abs(diff) * price;
        }
        return sum;
    }, 0);
});

function abs(val) {
    return Math.abs(val);
}

function openDialog(data = null) {
    if (data) {
        form.id = data.id;
        form.notes = data.notes || "";
        form.status = data.status || "I";
        form.items = Array.isArray(data.items)
            ? data.items.map((i) => ({
                product_id: i.product?.id || i.product_id,
                actual_stock: i.actual_stock,
                recorded_stock: i.recorded_stock,
            }))
            : [];
    } else {
        form.id = null;
        form.notes = "";
        form.status = "I";
        form.items = [];
    }
    activeItem.product_id = null;
    activeItem.actual_stock = 0;
    dialogOpen.value = true;
}

function viewDetail(data) {
    selectedRow.value = data;
    detailOpen.value = true;
}

function addItem() {
    if (!activeItem.product_id) return;

    const existing = form.items.find(
        (i) => i.product_id === activeItem.product_id,
    );
    if (existing) {
        existing.actual_stock = activeItem.actual_stock;
    } else {
        const product = products.value.find(
            (p) => p.id === activeItem.product_id,
        );
        form.items.push({
            product_id: activeItem.product_id,
            actual_stock: activeItem.actual_stock || 0,
            recorded_stock: product?.stock?.current_stock || 0,
        });
    }
    activeItem.product_id = null;
    activeItem.actual_stock = 0;
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function calculateDiff(item) {
    return (item.actual_stock || 0) - (item.recorded_stock || 0);
}

function nameById(id) {
    return products.value.find((p) => p.id === id)?.name || "-";
}

function formatNumber(val) {
    return new Intl.NumberFormat("id-ID").format(val || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function statusLabel(status) {
    const labels = { I: "INVESTIGATION", D: "VERIFIED", A: "ADJUSTED" };
    return labels[status] || status;
}

function statusSeverity(status) {
    const maps = { I: "warn", D: "info", A: "success" };
    return maps[status] || "secondary";
}

async function loadProducts() {
    const response = await productApi.getAll();
    // Handle paginated response: response.data.data is usually the array for Resource collections
    // but if it's a LengthAwarePaginator, it might be response.data.data.data
    const rawData = response?.data?.data;
    products.value = Array.isArray(rawData) ? rawData : rawData?.data || [];
}

async function loadAdjustments() {
    loading.value = true;
    try {
        const response = await inventoryApi.getAdjustments();
        rows.value = unwrapCollection(response).items;
    } finally {
        loading.value = false;
    }
}

async function save(targetStatus) {
    if (form.items.length === 0) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Daftar item tidak boleh kosong",
            life: 3000,
        });
        return;
    }

    saving.value = true;
    try {
        const payload = { ...form, status: targetStatus };
        await inventoryApi.createAdjustment(payload);

        toast.add({
            severity: "success",
            summary: "Success",
            detail:
                targetStatus === "A"
                    ? "Stok berhasil diperbarui & selisih dicatat"
                    : "Draft berhasil disimpan",
            life: 2000,
        });

        dialogOpen.value = false;
        loadAdjustments();
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Save failed",
            detail: error?.response?.data?.message || "Check input",
            life: 3000,
        });
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadProducts();
    loadAdjustments();
});
</script>
