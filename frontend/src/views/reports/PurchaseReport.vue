<template>
    <AppPage
        :title="$t('sidebar.purchase_report')"
        :breadcrumb="[$t('common.reports'), $t('sidebar.purchase_report')]"
    >
        <div class="space-y-4">
            <!-- Reusable Filter Bar -->
            <ReportFilterBar
                v-model="dates"
                :periodLabel="$t('reports.period')"
                :buttonLabel="$t('reports.show')"
                :loading="loading"
                showSearch
                v-model:searchValue="search"
                searchPlaceholder="Cari No. Pembelian atau Pemasok..."
                @search="loadData"
            />

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
                            <EmptyState
                                message="Tidak ada data pembelian untuk periode ini."
                            />
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
                                formatDate(data.purchase_date, true)
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
import AppPage from "@/components/layout/AppPage.vue";
import ReportFilterBar from "@/components/report/ReportFilterBar.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import { money, formatDate, formatDatePayload } from "@/utils/formatters";

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
        start_date: formatDatePayload(dates.value[0]),
        end_date: formatDatePayload(dates.value[1]),
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
