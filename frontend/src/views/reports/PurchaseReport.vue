<template>
    <AppPage
        :title="$t('sidebar.purchase_report')"
        :breadcrumb="[$t('common.reports'), $t('sidebar.purchase_report')]"
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
                        placeholder="Cari No. Pembelian atau Pemasok..."
                        class="w-full"
                    />
                </div>
                <Button
                    :label="$t('reports.show')"
                    icon="pi pi-search"
                    @click="loadData"
                    :loading="loading"
                    class="!rounded-xl shadow-sm"
                />
            </div>

            <!-- Purchase Report Table -->
            <Card class="border-none shadow-sm">
                <template #title>
                    <div class="flex justify-between items-center px-2">
                        <span
                            class="text-slate-800 font-black tracking-tight text-xl"
                            >Data Riwayat Pembelian Stok</span
                        >
                    </div>
                </template>
                <template #content>
                    <AppDataTable
                        :value="filteredPurchases"
                        paginator
                        :rows="10"
                        class="app-table"
                        stripedRows
                        responsiveLayout="scroll"
                    >
                        <template #empty>
                            <div class="text-center py-8 text-slate-400">
                                Tidak ada data pembelian untuk periode ini.
                            </div>
                        </template>
                        <Column
                            field="DT_RowIndex"
                            header="No."
                            class="text-center w-12"
                        >
                            <template #body="{ index }">{{
                                index + 1
                            }}</template>
                        </Column>
                        <Column
                            field="purchase_number"
                            header="No. Pembelian"
                        />
                        <Column header="Tanggal">
                            <template #body="{ data }">{{
                                formatDateTime(data.purchase_date)
                            }}</template>
                        </Column>
                        <Column header="Pemasok">
                            <template #body="{ data }">{{
                                data.supplier?.name || "-"
                            }}</template>
                        </Column>
                        <Column header="Karyawan">
                            <template #body="{ data }">{{
                                data.user?.name || "-"
                            }}</template>
                        </Column>
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
                                    >Total Pembelian:</span
                                >
                                <span
                                    class="font-black text-xl text-primary-600"
                                    >Rp {{ money(totalAmount) }}</span
                                >
                            </div>
                        </template>
                    </AppDataTable>
                </template>
            </Card>
        </div>
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
import { useToast } from "primevue/usetoast";

const toast = useToast();
const router = useRouter();
const loading = ref(false);
const dates = ref([
    new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    new Date(),
]);
const purchases = ref([]);
const search = ref("");

const filteredPurchases = computed(() => {
    if (!search.value) return purchases.value;
    const s = search.value.toLowerCase();
    return purchases.value.filter(
        (item) =>
            item.purchase_number?.toLowerCase().includes(s) ||
            item.supplier?.name?.toLowerCase().includes(s),
    );
});

const totalAmount = computed(() => {
    return filteredPurchases.value.reduce(
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
    });
};

async function loadData() {
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
        const response = await axios.get("/reports/purchases", { params });
        purchases.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to load purchase data", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal mengambil data pembelian.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

function viewDetail(purchase) {
    router.push({ name: "purchase-detail", params: { id: purchase.id } });
}

onMounted(() => {
    loadData();
});
</script>
