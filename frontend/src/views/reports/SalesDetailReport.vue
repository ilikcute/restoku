<template>
    <AppPage
        :title="$t('sidebar.sales_report')"
        :breadcrumb="[$t('common.reports'), $t('sidebar.sales_report')]"
    >
        <div class="space-y-1">
            <!-- Filters -->

            <div
                class="flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 p-2 rounded-2xl border border-slate-100"
            >
                <div class="flex items-center gap-3">
                    <label
                        class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap"
                        >{{ $t("reports.period") }}</label
                    >
                    <DatePicker
                        v-model="dates"
                        selectionMode="range"
                        :manualInput="false"
                        showIcon
                        iconDisplay="input"
                        class="w-72"
                    />
                    <label
                        class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap"
                        >{{ $t("reports.search") }}</label
                    >
                    <InputText
                        v-model="search"
                        :placeholder="$t('reports.search_placeholder')"
                        class="w-full"
                    />
                </div>
                <Button
                    :label="$t('reports.show')"
                    icon="pi pi-search"
                    @click="loadTransactions"
                    :loading="loading"
                    class="!rounded-xl shadow-sm"
                />
            </div>
        </div>

        <!-- Detailed Sales Report Table -->
        <Card class="border-none shadow-sm">
            <template #title>
                <div class="flex justify-between items-center px-2">
                    <span
                        class="text-slate-800 font-black tracking-tight text-xl"
                        >Data Riwayat Transaksi Penjualan</span
                    >
                    <div class="flex gap-2">
                        <Button
                            label="Excel Penjualan"
                            icon="pi pi-file-excel"
                            severity="success"
                            outlined
                            size="small"
                            @click="exportExcelSales"
                        />
                        <Button
                            label="Excel Detail"
                            icon="pi pi-file-excel"
                            severity="success"
                            outlined
                            size="small"
                            @click="exportExcelDetail"
                        />
                        <Button
                            label="Excel Per Shift"
                            icon="pi pi-file-excel"
                            severity="success"
                            outlined
                            size="small"
                            @click="exportExcelShift"
                        />
                    </div>
                </div>
            </template>
            <template #content>
                <!-- Bulk Sync Action Bar -->
                <div 
                    v-if="selectedOrderIds.length > 0"
                    class="bg-emerald-50/70 border border-emerald-100 rounded-2xl p-4 mb-4 flex items-center justify-between gap-4 animate-fadein"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center">
                            <i class="pi pi-check-circle text-sm"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-700">
                            {{ selectedOrderIds.length }} transaksi terpilih untuk sinkronisasi DPKAD
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            label="Batalkan Pilihan" 
                            outlined 
                            severity="secondary" 
                            size="small" 
                            class="!rounded-xl"
                            @click="selectedOrderIds = []" 
                        />
                        <Button 
                            label="Sinkronkan ke DPKAD" 
                            icon="pi pi-sync" 
                            severity="success" 
                            size="small" 
                            class="!rounded-xl !bg-emerald-600 !border-none shadow-sm shadow-emerald-100"
                            :loading="syncingBulk"
                            @click="bulkSyncToDpkad" 
                        />
                    </div>
                </div>

                <AppDataTable
                    :value="filteredTransactions"
                    paginator
                    :rows="10"
                    class="app-table"
                    stripedRows
                    responsiveLayout="scroll"
                >
                    <template #empty>
                        <div class="text-center py-8 text-slate-400">
                            Tidak ada data transaksi untuk periode ini.
                        </div>
                    </template>
                    <Column class="text-center w-12">
                        <template #header>
                            <Checkbox
                                :modelValue="isAllSelectableSelected"
                                :binary="true"
                                :disabled="filteredTransactions.filter(t => !t.is_synced_to_dpkad).length === 0"
                                @update:modelValue="toggleSelectAll"
                            />
                        </template>
                        <template #body="{ data }">
                            <Checkbox
                                v-model="selectedOrderIds"
                                :value="data.id"
                                :disabled="!!data.is_synced_to_dpkad"
                            />
                        </template>
                    </Column>
                    <Column
                        field="DT_RowIndex"
                        header="No."
                        class="text-center w-12"
                    >
                        <template #body="{ index }">{{ index + 1 }}</template>
                    </Column>
                    <Column field="order_number" header="No. Trans" />
                    <Column header="Tanggal">
                        <template #body="{ data }">{{
                            formatDateTime(data.created_at)
                        }}</template>
                    </Column>
                    <Column header="Karyawan">
                        <template #body="{ data }">{{
                            data.user?.name || "-"
                        }}</template>
                    </Column>
                    <Column
                        field="table_number"
                        header="No. Meja"
                        class="text-center"
                    />
                    <Column
                        field="total_amount"
                        header="Total Transaksi"
                        class="text-right"
                    >
                        <template #body="{ data }">
                            <span class="font-bold text-slate-800"
                                >Rp {{ money(data.total_amount) }}</span
                            >
                        </template>
                    </Column>
                    <Column header="DPKAD" class="text-center w-32">
                        <template #body="{ data }">
                            <div class="flex items-center justify-center gap-2">
                                <Checkbox
                                    :modelValue="!!data.is_synced_to_dpkad"
                                    :binary="true"
                                    :disabled="!!data.is_synced_to_dpkad"
                                    @update:modelValue="syncToDpkad(data)"
                                />
                                <span
                                    v-if="data.is_synced_to_dpkad"
                                    class="text-[10px] text-emerald-600 font-bold uppercase"
                                    >Synced</span
                                >
                            </div>
                        </template>
                    </Column>
                    <Column header="Aksi" class="text-center w-12">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-eye"
                                text
                                rounded
                                @click="viewDetail(data)"
                            />
                        </template>
                    </Column>
                    <template #footer>
                        <div class="flex justify-end pr-12 gap-4">
                            <span
                                class="font-bold text-slate-600 uppercase tracking-wider"
                                >Total Omzet:</span
                            >
                            <span class="font-black text-xl text-primary-600"
                                >Rp {{ money(transactionsTotal) }}</span
                            >
                        </div>
                    </template>
                </AppDataTable>
            </template>
        </Card>
    </AppPage>
