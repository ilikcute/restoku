<template>
    <AppPage
        :title="$t('sales.order_history')"
        :breadcrumb="[$t('common.sales'), $t('sidebar.orders')]"
    >
        <div class="space-y-1">
            <!-- Filters -->
            <div
                class="flex gap-3 bg-slate-50/50 p-1 rounded-2xl border border-slate-100"
            >
                <InputText
                    v-model="filters.q"
                    :placeholder="`${$t('common.search_placeholder')} order...`"
                    class="flex-1 !rounded-xl"
                />
                <Select
                    v-model="filters.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Filter Status"
                    showClear
                    class="flex-1 !rounded-xl"
                />
                <Button
                    icon="pi pi-refresh"
                    class="flex-none w-12 !rounded-xl !bg-white !border !border-slate-200 !text-slate-500 hover:!bg-slate-100 shadow-sm"
                    @click="load"
                    :loading="loading"
                />
            </div>

            <AppDataTable
                framed
                :value="rows"
                lazy
                paginator
                :rows="rowsPerPage"
                :first="first"
                :totalRecords="totalRecords"
                :loading="loading"
                selectionMode="single"
                @page="onPage"
                @rowSelect="openOrder"
            >
                <Column header="No" class="w-16 text-center">
                    <template #body="slotProps">
                        <span class="text-slate-400 font-mono text-xs">{{
                            slotProps.index + first + 1
                        }}</span>
                    </template>
                </Column>
                <Column field="order_number" :header="$t('dashboard.order_id')">
                    <template #body="{ data }">
                        <span class="font-semibold text-slate-800">{{
                            data.order_number
                        }}</span>
                    </template>
                </Column>
                <Column :header="$t('common.date')">
                    <template #body="{ data }">
                        <span class="text-slate-500">{{
                            formatDate(data.created_at)
                        }}</span>
                    </template>
                </Column>
                <Column field="customer_name" :header="$t('checkout.customer')">
                    <template #body="{ data }">
                        <span class="text-slate-600">{{
                            data.customer_name || "-"
                        }}</span>
                    </template>
                </Column>
                <Column
                    field="payment_method"
                    :header="$t('sales.payment_method')"
                >
                    <template #body="{ data }">
                        <span
                            class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wider"
                        >
                            {{ data.payment_method }}
                        </span>
                    </template>
                </Column>
                <Column field="total_amount" :header="$t('common.total')">
                    <template #body="{ data }">
                        <span class="font-semibold text-slate-700"
                            >Rp {{ money(data.total_amount) }}</span
                        >
                    </template>
                </Column>
                <Column :header="$t('common.status')" class="w-32">
                    <template #body="{ data }">
                        <StatusBadge
                            :status="data.status || 'pending'"
                            :label="data.status || 'pending'"
                        />
                    </template>
                </Column>
            </AppDataTable>
        </div>
    </AppPage>
</template>

<script setup>
import { ref, watch, reactive } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { orderApi } from "@/api/sales";
import { unwrapCollection } from "@/utils/api";
import AppPage from "@/components/layout/AppPage.vue";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import Column from "primevue/column";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

const { t: $t } = useI18n();
const router = useRouter();
const rows = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const first = ref(0);

const filters = reactive({ q: "", status: null });

const statusOptions = [
    { label: "Completed", value: "completed" },
    { label: "Pending", value: "pending" },
    { label: "Cancelled", value: "cancelled" },
];

function money(value) {
    return Number(value || 0).toLocaleString("id-ID");
}

function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function onPage(event) {
    first.value = event.first;
    rowsPerPage.value = event.rows;
    load(event.page + 1);
}

watch(
    filters,
    () => {
        first.value = 0;
        load(1);
    },
    { deep: true },
);

async function load(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: rowsPerPage.value };
        if (filters.q) params.q = filters.q;
        if (filters.status) params.status = filters.status;
        const response = await orderApi.getAll(params);
        const result = response?.data?.data || {};
        rows.value = result.data || [];
        totalRecords.value = result.meta?.total || 0;
    } finally {
        loading.value = false;
    }
}

async function openOrder(event) {
    router.push({ name: "order-detail", params: { id: event.data.id } });
}

load();
</script>
