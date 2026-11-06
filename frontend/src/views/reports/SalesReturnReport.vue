<template>
    <AppPage
        :title="$t('sidebar.sales_return_report')"
        :breadcrumb="[$t('common.reports'), $t('sidebar.sales_return_report')]"
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
                :searchPlaceholder="$t('reports.search')"
                @search="loadData"
            />

            <!-- Sales Return Table -->
            <Card class="border-none shadow-sm">
                <template #title>
                    <div class="flex justify-between items-center px-2">
                        <span
                            class="text-slate-800 font-black tracking-tight text-xl"
                            >Daftar Riwayat Retur Penjualan</span
                        >
                    </div>
                </template>
                <template #content>
                    <AppDataTable
                        :value="filteredReturns"
                        paginator
                        :rows="10"
                        class="app-table"
                        stripedRows
                        responsiveLayout="scroll"
                    >
                        <template #empty>
                            <EmptyState
                                message="Tidak ada data retur penjualan untuk periode ini."
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
                        <Column field="order_number" header="No. Pesanan" />
                        <Column header="Tgl Retur">
                            <template #body="{ data }">{{
                                formatDate(data.return_date, true)
                            }}</template>
                        </Column>
                        <Column header="Pelanggan">
                            <template #body="{ data }">{{
                                data.customer_name || "Pelanggan Umum"
                            }}</template>
                        </Column>
                        <Column header="Kasir">
                            <template #body="{ data }">{{
                                data.user?.name || "-"
                            }}</template>
                        </Column>
                        <Column header="Penerima Retur">
                            <template #body="{ data }">{{
                                data.return_user?.name || "-"
                            }}</template>
                        </Column>
                        <Column
                            field="total_return"
                            header="Total Retur"
                            class="text-right"
                        >
                            <template #body="{ data }">
                                <span class="font-bold text-rose-600"
                                    >Rp {{ money(data.total_return) }}</span
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
                                    >Total Nilai Retur:</span
                                >
                                <span class="font-black text-xl text-rose-600"
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
const returns = ref([]);
const search = ref("");

const filteredReturns = computed(() => {
    if (!search.value) return returns.value;
    const s = search.value.toLowerCase();
    return returns.value.filter(
        (item) =>
            item.order_number?.toLowerCase().includes(s) ||
            item.customer_name?.toLowerCase().includes(s),
    );
});

const totalAmount = computed(() => {
    return filteredReturns.value.reduce(
        (sum, item) => sum + Number(item.total_return),
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
        const response = await axios.get("/reports/sales-returns", { params });
        returns.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to load return data", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal mengambil data retur penjualan.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

function viewDetail(item) {
    const orderId = item.order_id || item.id;
    router.push({ name: "order-detail", params: { id: orderId } });
}

onMounted(() => {
    loadData();
});
</script>