</template>

<script setup>
import axios from "@/api/axios";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import AppPage from "@/components/layout/AppPage.vue";
import Card from "primevue/card";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Checkbox from "primevue/checkbox";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const router = useRouter();
const loading = ref(false);
const syncingBulk = ref(false);
const selectedOrderIds = ref([]);

// Default to current month
const dates = ref([
    new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    new Date(),
]);
const transactions = ref([]);
const search = ref("");

// Computed property to check if all selectable (un-synced) transactions are selected
const isAllSelectableSelected = computed(() => {
    const selectable = filteredTransactions.value.filter(t => !t.is_synced_to_dpkad);
    if (selectable.length === 0) return false;
    return selectable.every(t => selectedOrderIds.value.includes(t.id));
});

// Toggle select all un-synced transactions currently visible
const toggleSelectAll = () => {
    const selectable = filteredTransactions.value.filter(t => !t.is_synced_to_dpkad);
    if (isAllSelectableSelected.value) {
        const visibleSelectableIds = selectable.map(t => t.id);
        selectedOrderIds.value = selectedOrderIds.value.filter(id => !visibleSelectableIds.includes(id));
    } else {
        const visibleSelectableIds = selectable.map(t => t.id);
        selectedOrderIds.value = [...new Set([...selectedOrderIds.value, ...visibleSelectableIds])];
    }
};

