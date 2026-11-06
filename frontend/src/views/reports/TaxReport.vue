<template>
    <AppPage
        title="Laporan Pajak (DPKAD)"
        :breadcrumb="['Laporan', 'Laporan Pajak']"
    >
        <div class="space-y-4">
            <!-- Unified Filter & Export Bar -->
            <ReportFilterBar
                v-model="dates"
                periodLabel="Periode Laporan"
                buttonLabel="Tampilkan Laporan"
                :loading="loading"
                showExportExcel
                showExportPdf
                :exportingExcel="exportingExcel"
                :exportingPdf="exportingPdf"
                @search="loadReport"
                @export-excel="exportExcel"
                @export-pdf="exportPdf"
            />

            <!-- Tax Report Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #title>
                    <div class="flex justify-between items-center px-2">
                        <div class="flex flex-col">
                            <span
                                class="text-slate-800 font-black tracking-tight text-xl"
                                >Rekapitulasi Penjualan Terlapor (TAX)</span
                            >
                            <span
                                class="text-xs text-slate-400 font-medium uppercase tracking-wider"
                                >Hanya menampilkan data yang sudah disinkronkan
                                ke DPKAD</span
                            >
                        </div>
                    </div>
                </template>
                <template #content>
                    <AppDataTable
                        :value="reportData"
                        class="app-table"
                        stripedRows
                        responsiveLayout="scroll"
                        :loading="loading"
                    >
                        <template #empty>
                            <EmptyState
                                message="Tidak ada data transaksi yang tersinkron untuk periode ini."
                            />
                        </template>

                        <Column
                            field="date"
                            header="Tanggal"
                            class="font-bold text-slate-700"
                        >
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span>{{ formatDateId(data.date) }}</span>
                                    <span
                                        class="text-[10px] text-slate-400 uppercase font-bold"
                                        >{{ data.day }}</span
                                    >
                                </div>
                            </template>
                            <template #footer>
                                <span class="font-extrabold text-slate-900"
                                    >TOTAL</span
                                >
                            </template>
                        </Column>

                        <!-- Dynamic Category Columns -->
                        <Column
                            v-for="cat in allCategories"
                            :key="cat"
                            :header="cat"
                            class="text-right"
                        >
                            <template #body="{ data }">
                                <span class="text-slate-600 font-medium">
                                    Rp {{ money(data.categories[cat] || 0) }}
                                </span>
                            </template>
                            <template #footer>
                                <span class="font-bold text-slate-800">
                                    Rp
                                    {{
                                        money(
                                            reportData.reduce(
                                                (acc, day) =>
                                                    acc +
                                                    (day.categories[cat] || 0),
                                                0,
                                            ),
                                        )
                                    }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="subtotal"
                            header="Subtotal"
                            class="text-right font-medium text-slate-700"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.subtotal) }}
                            </template>
                            <template #footer>
                                <span class="font-bold text-slate-800">
                                    Rp {{ money(totals.subtotal) }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="tax"
                            header="Tax (1%)"
                            class="text-right font-medium text-slate-600"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.tax) }}
                            </template>
                            <template #footer>
                                <span class="font-bold text-slate-800">
                                    Rp {{ money(totals.tax) }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="service"
                            header="Service (1%)"
                            class="text-right font-medium text-slate-600"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.service) }}
                            </template>
                            <template #footer>
                                <span class="font-bold text-slate-800">
                                    Rp {{ money(totals.service) }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="grand_total"
                            header="Total"
                            class="text-right font-bold text-slate-900"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.grand_total) }}
                            </template>
                            <template #footer>
                                <span class="font-extrabold text-slate-900">
                                    Rp {{ money(totals.grand) }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="pb1"
                            header="PB1 (40%)"
                            class="text-right font-bold text-emerald-600"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.pb1 ?? (data.subtotal ? data.subtotal * 0.4 : 0)) }}
                            </template>
                            <template #footer>
                                <span class="font-bold text-emerald-700">
                                    Rp {{ money(totals.pb1 || (totals.subtotal ? totals.subtotal * 0.4 : 0)) }}
                                </span>
                            </template>
                        </Column>
                    </AppDataTable>
                </template>
            </Card>

            <!-- Detailed Invoices Table (Collapsible / Secondary) -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #title>
                    <div class="px-2">
                        <span
                            class="text-slate-800 font-black tracking-tight text-lg"
                            >Rincian Transaksi Terlapor</span
                        >
                        <p class="text-xs text-slate-400 font-medium">
                            Daftar struk/bill yang ditarik ke dalam pelaporan
                            pajak
                        </p>
                    </div>
                </template>
                <template #content>
                    <AppDataTable
                        :value="
                            reportData.flatMap((d) =>
                                d.invoices.map((inv) => ({
                                    ...inv,
                                    date: d.date,
                                })),
                            )
                        "
                        class="app-table"
                        stripedRows
                        paginator
                        :rows="15"
                        :rowsPerPageOptions="[15, 30, 50]"
                        responsiveLayout="scroll"
                        :loading="loading"
                    >
                        <template #empty>
                            <EmptyState
                                message="Tidak ada detail faktur transaksi."
                            />
                        </template>

                        <Column
                            field="order_number"
                            header="No. Transaksi"
                            class="font-mono text-xs font-bold text-slate-800"
                        />
                        <Column field="date" header="Tanggal">
                            <template #body="{ data }">
                                {{ formatDateId(data.date) }}
                            </template>
                        </Column>
                        <Column
                            field="customer_name"
                            header="Pelanggan"
                            class="text-slate-600"
                        >
                            <template #body="{ data }">
                                {{ data.customer_name || "-" }}
                            </template>
                        </Column>
                        <Column
                            field="payment_method"
                            header="Metode Bayar"
                            class="text-center"
                        >
                            <template #body="{ data }">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200"
                                >
                                    {{ data.payment_method }}
                                </span>
                            </template>
                        </Column>
                        <Column
                            field="subtotal"
                            header="Subtotal"
                            class="text-right"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.subtotal) }}
                            </template>
                        </Column>
                        <Column field="tax" header="Tax (1%)" class="text-right">
                            <template #body="{ data }">
                                Rp {{ money(data.tax) }}
                            </template>
                        </Column>
                        <Column field="service" header="Service (1%)" class="text-right">
                            <template #body="{ data }">
                                Rp {{ money(data.service) }}
                            </template>
                        </Column>
                        <Column
                            field="grand_total"
                            header="Total"
                            class="text-right font-bold text-slate-800"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.grand_total) }}
                            </template>
                        </Column>
                        <Column
                            field="pb1"
                            header="PB1 (40%)"
                            class="text-right font-bold text-emerald-600"
                        >
                            <template #body="{ data }">
                                Rp {{ money(data.pb1 ?? (data.subtotal ? data.subtotal * 0.4 : 0)) }}
                            </template>
                        </Column>
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
import Column from "primevue/column";
import { ref, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import { money, formatDateId, formatDatePayload, downloadBlob } from "@/utils/formatters";

const toast = useToast();
const loading = ref(false);
const exportingExcel = ref(false);
const exportingPdf = ref(false);

const dates = ref([
    new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    new Date(),
]);
const reportData = ref([]);

const allCategories = computed(() => {
    const cats = new Set();
    reportData.value.forEach((day) => {
        Object.keys(day.categories || {}).forEach((cat) => cats.add(cat));
    });
    return Array.from(cats).sort();
});

const totals = computed(() => {
    return reportData.value.reduce(
        (acc, day) => {
            const pb1Val = Number(day.pb1 ?? (day.subtotal ? day.subtotal * 0.4 : 0)) || 0;
            acc.subtotal += Number(day.subtotal) || 0;
            acc.tax += Number(day.tax) || 0;
            acc.service += Number(day.service) || 0;
            acc.grand += Number(day.grand_total) || 0;
            acc.pb1 += pb1Val;
            return acc;
        },
        { subtotal: 0, tax: 0, service: 0, grand: 0, pb1: 0 },
    );
});

async function loadReport() {
    if (!dates.value || !dates.value[0] || !dates.value[1]) {
        toast.add({
            severity: "warn",
            summary: "Peringatan",
            detail: "Pilih rentang tanggal.",
            life: 3000,
        });
        return;
    }

    loading.value = true;
    try {
        const params = {
            start_date: formatDatePayload(dates.value[0]),
            end_date: formatDatePayload(dates.value[1]),
        };
        const response = await axios.get("/reports/tax", { params });
        reportData.value = response.data.data || [];
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal memuat laporan pajak.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

async function exportExcel() {
    await downloadFile("/reports/export/tax-excel", "Laporan_Pajak", "xlsx", exportingExcel);
}

async function exportPdf() {
    await downloadFile("/reports/export/tax-pdf", "Laporan_Pajak", "pdf", exportingPdf);
}

async function downloadFile(url, prefix, type = "xlsx", loadingRef) {
    if (!dates.value || !dates.value[0] || !dates.value[1]) return;

    if (loadingRef) loadingRef.value = true;
    try {
        const start = formatDatePayload(dates.value[0]);
        const end = formatDatePayload(dates.value[1]);
        const response = await axios.get(url, {
            params: { start_date: start, end_date: end },
            responseType: "blob",
        });

        downloadBlob(response.data, `${prefix}_${start}_${end}.${type}`);
        toast.add({
            severity: "success",
            summary: "Berhasil",
            detail: "File berhasil diunduh.",
            life: 3000,
        });
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal mengunduh file.",
            life: 3000,
        });
    } finally {
        if (loadingRef) loadingRef.value = false;
    }
}

onMounted(() => {
    loadReport();
});
</script>