async function bulkSyncToDpkad() {
    if (selectedOrderIds.value.length === 0) return;

    syncingBulk.value = true;
    try {
        const response = await axios.post("/reports/sync-dpkad", {
            order_ids: selectedOrderIds.value,
        });

        if (response.data.status === "success" || response.data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: response.data.message || `Berhasil menyinkronkan ${selectedOrderIds.value.length} transaksi ke DPKAD.`,
                life: 3000,
            });

            // Update local state
            transactions.value.forEach(order => {
                if (selectedOrderIds.value.includes(order.id)) {
                    order.is_synced_to_dpkad = true;
                }
            });

            selectedOrderIds.value = [];
        }
    } catch (error) {
        console.error("Failed to bulk sync to DPKAD", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: error.response?.data?.message || "Gagal menyinkronkan data terpilih ke DPKAD.",
            life: 3000,
        });
    } finally {
        syncingBulk.value = false;
    }
}

const filteredTransactions = computed(() => {
    if (!search.value) return transactions.value;
    const s = search.value.toLowerCase();
    return transactions.value.filter(
        (item) =>
            item.order_number?.toLowerCase().includes(s) ||
            item.customer_name?.toLowerCase().includes(s) ||
            item.user?.name?.toLowerCase().includes(s) ||
            String(item.table_number || "")
                .toLowerCase()
                .includes(s),
    );
});

const transactionsTotal = computed(() => {
    return filteredTransactions.value.reduce(
        (sum, item) => sum + Number(item.total_amount),
        0,
    );
});

function money(value) {
    if (value === undefined || value === null) return "0";
    return Number(value).toLocaleString("id-ID");
}

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    let month = "" + (d.getMonth() + 1);
    let day = "" + d.getDate();
    let year = d.getFullYear();

    if (month.length < 2) month = "0" + month;
    if (day.length < 2) day = "0" + day;

    return [year, month, day].join("-");
};

const formatDateTime = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

async function syncToDpkad(order) {
    if (order.is_synced_to_dpkad) return;

    loading.value = true;
    try {
        const response = await axios.post("/reports/sync-dpkad", {
            order_ids: [order.id],
        });

        if (response.data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: response.data.message,
                life: 3000,
            });
            // Update local data
            order.is_synced_to_dpkad = true;
        }
    } catch (error) {
        console.error("Failed to sync to DPKAD", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail:
                error.response?.data?.message ||
                "Gagal menyinkronkan data ke DPKAD.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

async function loadTransactions() {
    if (!dates.value || !dates.value[0] || !dates.value[1]) {
        toast.add({
            severity: "warn",
            summary: "Peringatan",
            detail: "Harap pilih rentang tanggal lengkap.",
            life: 3000,
        });
        return;
    }

    loading.value = true;
    const params = {
        start_date: formatDate(dates.value[0]),
        end_date: formatDate(dates.value[1]),
    };

    try {
        const response = await axios.get("/reports/transactions", { params });
        transactions.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to load transaction data", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal mengambil data transaksi.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

async function exportExcelSales() {
    await downloadFile("/reports/export/sales", `Laporan_Penjualan`);
}

async function exportExcelDetail() {
    await downloadFile(
        "/reports/export/sales-detail",
        `Laporan_Penjualan_Detail`,
    );
}

async function exportExcelShift() {
    await downloadFile("/reports/export/sales-shift", `Laporan_Per_Shift`);
}

async function downloadFile(url, prefix) {
    if (!dates.value || !dates.value[0] || !dates.value[1]) return;

    loading.value = true;
    try {
        const start = formatDate(dates.value[0]);
        const end = formatDate(dates.value[1]);
        const response = await axios.get(url, {
            params: { start_date: start, end_date: end },
            responseType: "blob",
        });

        const downloadUrl = window.URL.createObjectURL(
            new Blob([response.data]),
        );
        const link = document.createElement("a");
        link.href = downloadUrl;
        link.setAttribute("download", `${prefix}_${start}_${end}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal mengunduh file Excel.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

function viewDetail(order) {
    router.push({ name: "order-detail", params: { id: order.id } });
}

onMounted(() => {
    loadTransactions();
});
</script>
